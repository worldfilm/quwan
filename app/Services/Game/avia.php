<?php
namespace App\Services\Game;

use Exception;
use App\Services\GameService;
use App\Model\User;
use DB;
use Illuminate\Support\Facades\Redis;

/**
 * 泛亚电竞
 */
class avia
{
    public function __construct(){
        $config_data = GameService::getGameConfig('avia');
        $config = json_decode($config_data['content'],true);
        $this->apiUrl = $config['apiUrl'];
        $this->accessToken = $config['accessToken'];
        $this->prifix = 'avia';
        $this->platform = 'Avia';
    }

    /**
     * 登陆创建账号接口
     */
    public function login( $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $result = $this->loginAccount( $user );

        if($result['success'] == 1){
            return response()->json( ['status' => 0, 'message' => 'OK', 'data' => $result['info']['Url']] );
        }elseif($result['success'] == 0 && $result['info']['Error'] == 'NOUSER'){
            //未创建账号，先创建
            $create_data = $this->createdAccount( $user );

            if($create_data['success'] == 1){
                //创建账号成功，再次登录
                $login_data = $this->loginAccount( $user );
                if($login_data['success'] == 1){
                    return response()->json( ['status' => 0, 'message' => 'OK', 'url' => $login_data['info']['Url']] );
                }else{
                    return response()->json( ['status' => 103, 'message' => '请求失败'] );
                }
            }else{
                return response()->json( ['status' => 102, 'message' => $result['info']] );
            }
        }else{
            return response()->json( ['status' => 101, 'message' => '请求失败'] );
        }
    }

