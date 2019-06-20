<?php

namespace App\Model\Mongo;

use Jenssegers\Mongodb\Eloquent\Model;

class Advert extends Model {

    protected $connection = 'mongodb';
    protected $collection = 'adverts';
    protected $primaryKey = '_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','title', 'description', 'service', 'position','type','url','thumb_img_url','start_time','end_time','status','rank','admin_id'
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
}
