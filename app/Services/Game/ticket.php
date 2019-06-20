<?php
namespace App\Services\Game;
use Exception;
use App\Model\User;
use App\Model\Config;
use DB;
use Illuminate\Support\Facades\Redis;
use App\Services\GameService;

/**
 * 易彩彩票
 */
class ticket
{
    const error_code = [
        '1' => '成功',
        '0' => '其他失败',
        '1001' => '操作失败，请稍后尝试',
        '1002' => '参数错误',
        '1003' => '逻辑事务错误',
        '10054' => 'IP不允许',
        '10039' => '用户已禁用',
        '10053.' => '代理已禁用',
        '10000' => '用户不存在',
        '10007' => '登录密码错误',
        '10010' => '用户名已存在'
    ];
    public function __construct(){
        $config_data = GameService::getGameConfig('ticket');
        $config = json_decode($config_data['content'],true);
        $this->agentid = $config['agentid'];
        $this->url = $config['url'];
        $this->rsa_public = $config['rsa_public'];
        //100042 测试账号
        $this->prifix = $this->agentid == 100042 ? 't17' : 'ysg_';
        $this->platform = 'Ticket';
    }
    /**
     * 返回首页h5
     */
    public function getHtml(Request $request)
    {
        $api_token = $request->input('api_token') ? $request->input('api_token') : $request->header('api_token');
        return view('ticket.index', compact('api_token'));
    }