    /**
     * 转账
     */
    public function transfer($request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $method = $request->input('method'); //转入in转出out
        $money = $request->input('money');
        $money = intval($money);
        if (empty($method) || !in_array($method, ['in', 'out'])) {
            return response()->json(['status' => 403, 'message' => '参数错误']);
        }
        if (empty($money) || $money <= 0 || !is_int(($money / 10))) {
            return response()->json(['status' => 404, 'message' => '金额错误,只支持整形']);
        }
        $userId = $user->id;

        if ($method == 'in') {
            if ($money > $user->deposit) {
                return response()->json(['status' => 405, 'message' => '余额不足,请先充值']);
            }
            //转入时先扣钻石,然后再执行转账
            $result = GameService::changeDeposit($userId, $money, $method);

            //生成转账记录
            if ($result == 'success') {
                $record_id = GameService::makeTransferId($user, $method, $money,$this->platform);
            }
        } else {
            $record_id = GameService::makeTransferId($user, $method, $money,$this->platform);
        }

        //组织参数发起请求
        $order_id = $this->prifix . $user->id . date('YmdHis');
        $data = [];
        $data['UserName'] = $this->prifix . $user->username;
        $data['Money'] = $money / 10;
        $data['Type'] = $method == 'in' ? 'IN' : 'OUT';
        $data['ID'] = $order_id;
        $url = $this->apiUrl . '/api/user/transfer';
        $result = $this->post_request($url, $data);

        if ($result['success'] == 1) {
            $transfer_status = 1;
            if ($method == 'out') {
                //转出时执行转账成功,再加钻石
                GameService::changeDeposit($userId, -1 * $money, $method);
            }
            $msg = '成功';
            $response = ['status' => 0, 'message' => 'OK'];
        } else {
            $msg = $result['msg'];
            $transfer_status = 0;
            $response = ['status' => 102, 'message' => $msg];
        }

        $user_deposit = User::where('id', $userId)->pluck('deposit')->toArray();
        $transferRecord = [
            'order_id' => $order_id,
            'afterAmount' => $user_deposit[0],
            'status' => $transfer_status,
            'msg' => $msg,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        //修改游戏平台返回状态
        DB::table('transfer_record')->where('id', $record_id)->update($transferRecord);

        return response()->json($response);
    }


    /**
     * 查询余额接口
     */
    public function getBalance( $request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $data = [
            'UserName' => $this->prifix . $user->username,
        ];
        $url = $this->apiUrl . '/api/user/balance';
        $result = $this->post_request($url, $data);
        if ($result['success'] == 1) {
            $game_money = $result['info']['Money'];
            return ['status' => 0, 'message' => '请求成功','game_money' => sprintf("%.2f",$game_money)];
        } elseif($result['info']['Error'] == 'NOUSER'){
            $login_data = $this->createdAccount( $user );
            if($login_data['success'] == 1){
                return ['status' => 0, 'message' => 'OK', 'game_money' => '0.00'];
            }
        }else {
            return ['status' => 101, 'message' => '请求失败' . $result['msg'],'game_money' => ''];
        }
    }

    // 登录账号
    public function loginAccount($user){
        $data = [
            'username' => $this->prifix . $user->username,
        ];
        $url = $this->apiUrl . '/api/user/login';
        $result = $this->post_request($url, $data);
        return $result;
    }

    // 创建游戏账号
    public function createdAccount($user){
        $data = [
            'UserName'    => $this->prifix . $user->username,
            'password'    => $this->prifix . $user->username
        ];
        $url = $this->apiUrl . '/api/user/register';
        $result = $this->post_request($url, $data);
        return $result;
    }

    //定时获取游戏记录
    public function getRecord()
    {
        //每两分钟拉前10分钟的游戏记录
        $start_time = date('Y-m-d H:i:s', (time() - 601));
        $end_time = date('Y-m-d H:i:s');
        $data = [
            'Type'    => 'UpdateAt',
            'StartAt'    => $start_time,
            'EndAt'    => $end_time,
            'StartAt'    => $start_time,
        ];
        $url = $this->apiUrl . '/api/log/get';
        $result = $this->post_request($url, $data);

        if ($result['success'] == 1) {
            if ($result['list'] > 0) {
                $record = [];
                $nowTime = date('Y-m-d H:i:s');
                foreach ($result['list'] as $k => $v) {
                    $record_id = $v['OrderID'];
                    $has = DB::table('game_record')->where('game_id',$record_id)->first();
                    if (!$has) {
                        $insert_tmp['game_id'] = $record_id;
                        $insert_tmp['username'] = ltrim($v['UserName'], $this->prifix);
                        $insert_tmp['kind_id'] = $v['Category'];
                        $insert_tmp['bet'] = $v['"BetAmount'];
                        $insert_tmp['profit'] = $v["Money"];
                        $insert_tmp['start_time'] = $v['StartAt'];
                        $insert_tmp['end_time'] = $v['EndAt'];
                        $insert_tmp['created_at'] = $nowTime;
                        $insert_tmp['updated_at'] = $nowTime;
                        $insert_tmp['platform'] = $this->platform;
                        //取主播id
                        $join_game_hall_way = 'join_game_hall_way';
                        $zb_user_id = Redis::hGet($join_game_hall_way, $insert_tmp['username']);
                        $insert_tmp['zb_user_id'] = $zb_user_id ? $zb_user_id : 0;
                        $record[] = $insert_tmp;
                    }
                }
                if (count($record) > 0) {
                    $res = DB::table('game_record')->insert($record);
                }
            }
            return 'success';
        }
    }

    public function post_request($url, $data){
        $ch = curl_init();
        $str = '';
        foreach ( $data as $k => $v )
        {
            $str.= "$k=" . urlencode( $v ). "&" ;
        }
        $str = rtrim($str, '&');
        $header[] = 'Authorization:'.$this->accessToken;
        $header[] = 'Content-length:'.strlen ($str);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        curl_setopt($ch, CURLOPT_POST,true);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output, true);
    }

    private $error_code = [
        '0'     => "成功",
        '1'     => "TOKEN丢失,请重新调用登录接口获取",
        '2'     => "渠道不存在,请检查渠道ID是否正确",
        '3'     => "验证时间超时,请检查timestamp是否正确",
        '4'     => "验证错误",
        '5'     => "渠道白名单错误,请联系客服添加服务器白名单",
        '6'     => "验证字段丢失,请检查参数完整性",
        '8'     => "不存在的请求,请检查子操作类型是否正确",
        '16'    => "数据不存在,当前没有注单",
        '34'    => "订单重复",
        '38'    => "余额不足导致下分失败",
        '43'    => "拉单过于频繁,两次拉单时间间隔必须大于5秒",
        '1001'  => "注册会员账号系统异常",
        '1002'  => "代理商金额不足"
    ];
}
