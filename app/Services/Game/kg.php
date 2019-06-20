<?php
namespace App\Services\Game;
require_once 'plug/api_action.php';
use Exception;
use App\Services\GameService;
use App\Model\User;
use DB;
use Illuminate\Support\Facades\Redis;
use App\Model\Config;
/**
 * KG
 */
class kg
{
    public function __construct(){
        $config_data = GameService::getGameConfig('kg');
        $config = json_decode($config_data['content'],true);
        $this->DO_URL = $config['DO_URL'];
        $this->FW_URL = $config['FW_URL'];
        $this->LANGUAGE = "zh-cn";
        $this->CURRENCY = "CNY";
        $this->ODD_TYPE = "A";
        $this->test_CTGent = $config['test_CTGent'];
        $this->test_MD5_KEY = $config['test_MD5_KEY'];

        $this->prifix = 'kg';
        $this->homeUrl = "http://www.baidu.com";

        $this->platform = 'Kg';
    }

    private $game_code = [
        '1028'     => '极乐 BAR',
        '1027'   => '香姬水果盘',
        '1026'   => '高潮甜点屋',
        '1025'   => '和室里的激情',
        '1024'   => '换妻公寓',
        '1023'   => '千菜的调教教室',
        '1022'   => '女王啪啪啪',
        '1021'   => 'G 奶俱乐部',
        '1020'   => '豆腐西施淫乱觉醒',
        '1019'   => '旬果的发情诱惑',
        '1018'   => '神之乳 RION',
        '1017'   => '忧の无限中出约会',
        '1016'   => '女大生调教日记',
        '1015'   => '快感‧三上悠亚',
        '1014'   => '明日花潮吹大作战',
        '1013'   => '淫乱学园',
        '1012'   => '性爱诊疗室',
        '1011'   => '乳姬无双',
        '0015'   => '梯子游戏',
        '0016'   => '甜心福袋',
        '1001'   => '阿拉伯之夜',
        '1002'  => '琅琊传奇',
        '1003'   => 'KISS 一夏',
        '1004'   => '七姬的诱惑',
        '1005'   => '神探金瓶梅',
        '1006'   => '武媚传奇',
        '1007'   => '萌娘学园',
        '1008'   => '哥是传说',
        '1009'  => '甜心‧三上悠亚',
        '1010'  => '魔女道',
    ];

    /**
     * 登录接口
     * @var string
     */

    public function login( $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $crtParams = [];
        $crtParams['Loginname'] = $this->prifix . $user->username;
        $crtParams['SecureToken'] = $user->id;
        $crtParams['NickName'] = $user->nickname;
        $crtParams['Cur'] = $this->CURRENCY;
        $crtParams['Oddtype'] = $this->ODD_TYPE;
        $result = api_player_create($this->test_CTGent, $this->test_MD5_KEY, $crtParams, $this->DO_URL);
        $result = json_decode($result, true);
        if($result['Status'] == 1){
            return response()->json(['status' => 0, 'message' => 'OK']);
        }else{
            return response()->json(['status' => 201, 'message' => $result['ErrorMsg']]);
        }
    }

    /**
     * 查询余额接口
     */
    public function getBalance( $request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $data=[];
        $data['Loginname'] = $this->prifix . $user->username;
        $data['Cur'] = $this->CURRENCY;
        $result = api_player_balance($this->test_CTGent, $this->test_MD5_KEY, $data, $this->DO_URL);
        $result = json_decode($result, true);
        if ($result['Status'] == 1) {
            $game_money = $result['Data'];
            return ['status' => 0, 'message' => '请求成功','game_money' => sprintf("%.2f",$game_money)];
        } elseif($result['ErrorCode'] == "M1147"){
            $crtParams = [];
            $crtParams['Loginname'] = $this->prifix . $user->username;
            $crtParams['SecureToken'] = $user->id;
            $crtParams['NickName'] = $user->nickname;
            $crtParams['Cur'] = $this->CURRENCY;
            $crtParams['Oddtype'] = $this->ODD_TYPE;
            $result = api_player_create($this->test_CTGent, $this->test_MD5_KEY, $crtParams, $this->DO_URL);
            $result = json_decode($result, true);
            if($result['Status'] == 1){
                return ['status' => 0, 'message' => '请求成功','game_money' => '0.00'];
            }
        }else {
            return ['status' => 101, 'message' => $result['ErrorMsg'],'game_money' => ''];
        }
    }

