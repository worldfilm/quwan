<?php
namespace App\Services\Game;
require_once 'plug/utils761.php';
use Exception;
use App\Services\GameService;
use App\Model\User;
use DB;
use Illuminate\Support\Facades\Redis;
use App\Model\Config;

/**
 * ig
 */
class ig
{
    public function __construct(){
        $config_data = GameService::getGameConfig('ig');
        $config = json_decode($config_data['content'],true);
        $this->config_arr = [
            'AGENT' => $config['agentid'],
            'APPKEY' => $config['appkey'],
            'APIURL' => $config['apiurl'],
        ];
        $this->prifix = 'ig';
        $this->platform = 'Ig';
    }

    /**
     * 登陆创建账号接口
     */
    public function login( $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $host = 'http://' . $request->server('HTTP_HOST') . '/api/game/index?api_token=' . $user->api_token;
        $params = array(
            "acc" => $this->prifix . $user->username,
            "rtnurl" => $host,
        );
        if($request->input('kind_id')){
            $params['game'] = $request->input('kind_id');
        }
        $result =  CallAPI("login",$params,$this->config_arr);

        if($result['code'] === 0){
            return response()->json( ['status' => 0, 'message' => 'OK', 'url' => $result['d']['url']] );
        }else{
            return response()->json( ['status' => 101, 'message' => $result['m']] );
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
        if (empty($money) || $money <= 0 ) {
            return response()->json(['status' => 404, 'message' => '金额错误']);
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
        $params = array(
            "acc" => $this->prifix . $user->username,
            "orderid" => $order_id,
            "money" => $money/ 10,
        );
        $acition = $method == 'in' ? "payup" : "paydown";

        $result = CallAPI($acition, $params, $this->config_arr);
        if ($result['code'] === 0) {
            $transfer_status = 1;
            if ($method == 'out') {
                //转出时执行转账成功,再加钻石
                GameService::changeDeposit($userId, -1 * $money, $method);
            }
            $msg = '成功';
            $response = ['status' => 0, 'message' => 'OK'];
        } else {
            $msg = $result['m'];
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
        $params = array(
            "acc" => $this->prifix . $user->username
        );
        $result =  CallAPI("getbalance",$params,$this->config_arr);

        if ($result['code'] === 0) {
            $game_money = $result['d']['balance'];
            return ['status' => 0, 'message' => '请求成功','game_money' => sprintf("%.2f",$game_money)];
        } elseif($result['code'] == 10){
            $params = array(
                "acc" => $this->prifix . $user->username,
                "rtnurl" => '',
            );
            $result =  CallAPI("login",$params,$this->config_arr);
            if($result['code'] === 0){
                return ['status' => 0, 'message' => '请求成功','game_money' => '0.00'];
            }
        } else {
            return ['status' => 101, 'message' => '请求失败' . $result['m'],'game_money' => ''];
        }
    }

    //定时获取游戏记录
    public function getRecord()
    {
        //每两分钟拉前10分钟的游戏记录
        $start_time = date('Y-m-d H:i:s', (time() - 12601));
        $end_time = date('Y-m-d H:i:s');
        $params = array(
            "stime" => $start_time,
            "etime" => $end_time,
            "pagenum" => 1000,

        );
        $result =  CallAPI("loadrecords", $params, $this->config_arr);

        if ($result['code'] === 0) {
            if ($result['d']['total'] > 0) {
                $record = [];
                $nowTime = date('Y-m-d H:i:s');
                $tipsData = Config::getConfigByAlias('winners_tips_threshold');
                $tipsDataVal = json_decode($tipsData['content'][0],true);
                $tipsDataVal = $tipsDataVal ? $tipsDataVal : 15;
                foreach ($result['d']['data'] as $k => $v) {
                    $record_id = $v['logid'];
                    $has = DB::table('game_record')->where('game_id',$record_id)->first();
                    if (!$has) {
                        $insert_tmp['game_id'] = $record_id;
                        $insert_tmp['username'] = ltrim($v['acc'], $this->prifix);
                        $insert_tmp['kind_id'] = isset($gameCode[$v['kind']]) ? $gameCode[$v['kind']] : "游戏".$v['kind'];
                        $insert_tmp['bet'] = $v['"realput'];
                        $insert_tmp['profit'] = $v["award"];
                        $insert_tmp['start_time'] = $v['stime'];
                        $insert_tmp['end_time'] = $v['ctime'];
                        $insert_tmp['created_at'] = $nowTime;
                        $insert_tmp['updated_at'] = $nowTime;
                        $insert_tmp['platform'] = $this->platform;
                        $record[] = $insert_tmp;

                        $profit = $insert_tmp['profit'] * 10;
                        if($profit >= $tipsDataVal){
                            //$userInfo = DB::table( 'uc_users' )->where( 'username', $insert_tmp['username'] )->first();
                            //$nickname = $userInfo ? $userInfo->nickname : "j***s";
                            $platform = lcfirst($this->platform);
                            $member = DB::table( 'game_members' )
                                ->where( 'game_platform', $platform )
                                ->where( 'kind_id', $v['kind'])
                                ->first();
                            $icon_url = $member ? $member->icon_url : 'http://play.zdzlw.com/img/v/vapp/quwan/gamelist/default_icon.png';
                            $game_title = $member ? $member->game_title : 'Ig' . $insert_tmp['kind_id'];
                            $data_arr = [
                                'record_id' => $insert_tmp['game_id'],
                                'username' => $insert_tmp['username'],
                                'profit' => $profit,
                                'kind_id' => $v['kind'],
                                'platform' => $this->platform,
                                'icon_url' => $icon_url,
                                'game_title' => $game_title
                            ];
                            Redis::select(14);
                            Redis::lPush('winners_tips_list', json_encode($data_arr));
                        }
                    }
                }
                if (count($record) > 0) {
                    $res = DB::table('game_record')->insert($record);
                }
            }
            return 'success';
        }
    }

    //游戏代码
    private $game_code = [
        '310001'  => '二人麻将',
        '310002'  => '红中麻将',
        '320001'  => '四人斗地主',
        '320002'  => '三人斗地主',
        '330001'  => '经典牛牛',
        '330002'  => '看牌抢庄',
        '330003'  => '通比牛牛',
        '330004'  => '抢庄牛牛',
        '111001'  => '十三水',
        '111002'  => '十三水',
        '111003'  => '十三水',
        '111004'  => '十三水',
        '101001'  => '港式五张',
        '101002'  => '港式五张',
        '101003'  => '港式五张',
        '101004'  => '港式五张',
        '91001'  => '二八杠',
        '91002'  => '二八杠',
        '91003'  => '二八杠',
        '91004'  => '二八杠',
        '81001'  => '炸金花',
        '81002'  => '炸金花',
        '81003'  => '炸金花',
        '81004'  => '炸金花',
        '65001'  => '飞禽走兽',
        '65002'  => '飞禽走兽',
        '65003'  => '飞禽走兽',
        '65004'  => '飞禽走兽',
        '63001'  => '龙虎',
        '63002'  => '龙虎',
        '63003'  => '龙虎',
        '63004'  => '龙虎',
        '64001'  => '奔驰宝马',
        '64002'  => '奔驰宝马',
        '64003'  => '奔驰宝马',
        '64004'  => '奔驰宝马',
        '53001'   => '大圣闹海',
        '53002'   => '大圣闹海',
        '53003'   => '大圣闹海',
        '53004'   => '大圣闹海',
        '61001'   => '欢乐 30 秒',
        '61002'   => '欢乐 30 秒',
        '61003'   => '欢乐 30 秒',
        '61004'   => '欢乐 30 秒',
        '62001'  => '百牛',
        '62002'  => '百牛',
        '62003'  => '百牛',
        '62004'  => '百牛',
        '3'     => '注册赠送',
        '9'     => '红包派送',
        '11001'   => '德州扑克',
        '11002'   => '德州扑克',
        '11003'   => '德州扑克',
        '11004'   => '德州扑克',
        '11005'   => '德州扑克',
        '11006'   => '德州扑克',
        '31001'   => '牛牛-随机庄',
        '31002'   => '牛牛-随机庄',
        '31003'   => '牛牛-随机庄',
        '31004'   => '牛牛-随机庄',
        '32001'   => '牛牛-看牌抢庄',
        '32002'   => '牛牛-看牌抢庄',
        '32003'   => '牛牛-看牌抢庄',
        '32004'   => '牛牛-看牌抢庄',
        '33001'   => '牛牛-通比',
        '33002'   => '牛牛-通比',
        '33003'   => '牛牛-通比',
        '33004'   => '牛牛-通比',
        '34001'   => '抢庄牛牛',
        '34002'   => '抢庄牛牛',
        '34003'   => '抢庄牛牛',
        '34004'   => '抢庄牛牛',
        '41001'   => '斗地主',
        '41002'   => '斗地主',
        '41003'   => '斗地主',
        '41004'   => '斗地主',
        '51001'   => '李逵捕鱼',
        '51002'   => '李逵捕鱼',
        '51003'   => '李逵捕鱼',
        '51004'   => '李逵捕鱼',
        '52001'   => '金蟾捕鱼',
        '52002'   => '金蟾捕鱼',
        '52003'   => '金蟾捕鱼',
        '52004'   => '金蟾捕鱼',
    ];
}
