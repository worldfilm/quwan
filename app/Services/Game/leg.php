<?php
namespace App\Services\Game;
require_once 'plug/utils.php';
use Exception;
use App\Services\GameService;
use App\Model\User;
use DB;
use Illuminate\Support\Facades\Redis;
use App\Model\Config;

/**
 * 乐游
 */
class leg
{
    public function __construct(){
        $config_data = GameService::getGameConfig('leg');
        $config = json_decode($config_data['content'],true);
        $this->agent = $config['agent'];
        $this->desKey = $config['desKey'];
        $this->md5Key = $config['md5Key'];
        $this->lineCode = $config['lineCode'];
        $this->apiUrl = $config['apiUrl'];
        $this->recordUrl = $config['recordUrl'];
        $this->platform = 'Leg';
    }

    public function login( $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $KindID = $request->input('kind_id','0');
        $account = $user->username;
        $timestamp = time().rand(100,999);
        $param = http_build_query([
            's'         => 0,
            'account'   => $account,
            'money'     => 0,
            'orderid'   => $this->agent.date('YmdHis').$account,
            'ip'        => "127.0.0.1",
            'lineCode'  => $this->lineCode,
            'KindID'    => $KindID,
            'lang'      => 'zh-CN'
        ]);

        $url = $this->apiUrl . '?' . http_build_query([
                'agent'     => $this->agent,
                'timestamp' => $timestamp,
                'param'     => desEncode($this->desKey, $param),
                'key'       => md5($this->agent.$timestamp.$this->md5Key)
            ]);

        $res = curl_get_content($url);
        $data = json_decode($res,true);
        $code = $data['d']['code'];
        $msg = !empty($this->error_code[$code]) ? $this->error_code[$code] : "null";

        if( $code === 0 ){
            $gameUrl = $data['d']['url'];
            $host = 'http://' . $request->server('HTTP_HOST') . '/api/game/index?api_token=' . $user->api_token;
            $host = urlencode($host);
            //添加返回键
            $gameUrl = $gameUrl . '&backUrl=' . $host . '&jumpType=3';
            return response()->json( ['status' => 0, 'message' => 'OK', 'url' => $gameUrl] );
        } else {
            return response()->json(['status' => $code, 'message' => $msg]);
        }
    }

    public function transfer( $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $method = $request->input('method'); //转入in转出out
        $money = $request->input('money');
        if(empty($method) || !in_array($method,['in', 'out'])){
            return response()->json(['status' => 403, 'message' => '参数错误']);
        }
        if(empty($money) || $money <= 0){
            return response()->json(['status' => 404, 'message' => '金额错误']);
        }
        Redis::select(14);
        $userId = $user->id;
        if($method == 'in'){
            if( $money > $user->deposit ){
                return response()->json(['status' => 405, 'message' => '余额不足,请先充值']);
            }
            //转入时先扣钻石,然后再执行转账
            $result = GameService::changeDeposit($userId, $money, $method);
            //生成转账记录
            if($result == 'success'){
                $record_id = GameService::makeTransferId($user, $method, $money,$this->platform);
            }else{
                return response()->json(['status' => 406, 'message' => '转账失败']);
            }
        }else{
            $record_id = GameService::makeTransferId($user, $method, $money,$this->platform);
        }

        //组织参数发起请求
        $returnDatas = $this->performTransfer($user, $method, $money);
        $code = $returnDatas['code'];
        $msg  = $returnDatas['msg'];
        $order_id = $returnDatas['order_id'];

        if( $code === 0 ){
            $transfer_status = 1;
            $response = ['status' => 0, 'message' => 'OK'];
            if($method == 'out'){
                //转出时执行转账成功,再加钻石
                GameService::changeDeposit($userId, -1*$money, $method);
            }
        } else {
            $transfer_status =  0;
            $response = ['status' => $code, 'message' => $msg];
        }
        $user_deposit = User::where('id', $userId)->pluck('deposit')->toArray();

        $transferRecord = [
            'order_id'      => $order_id,
            'afterAmount'   => $user_deposit[0],
            'status'        => $transfer_status,
            'msg'           => $msg,
            'updated_at'    => date('Y-m-d H:i:s')
        ];
        //修改游戏平台返回状态
        DB::table('transfer_record')->where('id', $record_id)->update($transferRecord);

        return response()->json($response);
    }

    public function getBalance( $request ){
        if( !$user = $request->user() ) {
            return ['status' => 401, 'message' => '请先登录'];
        }
        $account = $user->username;
        $timestamp = time().rand(100,999);
        $param = http_build_query([
            's'         => 1,
            'account'   => $account
        ]);

        $url = $this->apiUrl . '?' . http_build_query([
                'agent'     => $this->agent,
                'timestamp' => $timestamp,
                'param'     => desEncode($this->desKey, $param),
                'key'       => md5($this->agent.$timestamp.$this->md5Key)
            ]);

        $res = curl_get_content($url);
        $data = json_decode($res,true);
        $code = $data['d']['code'];
        $msg = !empty($this->error_code[$code]) ? $this->error_code[$code] : "null";

        if( $code == 0 ){
            $money = $data['d']['money'];
            return ['status' => 0, 'message' => '请求成功','game_money' => sprintf("%.2f",$money)];
        } else if( $code == 35 ){
            $param = http_build_query([
                's'         => 0,
                'account'   => $account,
                'money'     => 0,
                'orderid'   => $this->agent.date('YmdHis').$account,
                'ip'        => "127.0.0.1",
                'lineCode'  => $this->lineCode,
                'KindID'    => 0,
                'lang'      => 'zh-CN'
            ]);
            $url = $this->apiUrl . '?' . http_build_query([
                    'agent'     => $this->agent,
                    'timestamp' => $timestamp,
                    'param'     => desEncode($this->desKey, $param),
                    'key'       => md5($this->agent.$timestamp.$this->md5Key)
                ]);
            curl_get_content($url);
            return ['status' => 0, 'message' => '请求成功','game_money' => '0.00'];
        } else {
            return ['status' => 101, 'message' => '失败','game_money' => ''];
        }
    }

