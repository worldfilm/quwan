<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\Banner;
use App\Service\Help\VideoService;
use App\Model\Game;
use App\Services\GameService;
use Illuminate\Support\Facades\Redis;
use App\Model\Config;

class GameController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index( Request $request ){
        //获取banner
        $banners = VideoService::getGameBanner();
        //获取轮播文字消息
        $carousel_message = Config::getConfigByAlias('game_carousel_message');
        $carousel_message_val = $carousel_message['content'][0];
        //获取游戏数据
        $cates = DB::table('game_cates')->select('id','name','status','rank')
            ->where('status',0)
            ->orderBy('rank','asc')
            ->get();
        foreach($cates as $k => $v){
            $games = DB::table('game_members')
                ->select('game_title','game_platform','icon_url','kind_id')
                ->where('status',0)
                ->where('cate_id',$v->id)
                ->orderBy('rank','asc')
                ->get();
            $cates[$k]->lists = $games;
        }
        $api_token = $request->input('api_token') ? $request->input('api_token') : $request->header('api_token');
        $data = [
            'banners' => $banners,
            'cates' => $cates,
            'api_token' => $api_token,
            'carousel_message_val' => $carousel_message_val
        ];
        return view('game.index',$data);
    }

    public function transferH5(){
        return view('game.transfer');
    }



    /**
     *游戏登录
     */
    public function login($platform, Request $request){
        try{
            $result = GameService::loginGame($platform, $request);
            return $result;
        }catch( \Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
    }

    /**
     *游戏转账
     */
    public function transfer($platform, Request $request){
        try{
            $result = GameService::transferGame($platform, $request);
            return $result;
        }catch( \Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
    }

    /**
     *一键转出
     */
    public function oneKeyTransfer( Request $request){
        try{
            $result = GameService::oneKeyTransferGame($request);
            return $result;
        }catch( \Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
    }

    /**
     * 获取游戏列表
     */
    public function getList( Request $request ){
        $game_list = Game::orderBy('rank', 'asc')
            ->select('title', 'code', 'rank','img_url','status')
            ->where('status',0)
            ->get();
        return response()->json( ['status' => 0, 'message'=> '获取成功', 'data' => $game_list ] );
    }

    /**
     * 获取转账记录
     */
    public function getTransferRecord( $platform = '', Request $request ){
        try{
            $result = GameService::getTransferRecord($platform, $request);
            return $result;
        }catch( \Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
    }

    /**
     *获取游戏余额
     */
    public function balance($platform = '', Request $request){
        try{
            if($platform){
                $result = GameService::balanceGame($platform, $request);
            }else{
                $result = GameService::balanceGame('', $request);
            }
            $result['deposit'] = $request->user() ? $request->user()->deposit : '0.00';
            return response()->json( ['status' => 0, 'message'=> '请求成功', 'data' => $result] );
        }catch( \Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
    }

    /**
     *获取游戏地址
     */
    public function getGameUrl($platform = '', Request $request){
        try{
            $result = GameService::gameUrl($platform, $request);
            return $result;
        }catch( \Exception $e){
            return response()->json( ['status' => $e->getCode(), 'message'=> $e->getMessage() ] );
        }
    }

    /**
     * 转账，提现记录路由
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function recordH5( Request $request ){
        $api_token = $request->input('api_token') ? $request->input('api_token') : $request->header('api_token');
        return view('game.record', compact('api_token'));
    }

    /**
     * 获取中奖信息列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWinnersRecords( Request $request ){
        $key = 'winners_tips_list';
        Redis::select(14);
        Redis::lTrim($key, 0, 4);
        //取最近五条数据
        $lists = Redis::lRange($key, 0, 4);
        $data = [];
        foreach($lists as $v){
            $data[] = json_decode($v);
        }
        return response()->json( ['status' => 0, 'message'=> '请求成功', 'data' => $data] );
    }

    /**
     * 钱包路由
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function wallet( Request $request ){
        $api_token = $request->input('api_token') ? $request->input('api_token') : $request->header('api_token');
        return view('game.wallet', compact('api_token'));
    }

    public function record( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $type = $request->input( 'type', 'transfer' );
        switch ($type) {
            case 'transfer':
                $data = $this->transferRecord($request); break;
            case 'withdraw':
                $data = $this->withdrawRecord($request); break;
            default:
                $data = $this->transferRecord($request); break;
        }
        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $data]);
    }

    public function transferRecord( Request $request ){
        $user = $request->user();
        $page       = intval( $request->input('page',1) );
        $pageSize   = intval( $request->input('page_size',10) );
        $start_time = $request->input( 'start_time', date('Y-m-d') );
        $end_time = $request->input( 'end_time', date('Y-m-d') );

        $query = DB::table('transfer_record')->where('uid',$user->id)->where('created_at','>=',$start_time.' 00:00:00')->where('created_at','<=',$end_time.' 23:59:59');
        $total = $query->count();
        $records = $query->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get(['platform','order_id','type','amount','status','created_at']);
        $list = [];
        $totalIn = 0;
        $totalOut = 0;
        $platforms = DB::table('games')->get()->pluck('title','code');
        $status = ['0'=>'失败','1'=>'成功'];
        foreach ($records as $key=>$value){
            if($value->type == "in"){
                $totalIn += $value->amount;
            } else {
                $totalOut += $value->amount;
            }
            $list[$key] = [
                'trade_no'  => $value->order_id,
                'content'   => $value->type == "in" ? "钻石钱包→".$platforms[$value->platform] : $platforms[$value->platform]."→钻石钱包",
                'amount'    => $value->amount,
                'status'    => $status[$value->status],
                'created_at'=> $value->created_at
            ];
        }
        $page = [
            'total_page'=> $total ? ceil($total / $pageSize) : 0,
            'current'   => $page,
            'page_size' => $pageSize
        ];
        $data = [
            'total_in'  => $totalIn,
            'total_out' => $totalOut
        ];
        $res = ['page' => $page, 'data' => $data, 'list' => $list];
        return $res;
    }

    public function withdrawRecord( Request $request ){
        $user = $request->user();
        $page       = intval( $request->input('page',1) );
        $pageSize   = intval( $request->input('page_size',10) );
        $start_time = $request->input( 'start_time', date('Y-m-d') );
        $end_time = $request->input( 'end_time', date('Y-m-d') );

        $query = DB::table('withdraws')->where('uc_user_id',$user->id)->where('created_at','>=',$start_time.' 00:00:00')->where('created_at','<=',$end_time.' 23:59:59');
        $total = $query->count();
        $records = $query->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get(['trade_no','created_at','amount','fee','status']);
        $list = [];
        $status = ['0'=>'待处理','1'=>'处理中','2'=>'已完成','3'=>'已拒绝'];
        foreach ($records as $key=>$value){
            $list[$key] = [
                'trade_no'  => $value->trade_no,
                'content'   => '提现',
                'amount'    => $value->amount,
                'status'    => $status[$value->status],
                'created_at'=> $value->created_at
            ];
        }
        $page = [
            'total_page'=> $total ? ceil($total / $pageSize) : 0,
            'current'   => $page,
            'page_size' => $pageSize
        ];
        $res = ['page' => $page, 'list' => $list];
        return $res;
    }

}
