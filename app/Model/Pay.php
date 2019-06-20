<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Pay extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'pid', 'status','rank','alias_name','channel','is_jump'
    ];


    /**
     * 获取支付列表
     * @return [type] [description]
     */
    public static function getPayList(){
        return self::where('status',8)
                        ->where('pid',0)
                        ->orderBy('rank','asc')
                        ->get();
    }

    /**
     * 获取支持的支付类型
     * @return [type] [description]
     */
    public static function getPayMethod($os){
        $list =  self::select('channel as method')
            ->where('service','like','%'.$os.'%')
            ->whereIn('method',["wap","qr","app"])
            ->where('pid','!=',0)
            ->where('status',8)
            ->groupBy('channel')->get()->toArray();
        $channelName = config('pay.pay_channel');

        foreach ($list as $key => $value) {
            $list[$key]['name'] = $channelName[$value['method']];
        }
        return $list;
    }

    /**
     * W通过接口获取支付列表
     * @param $project_id
     * @return mixed
     */
    public static function getPayListByApi($project_id){
        $apiUrl = "http://".env("APP_GETPAYLIST")."/api/pay/method?project_id=".$project_id;
        $response = file_get_contents($apiUrl);
        $result = json_decode($response,true);
        if(isset($result['message']) && $result['message'] == 'success'){
            return $result['data'];
        } else {
            \Log::error("V项目请求支付列表失败:" . $response);
            return false;
        }
    }

    /**
     * 通过条件回去支付方式
     * @param  [type] $item [description]
     * @return [type]       [description]
     */
    public static function getPayByItem($item,$os='ios')
    {
        return self::with('parent_pay')->where('service','like','%'.$os.'%')->where('channel',$item)->where('status',8)->where('pid','!=',0)->first();
    }

    /**
     * 检测是否重复
     * @param  [type] $alias_name [description]
     * @return [type]             [description]
     */
    public static function checkAlias($alias_name){
        return self::where('alias_name',$alias_name)->first();
    }

    //关联自己 查询子集
    public function child_pay(){
        $relation = $this->hasMany('\App\Model\Pay','pid','id');
        $relation->orderBy('rank','asc');
        return $relation;
    }


    //关联自己 查询父级
    public function parent_pay(){
        $relation = $this->hasMany('\App\Model\Pay','id','pid');
        $relation->orderBy('rank','asc');
        return $relation;
    }


}
