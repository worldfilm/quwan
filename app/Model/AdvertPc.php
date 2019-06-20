<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use DB;
class AdvertPc extends Model {

    protected $table = 'advert_pc';
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'title', 'desc', 'cate_id','url','img_url','status','rank'
    ];


    /**
     * 获取广告列表
     * @return [type] [description]
     */
    public static function getList( $request, $vip, $cate_code)
    {
        $list = DB::table('advert_pc as a')
            ->leftjoin('advert_cate as c','a.cate_id','=','c.id')
            ->select('a.id','a.title','a.desc','a.url','a.img_url','a.rank')
            ->whereRaw("!find_in_set('$vip',shield_vips)")
            ->where('a.status',0)
            ->where('c.status',0)
            ->where('c.code',"$cate_code")
            ->orderBy('a.rank','asc')
            ->get();

        $data = [];
        foreach ($list as $key => $value) {
            $data[] = [
                //'id'    =>  hashid($value->id, 'advert'),
                'title' =>  $value->title,
                'url'   =>  $value->url,
                'desc'   =>  $value->desc,
                'rank'   =>  $value->rank,
                'img_url' =>  $value->img_url,
            ];
        }
        return $data;
    }

    public function AdvertCate()
    {
        return $this->belongsTo('\App\Model\AdvertCate','cate_id','id');
    }
}
