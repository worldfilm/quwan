<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class Advert extends Model {

    protected $table = 'adverts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'service', 'position','type','url','thumb_img_url','start_time','end_time','status'
    ];


    /**
     * 获取广告列表
     * @return [type] [description]
     */
    public static function getList( $request )
    {
        $time = date('Y-m-d H:i:s');
        $query = self::select('id','title','url','thumb_img_url','type')->where('status',8)->where('start_time','<=',$time)->where('end_time','>=',$time);
        if($position = $request->input('positions')){
            $query = $query->where('position',$position);
        }

        $list = $query->orderBy('rank','asc')->take(5)->get();
        $data = [];
        foreach ($list as $key => $value) {
            $data[] = [
                'id'    =>  hashid($value->id, 'advert'),
                'title' =>  $value->title,
                'url'   =>  $value->url,
                'thumb_img_url' =>  $value->thumb_img_url,
                'type'  => $value->type,
            ];
        }
        return $data;
    }

    /**
     * 获取新广告列表
     * @return [type] [description]
     */
    public static function getNewList( $request, $vip, $cate_code)
    {
        $query = DB::table('advert_pc as a')
            ->leftjoin('advert_cate as c','a.cate_id','=','c.id')
            ->select('a.id','a.title','a.url','a.img_url','a.code')
            ->whereRaw("!find_in_set('$vip',shield_vips)")
            ->where('a.status',0)
            ->where('c.status',0)
            ->where('c.code',$cate_code);
        $list = $query->orderBy('rank','asc')->take(5)->get();

        $data = [];
        foreach ($list as $key => $value) {
            $data[] = [
                'id'    =>  hashid($value->id, 'advert'),
                'title' =>  $value->title,
                'code' =>  $value->code,
                'url'   =>  $value->url,
                'img_url' =>  $value->img_url,
            ];
        }
        return $data;
    }
}
