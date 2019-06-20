<?php

namespace App\Services;

use Exception;
use DB;
use Illuminate\Support\Facades\Redis;
use App\Model\UcUser;
use \App\Services\Game\ticket;
use \App\Services\Game\chess;

/**
 *第三方游戏
 */
class GameService
{
    //所有平台
    //const platforms = ['ticket', 'chess', 'kg', 'avia', 'leg','ig'];
    /**
     * [loginGame 登录]
     * @param  [type] $userId [description]
     */
    public static function loginGame( $platform, $request)
    {
        $serviceName = '\App\Services\Game\\' . $platform;
        $gameService = new $serviceName();
        $data = $gameService->login($request);
        return $data;
    }

    /**
     * [transferGame 转账]
     * @param  [type] $userId [description]
     */
    public static function transferGame( $platform, $request)
    {
        $serviceName = '\App\Services\Game\\' . $platform;
        $gameService = new $serviceName();
        $data = $gameService->transfer($request);
        return $data;
    }

    /**
     * [oneKeyTransferGame 一键转出]
     * @param  [type] $userId [description]
     */
    public static function oneKeyTransferGame($request)
    {
        $platforms = self::getOpenPlatform();
        foreach($platforms as $v){
            //查询余额
            $serviceName = '\App\Services\Game\\' . $v;
            $gameService = new $serviceName();
            $result = $gameService->getBalance($request);
            $status = $result['status'];
            if($status == 0){
                $gameMoney = $result['game_money'];
                if($gameMoney > 0){
                    $request->offsetSet('method', 'out');
                    $request->offsetSet('money', $gameMoney * 10);
                    //执行转账
                    $gameService->transfer($request);
                }
            }
        }

        return response()->json(['status' => 0, 'message' => '一键转出成功']);
    }

    /**
     * [balanceGame 获取余额]
     * @param  [type] $userId [description]
     */
    public static function balanceGame($platform, $request)
    {
        $platforms = $platform ? [$platform] : self::getOpenPlatform();
        $data = [];
        foreach($platforms as $v){
            //查询余额
            $serviceName = '\App\Services\Game\\' . $v;
            $gameService = new $serviceName();
            $result = $gameService->getBalance($request);
            $status = $result['status'];
            if($status == 0){
                $data[$v] = [
                    'game_money' => $result['game_money'],
                ];
            }else{
                $data[$v] = [
                    'game_money' => '',
                    'info' => $result['message'],
                ];
            }
        }
        return $data;
    }

    /**
     * [gameUrl 获取游戏地址]
     * @param  [type] $userId [description]
     */
    public static function gameUrl( $platform, $request)
    {
        $serviceName = '\App\Services\Game\\' . $platform;
        $gameService = new $serviceName();
        $data = $gameService->getGameUrl($request);
        return $data;
    }

    /**
     * [getTransferRecord 获取转账记录]
     * @param  [type] $userId [description]
     */
    public static function getTransferRecord($platform, $request)
    {
        if($platform == 'ticket'){
            $gameService = new ticket();
            $data = $gameService->getTransferRecord($request);
            return $data;
        }else{
            if( !$user = $request->user() ) {
                return response()->json(['status' => 401, 'message' => '请先登录']);
            }
            $uid = $user->id;
            $start_time = $request->input( 'start_time');
            $end_time = $request->input( 'end_time');
            $page       = intval($request->input('page',1));
            $pageSize   = intval($request->input('page_size',10));
            $platform   = ucfirst($platform);
            $queryObj = DB::table('transfer_record')->where('platform', $platform)->where('uid',$uid);
            if($start_time){
                $queryObj = $queryObj->where( 'created_at', '>=', $start_time.' 00:00:00' );
            }
            if($end_time){
                $queryObj = $queryObj->where( 'created_at', '<=', $end_time.' 23:59:59' );
            }
            $total = $queryObj->count();
            $lists = $queryObj->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get()->toArray();

            $result = [];
            foreach( $lists as $list ) {
                $tmp['type'] = $list->type;
                $tmp['amount'] = $list->amount;
                $tmp['time'] = $list->created_at;
                $result[] = $tmp;
            }

            $page = [
                'total'   => ceil($total / $pageSize),
                'current' => $page,
                'size'    => $pageSize
            ];
            $data = [
                'totalRecord' => $total,
                'page' => $page,
                'list' => $result
            ];
            $response = ['status' => 0, 'message' => 'OK', 'data' => $data];
            return response()->json($response);
        }
    }

    //获取游戏配置
    public static function getGameConfig( $code ){
        Redis::select(10);
        $redis_key = 'game_list';
        if(!Redis::hExists($redis_key,$code)){
            $data = DB::table('games')->where('code', $code)->select('id','title','code','img_url','content')->first();
            if($data){
                Redis::hSet($redis_key, $code, json_encode($data));
            }
        }
        $info = Redis::hGet($redis_key,$code);
        $info = json_decode($info,true);
        return $info;
    }

    //变动用户余额
    public static function changeDeposit($userId, $money, $method = 'in')
    {
        try {
            $state = DB::transaction(function () use ($userId, $money, $method) {
                DB::table('users')->where('id', $userId)->lockForUpdate()->first();
                if($method == 'in'){
                    $state = DB::table('users')->where('id', $userId)->where('deposit', '>=', $money)->decrement('deposit', $money);
                }else{
                    $state = DB::table('users')->where('id', $userId)->decrement('deposit', $money);
                }
                return $state;
            }, 3);
            Redis::select(0);
            Redis::hdel('user_detail_info', $userId);
            Redis::hdel('user_money_info', $userId);
            return $state ? 'success' : 'fail';
        } catch (\Exception $e) {
            return response()->json(['status' => 406, 'message' => '操作失败,请联系客服']);
        }
    }

    //获取进入游戏列表的主播id
    public static function getGameZbUserId( $user_name ){
        $join_game_hall_way = 'join_game_hall_way';
        $zb_user_id = Redis::hGet($join_game_hall_way, $user_name);
        return $zb_user_id;
    }

    //生成转账记录
    public static function makeTransferId($user, $method, $money, $platform)
    {
        $msg = '生成记录，未上分';
        $transferRecord = [
            'uid' => $user->id,
            'platform' => $platform,
            'type' => $method,
            'beforeAmount' => $user->deposit,
            'amount' => $money,
            'status' => 0,
            'msg' => $msg,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $record_id = DB::table('transfer_record')->insertGetId($transferRecord);
        return $record_id;
    }

    //获取开启的平台游戏
    public static function getOpenPlatform(){
        $data = DB::table('games')->where('status', 0)->select('code')->get();
        $game = [];
        foreach($data as $v){
            $game[] = $v->code;
        }
        return $game;
    }
}