    public function getRecord(){
        $start_time = (time() - 300)."000"; //每分钟拉前5分钟的游戏记录
        $end_time = time()."000";
        $timestamp = time().rand(100,999);
        $param = http_build_query([
            's'         => 6,
            'startTime' => $start_time,
            'endTime'   => $end_time
        ]);

        $url = $this->recordUrl . '?' . http_build_query([
                'agent'     => $this->agent,
                'timestamp' => $timestamp,
                'param'     => desEncode($this->desKey, $param),
                'key'       => md5($this->agent.$timestamp.$this->md5Key)
            ]);

        $res = curl_get_content($url);
        $data = json_decode($res,true);
        $code = $data['d']['code'];
        $msg = !empty($this->error_code[$code]) ? $this->error_code[$code] : "null";

        if( $code == 0 ){
            $count = $data['d']['count'];
            $list = $data['d']['list'];
            $nowTime = date('Y-m-d H:i:s');
            if($count > 0){
                $record = [];
                $tipsData = Config::getConfigByAlias('winners_tips_threshold');
                $tipsDataVal = json_decode($tipsData['content'][0],true);
                $tipsDataVal = $tipsDataVal ? $tipsDataVal : 15;
                Redis::select(14);
                for($i = 0; $i < $count; $i++){
                    $gameCode = $this->game_code;
                    $tmp['game_id'] = $list['GameID'][$i];
                    $tmp['username'] = substr($list['Accounts'][$i],strlen($list['ChannelID'][$i])+1);
                    $tmp['kind_id'] = isset($gameCode[$list['KindID'][$i]]) ? $gameCode[$list['KindID'][$i]] : "游戏".$list['KindID'][$i];
                    $tmp['bet'] = $list['CellScore'][$i];
                    $tmp['profit'] = $list['Profit'][$i];
                    $tmp['start_time'] = $list['GameStartTime'][$i];
                    $tmp['end_time'] = $list['GameEndTime'][$i];
                    $tmp['created_at'] = $nowTime;
                    $tmp['updated_at'] = $nowTime;
                    $tmp['platform'] = $this->platform;
                    $has = DB::table('game_record')->where('game_id',$tmp['game_id'])->first();

                    if(!$has){
                        $record[] = $tmp;
                        $profit = $tmp['profit'] * 10;
                        if($profit >= $tipsDataVal){
                            //$userInfo = DB::table( 'uc_users' )->where( 'username', $insert_tmp['username'] )->first();
                            //$nickname = $userInfo ? $userInfo->nickname : "j***s";
                            $platform = lcfirst($this->platform);
                            $member = DB::table( 'game_members' )
                                ->where( 'game_platform', $platform )
                                ->where( 'kind_id', $list['KindID'][$i])
                                ->first();
                            $icon_url = $member ? $member->icon_url : 'http://play.zdzlw.com/img/v/vapp/quwan/gamelist/default_icon.png';
                            $game_title = $member ? $member->game_title : '乐游' . $tmp['kind_id'];
                            $data_arr = [
                                'record_id' => $tmp['game_id'],
                                'username' => $tmp['username'],
                                'profit' => $profit,
                                'kind_id' => $list['KindID'][$i],
                                'platform' => $this->platform,
                                'icon_url' => $icon_url,
                                'game_title' => $game_title
                            ];
                            Redis::lPush('winners_tips_list', json_encode($data_arr));
                        }
                    }
                }
                DB::table('game_record')->insert($record);
            }
            return response()->json( ['status' => 0, 'message' => 'OK', 'data' => $count] );
        } else {
            return response()->json(['status' => $code, 'message' => $msg]);
        }
    }

    //组合参数请求
    public function performTransfer($user, $method, $money){
        $s = $method == 'in' ? 2 : 3;
        $account = $user->username;
        $timestamp = time().rand(100,999);
        $order_id = $this->agent.date('YmdHis').$account;
        $param = http_build_query([
            's'         => $s,
            'account'   => $account,
            'money'     => $money/10,
            'orderid'   => $order_id
        ]);

        $url = $this->apiUrl . '?' . http_build_query([
                'agent'     => $this->agent,
                'timestamp' => $timestamp,
                'param'     => desEncode($this->desKey, $param),
                'key'       => md5($this->agent.$timestamp.$this->md5Key)
            ]);

        $res = curl_get_content($url);
        $data = json_decode($res,true);
        $code = $data['d']['code'];
        $msg = !empty($this->error_code[$code]) ? $this->error_code[$code] : "null";

        return ['code' => $code, 'order_id' => $order_id, 'msg' => $msg];
    }

    //游戏代码
    private $game_code = [
        '0'     => '大厅',
        '620'   => '德州扑克',
        '720'   => '二八杠',
        '830'   => '抢庄牛牛',
        '220'   => '炸金花',
        '860'   => '三公',
        '900'   => '押庄龙虎',
        '600'   => '21点',
        '870'   => '通比牛牛',
        '880'   => '欢乐红包',
        '230'   => '极速炸金花',
        '730'   => '抢庄牌九',
        '630'   => '十三水',
        '380'   => '幸运五张',
        '610'   => '斗地主',
        '390'   => '射龙门',
        '910'   => '百家乐',
        '920'   => '森林舞会',
        '930'   => '百人牛牛',
        '890'   => '看牌抢庄牛牛',
        '740'   => '二人麻将',
        '1950'  => '万人炸金花'
    ];

    //错误码
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
