<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WithdrawController extends Controller {

    public function index( Request $request ){
        $api_token = $request->input('api_token') ? $request->input('api_token') : $request->header('api_token');
        $data = [
            'api_token' => $api_token
        ];
        return view('withdraw.index',$data);
    }

    public function bankcard( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $data['deposit'] = $user->deposit / 10;
        $data['banks'] = DB::table('banks')->get(['id','name','code']);
        $data['bankcards'] = DB::table('bankcards as l')->leftJoin('banks as r','l.bank_id','=','r.id')->where('l.uc_user_id',$user->id)
            ->where('l.status',1)->get(['l.id','r.name as bank','r.code as code','l.card_number','l.name','l.province','l.city','l.branch']);
        if($data){
            return response()->json(['status' => 0, 'message' => 'OK', 'data' => $data]);
        }else{
            return response()->json(['status' => 403, 'message' => '未找到配置信息']);
        }
    }

    public function bind( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $totalBankcards = DB::table('bankcards')->where('uc_user_id',$user->id)->where('status',1)->count();
        if( $totalBankcards >= 5 ){
            return response()->json(['status' => 10003, 'message' => '每个用户只能绑定五张银行卡']);
        }
        $keyList = [
            'bank_id',
            'name',
            'card_number',
            'province',
            'city',
            'branch'
        ];
        $data = [];
        foreach ($keyList as $key) {
            $data[$key] = $request->input($key);
        }
        $rules = [
            'bank_id'     => 'required|exists:banks,id',
            'name'        => 'required|string|min:2|max:10',
            'card_number' => 'required|min:14|max:20|unique:bankcards',
            'province'    => 'required|string|min:2|max:20',
            'city'        => 'required|string|min:2|max:20',
            'branch'      => 'required|string|min:2|max:30'
        ];
        $messages = [
            'required'  => ":attribute不能为空",
            'exists'    => ":attribute不存在",
            'unique'    => ":attribute已存在",
            'string'    => ":attribute需为字符串",
            'min'       => ":attribute最少:min个字符",
            'max'       => ":attribute最多:max个字符",
            'card_number.min' => ":attribute格式错误",
            'card_number.max' => ":attribute格式错误"
        ];
        $attributes = [
            'bank_id'     => '银行类别',
            'name'        => '开户姓名',
            'card_number' => '银行卡号',
            'province'    => '省份',
            'city'        => '城市',
            'branch'      => '支行地址'
        ];
        $validator = Validator::make($data, $rules, $messages, $attributes);
        if( $validator->fails() ){
            return response()->json(['status' => 10002, 'message' => $validator->errors()->first()]);
        }
        $notTime = date('Y-m-d H:i:s');
        $data['uc_user_id'] = $user->id;
        $data['status'] = 1;
        $data['created_at'] = $notTime;
        $data['updated_at'] = $notTime;
        try{
            DB::table('bankcards')->insert($data);
            return response()->json(['status' => 0, 'message' => "添加成功"]);
        } catch (\Exception $e){
            return response()->json(['status' => 20001, 'message' => $e->getMessage()]);
        }
    }

    public function record( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $page       = intval( $request->input('page',1) );
        $pageSize   = intval( $request->input('page_size',10) );

        $query = DB::table('withdraws')->where('uc_user_id',$user->id);
        $total = $query->count();
        $records = $query->orderBy('created_at','desc')->skip(($page - 1) * $pageSize)->take($pageSize)->get(['trade_no','created_at','amount','fee','status']);
        $list = [];
        $status = ['0'=>'待处理','1'=>'处理中','2'=>'已完成','3'=>'已拒绝'];
        foreach ($records as $key=>$value){
            $list[$key] = [
                'trade_no'  => $value->trade_no,
                'created_at'=> $value->created_at,
                'amount'    => $value->amount,
                'fee'       => $value->fee,
                'status'    => $status[$value->status]
            ];
        }
        $page = [
            'total_page'=> $total ? ceil($total / $pageSize) : 0,
            'current'   => $page,
            'page_size' => $pageSize
        ];
        $res = ['page' => $page, 'list' => $list];
        return response()->json(['status' => 0, 'message' => 'OK', 'data' => $res]);
    }

    public function commit( Request $request ){
        if( !$user = $request->user() ) {
            return response()->json(['status' => 401, 'message' => '请先登录']);
        }
        $keyList = [
            'bankcard',
            'amount'
        ];
        $data = [];
        foreach ($keyList as $key) {
            $data[$key] = $request->input($key);
        }
        $rules = [
            'bankcard'  => 'required|exists:bankcards,id',
            'amount'    => 'required|integer|min:100|max:50000'
        ];
        $messages = [
            'required'  => ":attribute不能为空",
            'exists'    => ":attribute不存在",
            'integer'   => ":attribute需为整数值",
            'min'       => ":attribute最少:min元",
            'max'       => ":attribute最多:max元"
        ];
        $attributes = [
            'bankcard'  => '银行卡号',
            'amount'    => '提现金额'
        ];
        $validator = Validator::make($data, $rules, $messages, $attributes);
        if( $validator->fails() ){
            return response()->json(['status' => 10001, 'message' => $validator->errors()->first()]);
        }
        if( $data['amount'] > $user->deposit / 10 ){
            return response()->json(['status' => 10002, 'message' => '余额不足']);
        }

        try{
            DB::transaction( function () use( $user, $data ){
                $nowTime = date('Y-m-d H:i:s');
                $User = DB::table('users')->where('id', $user->id)->lockForUpdate()->first(); //already
                $available = $User->deposit / 10 / 1.1; //手续费0.1
                $amount = $data['amount'] >= $available ? $available : $data['amount'];
                $fee = $amount * 0.1; //手续费0.1
                //扣钱
                $deposit = $User->deposit - $amount*10 - $fee*10;
                DB::table('users')->where('id', $User->id)->update(['deposit'=>$deposit]);
                DB::table('withdraws')->insert([
                    'uc_user_id'    => $User->id,
                    'trade_no'      => generateWithdrawNo(),
                    'deposit'       => $deposit / 10,
                    'amount'        => $amount,
                    'fee'           => $fee,
                    'bankcard_id'   => $data['bankcard'],
                    'status'        => 0,
                    'created_at'    => $nowTime,
                    'updated_at'    => $nowTime
                ]);
//                DB::connection('mysql_fund')->table('fund_records')->insert([
//                    [
//                        'uc_user_id'    => $ucUser->id,
//                        'transaction_no'=> generateTransactionNo(),
//                        'fund_type'     => '提现',
//                        'before'        => $ucUser->deposit,
//                        'amount'        => $amount*10,
//                        'after'         => $ucUser->deposit - $amount*10,
//                        'created_at'    => date('Y-m-d H:i:s')
//                    ],
//                    [
//                        'uc_user_id'    => $ucUser->id,
//                        'transaction_no'=> generateTransactionNo(),
//                        'fund_type'     => '提现手续费',
//                        'before'        => $ucUser->deposit - $amount*10,
//                        'amount'        => $fee*10,
//                        'after'         => $ucUser->deposit - $amount*10 - $fee*10,
//                        'created_at'    => date('Y-m-d H:i:s')
//                    ]
//                ]);
            }, 3);
            return response()->json(['status' => 0, 'message' => '申请成功']);
        } catch (\Exception $e){
//            \Log::error( '提现账变异常,错误消息' . $e->getMessage());
            \Log::error('用户"'.$user->id.'"申请提现失败,用户名:'.$user->username.',错误详情:'.$e->getMessage());
            return response()->json(['status' => 20001, 'message' => '申请失败,请联系客服']);
        }

    }


}
