<?php

namespace App\Http\Controllers;

use Cache;
use Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\UcUser;
use App\Model\Config;
use App\Model\UserVip;
use App\Model\InstallLog;
use App\Model\UserCollects;
use App\Model\UserIdentifiers;
use App\Model\UserPhoneHistory;
use App\Model\UserConsumptionLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use App\Service\Luosimao\Sms;
use Mews\Captcha\Facades\Captcha;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

use App\Exceptions\UserException;


class UserController extends BaseController {

    /**
     * 同IP发送短信限制数
     */
    const sendVerifyCodeLimit = 10;

    /**
     * 同IP发送短信次数
     */
    const sendVerifyCodeTimes = 'send_verify_code_times';

    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        $this->response = ['status' => 0, 'message' => 'OK', 'data' => []];
    }



    /**
     * 登录操作
     * @param Request $request
     * @param type $identifier
     * @return type
     */
    public function login( Request $request, $identifier )
    {
        if( !$identifier ) {
            return response()->json(['status' => 400, 'message' => '参数异常']);
        }

        $username = get_param($request, 'username');
        $password = get_param($request, 'password');
        $os       = get_param($request, 'os');

        if( !$ucUser = UcUser::findByUserName($username) ) {
            return response()->json(['status' => 405, 'message' => '用户名错误']);
        }

        if( sha1($ucUser->salt . $password) != $ucUser->password ) {
            return response()->json(['status' => 405, 'message' => '密码错误']);
        }

        if( !$user = User::findByUcId($ucUser->id) ) {
            return response()->json(['status' => 405, 'message' => '用户异常，请联系客服']);
        }

        //查看保存设备 并更新
        UserIdentifiers::deleteOrCreate($user->id, $identifier);


        $user      = self::loginLog($user, $request->getClientIp(),$os);
        $user->vip = ['is_vip' => false];
        if( $isVip     = User::isVip($user) ) {
            $user->vip = [
                'is_vip'    => true,
                'expire_at' => strtotime($isVip->expire_at),
            ];
        }
        $user->identifier = $identifier;
        $user->is_register = true;
        $user             = $user->toArray();
        $user['is_mute']  = self::isMute($user['id']);
        $user['id']       = hashid($user['id'], 'user');
        $user['phone']    = $ucUser->phone;
        $user['username'] = $ucUser->username;

        unset($user['uc_user']);
        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $user]);
    }

    /**
     *  注册  TODO  后期切换至laravel原生注册  需要启用Auth
     * @param Request $request
     * @param type $identifier
     * @return type
     */
    public function register( Request $request, $identifier )
    {
        if( !$identifier ) {
            return response()->json(['status' => 400, 'message' => '参数异常']);
        }

        $data   = [
            'username' =>get_param($request, 'username'),
//            'nickname' =>get_param($request, 'nickname'),
            'password' =>get_param($request, 'password'),
        ];

        // 基本验证
        if($validator = $this->userValidator($data)){
            return response()->json($validator);
        }

        //用户名判断
        if( UcUser::findByUserName($data['username']) ) {
            return response()->json(['status' => 405, 'message' => '用户名已存在']);
        }

        //昵称判断
//        if( User::findByNickName($data['nickname']) ) {
//            return response()->json(['status' => 405, 'message' => '昵称已存在']);
//        }
//        if($keywordsRule = self::getConfig('keyword_masking')){
//            foreach ($keywordsRule['content'] as $key => $value) {
//                if(strpos($data['nickname'],$value) !== false){
//                    return response()->json(['status' => 405, 'message' => '禁止非法字节(例如：q,Q)，请重新输入']);
//                    break;
//                }
//            }
//        }

        $data['salt']       = Str::random(6);
        $data['password']   = sha1($data['salt'] . $data['password']);
        $data['os']         = get_param($request, 'os');
        $data['channel']    = get_param($request, 'ch');
        $data['identifier'] = $identifier;

        //事务进行
        try {
            $ucId = DB::transaction(function($data)use($data) {
                        $identifier     = $data['identifier'];
                        $now            = date('Y-m-d H:i:s');
                        $user = [];

                        //检测是否是老用户 并且检测是否已关联主账户表
                        // if( $useridentifier = DB::table('user_identifiers')->where('identifier', $identifier)->first() ){
                        //     $user = DB::table('users')->where('id',$useridentifier->user_id)->first();
                        //     // if( $user && (isset($user->uc_id) && $user->uc_id)){
                        //     //     throw new \Exception("此设备已注册,请重启APP",1);
                        //     // }
                        // }

                        //生成主表用户 返回ID
                        $ucUser         = DB::table("uc_users")->insertGetId([
                            'username' => $data['username'],
                            'password' => $data['password'],
                            'salt'     => $data['salt'],
                            'channel'    => $data['channel'],
                            'created_at' => $now,
                        ]);

                        $userId = DB::table('users')->insertGetId([
//                            'nickname'   => $data['nickname'],
                            'api_token'  => md5($identifier . time()),
                            'vip_type'   => 1,
                            'uc_id'      => $ucUser,
                            'channel'    => $data['channel'],
                            'os'         => $data['os'] ? $data['os'] : 'android',
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);

                        //写入设备关联表
                        DB::table('user_identifiers')->insert([
                            'user_id'    => $userId,
                            'identifier' => $identifier
                            ]
                        );

                        return $ucUser;
                    });
        } catch( \Exception $ex ) {
            return response()->json(array('status' => 405, 'message' => $ex->getMessage()));
        }

        //重新查询，兼容旧用户注册操作
        $user      = User::findByUcId($ucId);
        $user      = self::loginLog($user, $request->getClientIp(),$data['os']);

        $user->vip = ['is_vip' => false];
        if( $isVip     = User::isVip($user) ) {
            $user->vip = [
                'is_vip'    => true,
                'expire_at' => strtotime($isVip->expire_at),
            ];
        }
        $user->identifier = $identifier;
        $user['is_mute']  = self::isMute($user['id']);
        $user             = $user->toArray();
        $user['id']       = hashid($user['id'], 'user');
        $user['phone']    = $user['uc_user']['phone'];
        $user['username'] = $user['uc_user']['username'];
        unset($user['uc_user']);

        return response()->json(['status' => 0, 'message' => '注册成功', 'data' => $user]);
    }

    /**
     * 用户登录注册验证
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function userValidator($data)
    {
        $rule = [
            'username' =>  'required|alpha_num|between:4,12',
//            'nickname' =>  'required|between:2,12',
            'password' =>  'required|alpha_num|between:6,12',
        ];
        $messages = [
            'required' => ':attribute 不可为空',
            'alpha_num'=> ':attribute 必须为数字和英文字母',
            'between'=>':attribute 最少:min 位,最大:max 位',
        ];

        $validator = Validator::make($data,$rule,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = $errors->toArray();
            $message  = array_shift($messages);
            $response = [
                'status'    =>  405,
                'message'   => $message[0]
            ];
            return $response;
        }
    }

    /**
     * 检测用户是否已存在 存在默认登录
     * @param Request $request
     * @param type $identifier
     * @return type
     */
    public function checkUser( Request $request, $identifier )
    {
        if( !$identifier ) {
            return response()->json(['status' => 400, 'message' => '参数异常']);
        }
        $os       = get_param($request, 'os');
        $channel  = get_param($request, 'ch');
        //检测设备是否已注册
        if( $userIdentifier = UserIdentifiers::findByIdentifier( $identifier ) ) {
            //通过设备表反向获取用户信息
            $user      = $userIdentifier->user;
            $user      = self::loginLog( $user, $request->getClientIp(),$os );
            $user->vip = ['is_vip' => false];
            if( $isVip     = User::isVip( $user ) ) {
                $user->vip = [
                    'is_vip'    => true,
                    'expire_at' => strtotime( $isVip->expire_at ),
                ];
            }
            $user->is_register = true;
            $user->identifier = $userIdentifier->identifier;
            $user             = $user->toArray();
//            $user['is_mute']  = self::isMute($user['id']);
//            $user['phone']    = $ucUser ? $ucUser->phone :'';
//            $user['username'] = $ucUser ? $ucUser->username : '';
//            $user['deposit'] = $ucUser ? $ucUser->deposit : '';
            return response()->json( ['status' => 0, 'messge' => 'OK', 'data' => $user] );
        }
        else {
            //记录装机量
            $os = get_param( $request, 'os' );
            InstallLog::checkCreate( $identifier,$os,$channel );

            $last_user_id = Redis::get('last_user_id');

            $last_user_id = $last_user_id ? $last_user_id + 1 : 1;

            $data = [
                'username' => $last_user_id.mt_rand(10000,99999),
            ];

            $data['nickname']   = $data['username'];
            $data['salt']       = Str::random(6);
            $data['password']   = '';
            $data['os']         = get_param($request, 'os');
            $data['channel']    = get_param($request, 'ch');
            $data['identifier'] = $identifier;

            //事务进行
            try {
                $uid = DB::transaction(function($data)use($data) {
                    $identifier     = $data['identifier'];
                    $now            = date('Y-m-d H:i:s');

                    $userId = DB::table('users')->insertGetId([
                        'username'   => $data['username'],
                        'nickname'   => $data['nickname'],
                        'salt'       => $data['salt'],
                        'password'   => '',
                        'vip_type'   => 1,
                        'api_token'  => md5($identifier . time()),
                        'channel'    => $data['channel'],
                        'os'         => $data['os'] ? $data['os'] : 'android',
                        'avatar'     => 'http://play.zdzlw.com/img/avatar/head4.png',
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);

                    //写入设备关联表
                    DB::table('user_identifiers')->insert([
                            'user_id'    => $userId,
                            'identifier' => $identifier
                        ]
                    );

                    return $userId;
                });
            } catch( \Exception $ex ) {
                return response()->json(array('status' => 405, 'message' => $ex->getMessage()));
            }

            Redis::set('last_user_id',$uid);

            //重新查询，兼容旧用户注册操作
            $user      = User::findByUid($uid);
            $user      = self::loginLog($user, $request->getClientIp(),$data['os']);

            $user->vip = ['is_vip' => false];
            if( $isVip     = User::isVip($user) ) {
                $user->vip = [
                    'is_vip'    => true,
                    'expire_at' => strtotime($isVip->expire_at),
                ];
            }
            $user->identifier = $identifier;
            $user['is_mute']  = self::isMute($user['id']);
            $user             = $user->toArray();
            $user['id']       = hashid($user['id'], 'user');

            return response()->json( ['status' => 0, 'messge' => 'OK', 'data' => $user] );
        }
    }

    /**
     * 登录日志
     * @param type $user
     */
    private static function loginLog( $user, $ip = '', $os = 'android')
    {
        $num = rand(200,500);
        $user->api_token = md5($user->identifier . microtime() . $num);
        $user->update();

        \App\Model\UserLogin::log($user->id, $ip, $os);

        return $user;
    }

    /**
     * 获取用户
     * @param Request $request
     * @param type $id
     * @return type
     */
    public function getUser( Request $request, $id )
    {
        return response()->json($request->user());
    }


    /**
     * 获取收藏列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getUserCollect( Request $request )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $page       = intval($request->input('page',1));
        $pageSize   = intval($request->input('page_size',20));

        $result = User::getCollectList($user,$page,$pageSize);

        return response()->json(['status'=>0,'message'=>'OK','data'=>$result]);
    }


    /**
     * [bindPhone 绑定手机]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function bindPhone( Request $request )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status'=>401,'message'=>'请先登录']);
        }

        try{
            $info = self::verifyBind($request, $user, 1);
        }catch(UserException $e){
            return response()->json(['status'=>$e->getCode(),'message'=>$e->getMessage()]);
        }

        //写入
        $state = User::where('id',$info['uid'])->update(['phone'=>$request->input('mobile'),'password'=>$info['password']]);
        if($state){
            Redis::del($info['cacheKey']);
            return response()->json(['status'=>0,'message'=>'操作成功']);
        }
        return response()->json(['status'=>405,'message'=>'操作失败']);
    }

    /**
     * [unbindPhone 解绑]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function unbindPhone( Request $request )
    {
        if( !$user = $request->user() ) {
            throw new UserException( 401,'请先登录' );
        }

        try{
            $info = self::verifyBind($request, $user, 2);
        }catch(UserException $e){
            return response()->json(['status'=>$e->getCode(),'message'=>$e->getMessage()]);
        }
        //修改手机号
        $state = User::where('id',$info['uid'])->update(['phone'=>$request->input('mobile')]);
        if($state){
            Redis::del($info['cacheKey']);
            UserPhoneHistory::insert(['user_id'=>$user->id,'phone'=>$request->input('mobile')]);
            return response()->json(['status'=>0,'message'=>'操作成功']);
        }
        return response()->json(['status'=>405,'message'=>'操作失败']);
    }

    /**
     * [verifyBind 绑定、解绑公用验证]
     * @param  [type] $request [description]
     * @param  [type] $type    [description]
     * @return [type]          [description]
     */
    private static function verifyBind($request, $user, $type)
    {
        $phone      = get_param($request, 'mobile');
        $verifyCode = get_param($request, 'code');
        $password   = get_param($request, 'password');
        $password_confirm   = get_param($request, 'password_confirm');

        if( $type == 1 && $password != $password_confirm ) {
            throw new UserException( 405,'确认密码错误' );
        }

        if(!preg_match("/^1[34578]\d{9}$/", $phone)){
            throw new UserException( 405,'请填写正确手机号' );
        }

        if($type ==1 && $user->phone != ''){
            throw new UserException( 406,'已绑定手机，请先解绑' );
        }

        $isset = User::findByPhone($phone);
        //type为1 绑定  判断手机是否已绑定
        if($isset){
            throw new UserException( 406,'手机号已绑定' );
        }

        if( $type == 2 && sha1($user->salt.$password) != $user->password ) {
            throw new UserException( 405,'密码错误' );
        }

        if( $type == 1 || $type == 2 ){
            $cacheKey = 'verify_code_'.$user->id.$phone;
        } else {
            $cacheKey = 'verify_code_'.$user->phone;
        }

        //检测redis
        if(!$redis = Redis::ping()){
            throw new UserException( 405,'系统异常，请稍后再试' );
        }
        Redis::select(7);
        if(!$exists = Redis::exists($cacheKey)){
            throw new UserException( 405,'验证码已过期' );
        }

        $code = Redis::get($cacheKey);

        if($code != $verifyCode){
            throw new UserException( 405,'验证码错误' );
        }

        return ['cacheKey'=>$cacheKey,'uid'=>$user->id,'password'=>sha1($user->salt . $password)];
    }

    /**
     * [resetPassword 重置密码]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function resetPassword( Request $request )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $phone = get_param($request, 'mobile');
        $verifyCode = get_param($request, 'code');
        $password   = get_param($request, 'password');
        $password_confirm   = get_param($request, 'password_confirm');

        if( $phone != $user->phone ){
            return response()->json(['status' => 405, 'message' => '手机号错误']);
        }

        $rule = [
            'password' =>  'required|alpha_num|between:6,12',
        ];
        $messages = [
            'required' => ':attribute 不可为空',
            'alpha_num'=> ':attribute 必须为数字和英文字母',
            'between'=>':attribute 最少:min 位,最大:max 位',
        ];

        $validator = Validator::make(['password'=>$password],$rule,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = $errors->toArray();
            $message  = array_shift($messages);
            $response = [
                'status'    =>  405,
                'message'   => $message[0]
            ];
            return $response;
        }

        if( $password != $password_confirm ){
            return response()->json(['status' => 405, 'message' => '确认密码错误']);
        }

        //检测redis
        if(!$redis = Redis::ping()){
            return response()->json(['status'=>405,'message'=>'系统异常，请稍后再试']);
        }
        Redis::select(7);
        $cacheKey = 'verify_code_'.$user->phone;

        if(!$exists = Redis::exists($cacheKey)){
            return response()->json(['status'=>405,'message'=>'验证码已过期']);
        }

        $code = Redis::get($cacheKey);
        if($code != $verifyCode){
            return response()->json(['status'=>405,'message'=>'验证码错误']);
        }

        $data['salt']       = Str::random(6);
        $data['password']   = sha1($data['salt'] . $password);
        $state = User::where('id',$user->id)->update($data);

        if($state){
            Redis::del($cacheKey);
            return response()->json(['status'=>0,'message'=>'重置成功']);
        }
        return response()->json(['status'=>405,'message'=>'重置失败']);

    }

    /**
     * [loginout 登出操作]
     * @return [type] [description]
     */
    public function loginout( Request $request )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $state = User::where('id',$user->id)->update(['api_token'=>rand().rand()]);
        if($state){
            return response()->json(['status'=>0,'message'=>'已登出']);
        }
        return response()->json(['status'=>405,'message'=>'登出失败']);

    }

    /**
     * [getVerifyCode 发送手机验证码]
     * @param  Request $request [description]
     * @param  [type]  $type    [1 绑定 2解绑 3修改密码]
     * @param  [type]  $phone   [description]
     * @return [type]           [description]
     */
    public function getVerifyCode( Request $request, $type, $phone)
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        $ip = $request->getClientIp();
        \Log::info("VerifyCode-----Phone:" . $phone . "----Ip:" . $ip . " -----Parm:" . json_encode($request->all()));
        if($redis = Redis::ping()){
            Redis::select(7);
            $sendVerifyCodeTimes = Redis::hget(self::sendVerifyCodeTimes,$ip);
            if($sendVerifyCodeTimes && $sendVerifyCodeTimes >= self::sendVerifyCodeLimit){
                return response()->json(['status'=>405,'message'=>'请求次数过多，请稍后再试']);
            }
        }

        if(!$type || !$phone){
            return response()->json(['status' => 400, 'message' => '参数异常']);
        }

        if(!preg_match("/^1[34578]\d{9}$/", $phone)){
            return response()->json(['status'=>405,'message'=>'请填写正确手机号']);
        }

        if(in_array($type, [1,2])){
            $cacheKey = 'verify_code_'.$user->id.$phone;
        }else{
            $cacheKey = 'verify_code_'.$user->phone;
        }

        //通过手机查询主表用户信息
        $isset = User::findByPhone($phone);

        //type为1 获取绑定验证码  判断手机是否已绑定
        if( $type == 1 && $isset ){
            return response()->json(['status'=>406,'message'=>'手机已绑定']);
        }
        if( $type == 1 && $user->phone != '' ){
            return response()->json(['status'=>405,'message'=>'用户已绑定手机']);
        }

        //检测redis
        if( !$redis = Redis::ping() ){
            return response()->json(['status'=>405,'message'=>'系统异常，请稍后再试']);
        }

        Redis::select(7);
        //生成随机数和缓存key
        $verifyCode = rand(1000,9999);


        //检测重复发送
        if( !Redis::setNx($cacheKey, $verifyCode) ){
           return response()->json(['status'=>405,'message'=>'短信已发送，请稍后再试']);
        }

        Redis::expire( $cacheKey,300 );

        //return response()->json(['status'=>0,'message'=>$verifyCode]);
        //TODO
        //发送短信
        $luosimao = new Sms();
        $deposit = $luosimao->get_deposit();

        if(!is_array($deposit)){
            return response()->json(['status'=>405,'message'=>'短信发送失败']);
        }

        //判断接口状态和短信剩余条数
        if($deposit['error'] != 0 || $deposit['deposit'] < 1  ){
            \Log::info('短信接口异常,异常情况:'.$deposit);
            return response()->json(['status'=>405,'message'=>'短信发送失败，请稍后再试']);
        }

        try{
            $message = '验证码为：'.$verifyCode.'。【趣玩娱乐】';
            $info = $luosimao->send($phone,$message);

            if( $info['error'] == 0 ){
                Redis::hIncrBy(self::sendVerifyCodeTimes,$ip,1);
                return response()->json(['status'=>0,'message'=>'验证码已发送']);
            }
            \Log::info('短信发送返回结果：'.json_encode([$info,$phone,$verifyCode]));
            Redis::del($cacheKey);
            return response()->json(['status'=>505,'message'=>'短信发送失败，请稍后再试']);
        }catch(\Exception $e){
            Redis::del($cacheKey);
            return response()->json(['status'=>506,'message'=>'短信发送失败，请稍后再试']);
        }

    }

    /**
     * [getCaptcha 获取图形验证码]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getCaptcha( Request $request, $uuid ='')
    {
        if ($request->isMethod('post')){
            $rules = ['captcha' => 'required|captcha'];
            $messages = [
                'captcha'=>':attribute 验证码错误',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()){
                $errors = $validator->errors();
                $messages = $errors->toArray();
                var_dump($messages);die;
                $message  = array_shift($messages);
                $response = [
                    'status'    =>  405,
                    'message'   => $message[0]
                ];
                return response()->json($response);
            }else{
                return response()->json(['status'=>0,'message'=>'OK']);
            }
        }
        $a = new Captcha();

        $phrase = new PhraseBuilder;
        // 设置验证码位数
        $code = $phrase->build(6);
        // 生成验证码图片的Builder对象，配置相应属性
        $builder = new CaptchaBuilder($code, $phrase);
        // 设置背景颜色
        $builder->setBackgroundColor(220, 210, 230);
        $builder->setMaxAngle(25);
        $builder->setMaxBehindLines(0);
        $builder->setMaxFrontLines(0);
        // 可以设置图片宽高及字体
        $builder->build($width = 100, $height = 40, $font = null);
        // 获取验证码的内容
        $phrase = $builder->getPhrase();
        // 把内容存入session
        \Session::flash('code', $phrase);
        // 生成图片   此处要设置浏览器不要缓存
        header("Cache-Control: no-cache, must-revalidate");
        header("Content-Type:image/jpeg");
        $builder->output();
    }


    /**
     * [markPromote 个人推广页]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function markPromote( Request $request )
    {
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        if(!$UcUser = UcUser::findById($user->uc_id)){
            return response()->json(['status' => 404, 'message' => '请先注册']);
        }

        $userRegMembers         = Redis::hget('user_reg_members',$user->uc_id);
        //$userResPointTotal      = Redis::hget('user_respoint_total',$user->uc_id);
        $promoteRechargeMembers = Redis::hget('user_promote_members',$user->uc_id);
        $promoteRechargeTotal   = Redis::hget('user_promote_recharge_total',$user->uc_id);

        $data['userRegMembers']         = $userRegMembers ? $userRegMembers : 0;
        $data['userResPointTotal']      = $UcUser->signage_points;
        $data['promoteRechargeMembers'] = $promoteRechargeMembers ? $promoteRechargeMembers :0;
        $data['promoteRechargeTotal']   = $promoteRechargeTotal ? $promoteRechargeTotal : 0;

        //获取配置
        $url = self::getConfig('signage_url');
        $exchange = self::getConfig('exchange_list');

        if($UcUser->signage == ''){
            $UcUser->signage = Str::random(11);
            $UcUser->save();
        }
        $data['promoteUrl'] = isset($url['description']) ? $url['description'].$UcUser->signage : '';

        $data['exchange']   = isset($exchange['content']) ? json_decode($exchange['content'][0],true) : [];

        return response()->json(['status'=>0,'message'=>'OK','data'=>$data]);
    }

    /**
     * [doExchange 推广红利换购]
     * @param  Request $request [description]
     * @param  [type]  $porint  [消费积分]
     * @return [type]           [description]
     */
    public function doExchange( Request $request,$porint )
    {
        if( !$porint ){
            return response()->json(['status' => 405, 'message' => '请刷新页面重试']);
        }

        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }

        if(!$UcUser = UcUser::findById($user->uc_id)){
            return response()->json(['status' => 404, 'message' => '请先注册']);
        }

        if( $UcUser->signage_points < $porint ){
            return response()->json(['status' => 405, 'message' => '积分不足']);
        }

        $exchange = self::getConfig('exchange_list');
        if(!$exchange){
            return response()->json(['status' => 405, 'message' => '异常，请联系客服']);
        }

        $exchange = json_decode($exchange['content'][0],true);

        $exDetail = array_first($exchange,function($value,$key) use ($porint) {
            if($value['use'] == $porint){
                return $value;
            }
        });

        //如果获取不到表示非法请求或后台参数有变动
        if(!$exDetail){
            return response()->json(['status' => 405, 'message' => '参数异常，请刷新页面或联系客服']);
        }
        //换购VIP 扣除积分
        $extime = UserVip::createVip($user->id,$exDetail['days'],'',2);
        $UcUser->signage_points = $UcUser->signage_points-$porint;
        $UcUser->save();
        UserConsumptionLog::create(['user_id'=>$user->id,'use_porint'=>$porint,'unit'=>'天','type'=>1]);

        return response()->json(['status' => 0, 'message' => 'ok','data'=>['expire_at'=>strtotime($extime['expire_at'])]]);
    }


    /**
     * [getConfig 获取配置]
     * @param  [type] $method [description]
     * @return [type]         [description]
     */
    public  static function getConfig($method)
    {
        try{
            if( $config = Redis::hget('qvod_config',$method)){
                $config = json_decode($config,true);
            }else{
                $config = Config::getConfigByAlias($method);
                $detail = $config ? json_encode($config) : null;
                Redis::hSet('qvod_config',$method,$detail);
            }
        }catch(\Exception $e){
             $config = Config::getConfigByAlias($method);
        }

        return $config;
    }


    /**
     * [isMute 判断用户是否被禁言]
     * @param  [type]  $ucuserId [description]
     * @return boolean           [description]
     */
    private static function isMute($userId)
    {
        try{
            Redis::select(4);
            $isMute = Redis::get('user_mute_'.$userId);
            $isMute = $isMute ? $isMute : 0;
        }catch(\Exception $e){
            $isMute = 0;
        }

        return $isMute;
    }
  
}