    /**
     * 获取游戏地址
     */
    public function getGameUrl( $request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $game_id = $request->game_id;
        $data = [];
        $data['Loginname'] = $this->prifix . $user->username;
        $data['SecureToken'] = $user->id;
        $data['Cur'] = $this->CURRENCY;
        $data['GameId'] = $game_id;
        $data['Oddtype'] = $this->ODD_TYPE;
        $data['Lang'] = $this->LANGUAGE;
        $data['HomeURL'] = $this->homeUrl;

        $url = api_fw_game_opt($this->test_CTGent, $this->test_MD5_KEY,$data, $this->FW_URL)->Data;
        if ($url) {
            return response()->json(['status' => 0, 'message' => '请求成功', 'data' => $url]);
        } else {
            return response()->json(['status' => 101, 'message' => '请求失败']);
        }
    }

    /**
     *转账接口
     */
    public function transfer($request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $method = $request->input('method'); //转入in转出out
        $money = $request->input('money');
        if (empty($method) || !in_array($method, ['in', 'out'])) {
            return response()->json(['status' => 403, 'message' => '参数错误']);
        }
        if (empty($money) || $money <= 0) {
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
        $order_id = $this->test_CTGent . $user->id . date('YmdHis');
        $data = [];
        $data['Loginname'] = $this->prifix . $user->username;
        $data['Billno'] = $order_id;
        $data['Credit'] = $money / 10;
        $data['Cur'] = $this->CURRENCY;

        if ($method == 'in') {
            $result = api_player_trans_deposit($this->test_CTGent, $this->test_MD5_KEY, $data, $this->DO_URL);
        } else {
            $result = api_player_trans_withdrawal($this->test_CTGent, $this->test_MD5_KEY, $data, $this->DO_URL);
        }

        $result = json_decode($result, true);
        if ($result['Status'] == 1) {
            $transfer_status = 1;
            if ($method == 'out') {
                //转出时执行转账成功,再加钻石
                GameService::changeDeposit($userId, -1 * $money, $method);
            }
            $msg = '成功';
            $response = ['status' => 0, 'message' => 'OK'];
        } else {
            $msg = $result['ErrorMsg'];
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

    //定时获取游戏记录
    public function getRecord()
    {
        //每两分钟拉前10分钟的游戏记录
        $start_time = date('Y-m-d H:i:s', (time() - 601));
        $end_time = date('Y-m-d H:i:s');
        $data = [];
        $data['Start'] = $start_time;
        $data['End'] = $end_time;
        $data['PageNum'] = 1;
        $result = api_player_getRecordsWithDateOnPage($this->test_CTGent, $this->test_MD5_KEY, $data, $this->DO_URL);
        $result = json_decode($result, true);
        if ($result['Status'] == 1) {
            if ($result['Data'] > 0) {
                $record = [];
                $nowTime = date('Y-m-d H:i:s');
                $tipsData = Config::getConfigByAlias('winners_tips_threshold');
                $tipsDataVal = json_decode($tipsData['content'][0],true);
                $tipsDataVal = $tipsDataVal ? $tipsDataVal : 15;
                foreach ($result['Data'] as $k => $v) {
                    $record_id = $v['BillNo'];
                    $has = DB::table('game_record')->where('game_id',$record_id)->first();
                    if (!$has) {
                        $gameCode = $this->game_code;
                        $insert_tmp['game_id'] = $v['GameID'];
                        $insert_tmp['username'] = ltrim($v['Account'], $this->prifix);
                        $insert_tmp['kind_id'] = isset($gameCode[$v['GameID']]) ? $gameCode[$v['GameID']] : "游戏".$v['GameID'];
                        $insert_tmp['bet'] = $v['"BetValue'];
                        $insert_tmp['profit'] = $v["NetAmount"];
                        $insert_tmp['start_time'] = $v['SettleTime'];
                        $insert_tmp['end_time'] = $v['SettleTime'];
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
                                ->where( 'kind_id', $v['GameID'])
                                ->first();
                            $icon_url = $member ? $member->icon_url : 'http://play.zdzlw.com/img/v/vapp/quwan/gamelist/default_icon.png';
                            $game_title = $member ? $member->game_title : 'Kg' . $insert_tmp['kind_id'];
                            $data_arr = [
                                'record_id' => $insert_tmp['game_id'],
                                'username' => $insert_tmp['username'],
                                'profit' => $profit,
                                'kind_id' => $v['GameID'],
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
}