    /**
     * 登陆创建账号接口
     */
    public function login($request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $host = 'http://' . $request->server('HTTP_HOST') . '/api/game/index?api_token=' . $user->api_token;

        $result = $this->loginAccount($user, $host);

        if ($result['code'] == 1) {
            return response()->json(['status' => 0, 'message' => 'OK', 'url' => $result['info']]);
        } elseif ($result['code'] == 10000) {
            //未创建账号，先创建
            $create_data = $this->createdAccount($user);
            if ($create_data['code'] == 1) {
                //创建账号成功，再次登录
                $login_data = $this->loginAccount($user, $host);
                if ($login_data['code'] == 1) {
                    return response()->json(['status' => 0, 'message' => 'OK', 'url' => $login_data['info']]);
                } else {
                    throw new Exception('请求失败', 103);
                }
            } else {
                throw new Exception($result['info'], 102);
            }
        } else {
            throw new Exception($result['info'], 101);
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

        $data = [
            'username' => $this->prifix . $user->username,
            'password' => 'ysg' . $user->username,
        ];
        $result = $this->request_ticket_content('balance', $data);

        if ($result['code'] == 1) {
            $game_money = $result['info'];
            return ['status' => 0, 'message' => '请求成功','game_money' => sprintf("%.2f",$game_money)];
        }elseif ($result['code'] == 10000) {
            //未创建账号，先创建
            $create_data = $this->createdAccount($user);
            if ($create_data['code'] == 1) {
                return ['status' => 0, 'message' => '请求成功','game_money' => '0.00'];
            }
        }
        else {
            return ['status' => 101, 'message' => '请求失败' . $result['info'], 'game_money' => ''];
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
        Redis::select(14);
        $userId = $user->id;
        if ($method == 'in') {
            if ($money > $user->deposit) {
                return response()->json(['status' => 405, 'message' => '余额不足,请先充值']);
            }
            //转入时先扣钻石,然后再执行转账
            $result = GameService::changeDeposit($userId, $money, $method);
            //生成转账记录
            if ($result == 'success') {
                $record_id = GameService::makeTransferId($user, $method, $money, $this->platform);
            }
        } else {
            $record_id = GameService::makeTransferId($user, $method, $money, $this->platform);
        }

        //组织参数发起请求
        $order_id = 'ysg' . $user->id . date('YmdHis');
        $data = [
            'username' => $this->prifix . $user->username,
            'password' => 'ysg' . $user->username,
            'orderno' => $order_id,
        ];
        if ($method == 'in') {
            $data['amount'] = $money / 10;
            $action = 'deposit';
        } else {
            $data['amount'] = (-1 * $money) / 10;
            $action = 'withdraw';
        }
        $result = $this->request_ticket_content($action, $data);
        $msg = $result['info'];
        if ($result['code'] == 1) {
            $transfer_status = 1;
            if ($method == 'out') {
                //转出时执行转账成功,再加钻石
                GameService::changeDeposit($userId, -1 * $money, $method);
            } else {
            }
            $response = ['status' => 0, 'message' => 'OK'];
        } else {
            $transfer_status = 0;
            $response = ['status' => 102, 'message' => $msg];
        }
        $user_info = User::where('id', $userId)->first();

        $transferRecord = [
            'order_id' => $order_id,
            'afterAmount' => $user_info->deposit,
            'status' => $transfer_status,
            'msg' => $msg,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        //修改游戏平台返回状态
        DB::table('transfer_record')->where('id', $record_id)->update($transferRecord);

        return response()->json($response);
    }

    /**
     * 获取转账记录
     */
    public function getTransferRecord($request)
    {
        if (!$user = $request->user()) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $uid = $user->id;
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $page = intval($request->input('page', 1));
        $pageSize = intval($request->input('page_size', 10));
        $queryObj = DB::table('transfer_record')->where('platform',$this->platform)->where('uid', $uid);
        if ($start_time) {
            $queryObj = $queryObj->where('created_at', '>=', $start_time . ' 00:00:00');
        }
        if ($end_time) {
            $queryObj = $queryObj->where('created_at', '<=', $end_time . ' 23:59:59');
        }
        $total = $queryObj->count();
        $lists = $queryObj->orderBy('created_at', 'desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get()->toArray();

        $result = [];
        foreach ($lists as $list) {
            $tmp['type'] = $list->type;
            $tmp['amount'] = $list->amount;
            $tmp['time'] = $list->created_at;
            $result[] = $tmp;
        }

        $page = [
            'total' => ceil($total / $pageSize),
            'current' => $page,
            'size' => $pageSize
        ];
        $data = [
            'totalRecord' => $total,
            'page' => $page,
            'list' => $result
        ];
        $response = ['status' => 0, 'message' => 'OK', 'data' => $data];
        return response()->json($response);
    }

    //定时获取游戏记录
    public function getRecord()
    {
        //每两分钟拉前10分钟的游戏记录
        $start_time = date('Y-m-d H:i:s', (time() - 1201));
        //$start_time = '2019-04-19 14:12:53';
        $end_time = date('Y-m-d H:i:s');
        //$end_time = '2019-04-19 16:00:53';
        $data = [
            'starttime' => $start_time,
            'endtime' => $end_time,
            'settled' => 1,
            'pagenum' => 1,
            'pasesize' => 5000,
        ];
        $result = $this->request_ticket_content('data', $data);
        if ($result['code'] == 1) {
            if ($result['info']['totalcount'] > 0) {
                $tipsData = Config::getConfigByAlias('winners_tips_threshold');
                $tipsDataVal = json_decode($tipsData['content'][0],true);
                $tipsDataVal = $tipsDataVal ? $tipsDataVal : 15;

                foreach ($result['info']['data'] as $k => $v) {
                    $game_record_id = $v['gamerecordno'];
                    $has = DB::table('game_record')
                        ->where('game_record_id', $game_record_id)
                        ->where('platform','Ticket')
                        ->first();
                    $now_time = date('Y-m-d H:i:s');
                    if (!$has) {
                        $insert_tmp['game_id'] = $game_record_id;
                        $insert_tmp['username'] = ltrim($v['playeraccount'], $this->prifix);
                        $insert_tmp['kind_id'] = $v['gameid'];
                        $insert_tmp['bet'] = $v['bettingamount'];
                        $insert_tmp['profit'] = $v['netwinningamount'];
                        $insert_tmp['start_time'] = $v['bettingtime'];
                        $insert_tmp['end_time'] = $v['payouttime'];
                        $insert_tmp['status'] = $v['payoutstatus'];
                        $insert_tmp['result'] = $v['gameroundresult'];
                        $insert_tmp['platform'] = $this->platform;
                        $insert_tmp['created_at'] = $now_time;
                        $insert_tmp['updated_at'] = $now_time;
                        $insert_record[] = $insert_tmp;
                        $profit = $insert_tmp['profit'] * 10;
                        if($profit >= $tipsDataVal){
                            //$userInfo = DB::table( 'uc_users' )->where( 'username', $insert_tmp['username'] )->first();
                            //$nickname = $userInfo ? $userInfo->nickname : "j***s";
                            $platform = lcfirst($this->platform);
                            $member = DB::table( 'game_members' )
                                ->where( 'game_platform', $platform )
                                ->where( 'kind_id', $v['gameid'])
                                ->first();
                            $icon_url = $member ? $member->icon_url : 'http://play.zdzlw.com/img/v/vapp/quwan/gamelist/default_icon.png';
                            $game_title = $member ? $member->game_title : '福彩' . $insert_tmp['gamename'];
                            $data_arr = [
                                'record_id' => $insert_record['game_id'],
                                'username' => $insert_tmp['username'],
                                'profit' => $profit,
                                'kind_id' => $v['gameid'],
                                'platform' => $this->platform,
                                'icon_url' => $icon_url,
                                'game_title' => $game_title
                            ];
                            Redis::select(14);
                            Redis::lPush('winners_tips_list', json_encode($data_arr));
                        }
                    } elseif ($has->status != $v['payoutstatus']) {
                        $update_tmp['bet'] = $v['bettingamount'];
                        $update_tmp['profit'] = $v['netwinningamount'];
                        $update_tmp['start_time'] = $v['bettingtime'];
                        $update_tmp['end_time'] = $v['payouttime'];
                        $update_tmp['status'] = $v['payoutstatus'];
                        $update_tmp['result'] = $v['gameroundresult'];
                        $update_tmp['updated_at'] = $now_time;
                        DB::table('game_record')->where('game_id', $game_record_id)->update($update_tmp);
                    }
                }

                if (count($insert_record) > 0) {
                    $res = DB::table('game_record')->insert($insert_record);
                }
            }
            return 'success';
        }
    }

    // 登录账号
    public function loginAccount($user, $host = '')
    {
        $data = [
            'username' => $this->prifix . $user->username,
            'password' => 'ysg' . $user->username,
            'homeurl' => $host,
            'platform' => 'app'
        ];
        $result = $this->request_ticket_content('login', $data);
        return $result;
    }

    // 创建游戏账号
    public function createdAccount($user)
    {
        $data = [
            'username' => $this->prifix . $user->username,
            'password' => 'ysg' . $user->username,
            'nickname' => $user->username
        ];
        $result = $this->request_ticket_content('createAccount', $data);
        return $result;
    }

    //组织参数，发起请求
    public function request_ticket_content($method, $param_arr)
    {
        $str = '';
        foreach ($param_arr as $k => $v) {
            if (!in_array($k, ['starttime', 'endtime', 'homeurl'])) {
                $str .= "$k=" . urlencode($v) . "&";
            } else {
                $str .= "$k=" . $v . "&";
            }
        }
        $str = substr($str, 0, -1);
        $base_str = base64_encode($str);
        $signature = '';
        openssl_public_encrypt($this->agentid, $signature, $this->rsa_public);
        $signature = base64_encode($signature);
        $post_data = 'params=' . $base_str . '&signature=' . $signature . '&encrypted=BASE64&agentid=' . $this->agentid;
        $post_url = $this->url . '/api/' . $method . '?' . $post_data;
        $res = $this->get_request($post_url);
        return $res;
    }

    public function get_request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true);
    }
}