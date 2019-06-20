<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Category extends Model {
    protected $fillable = ['id','name','status','rank','created_at', 'updated_at','thumb','is_online'];
    public static function getList( $page = 1, $pageSize = 20 )
    {
        $data  = self::where('status', 0)
                ->orderBy('rank', 'asc')
                ->select('id', 'name', 'thumb')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();
        $total = self::where('status', 8)->count();
        $data  = $data->toArray();
        foreach( $data as $key => $value ) {
            $data[$key]['id'] = hashid($value['id'], 'category');
        }
        $page = [
            'total'   => ceil($total / $pageSize),
            'current' => $page,
            'size'    => $pageSize
        ];

        $ret = [
            'page' => $page,
            'list' => $data,
        ];

        return $ret;
    }

    /**
     * 获取分类列表
     * @param  integer $page     [description]
     * @param  integer $pageSize [description]
     * @return [type]            [description]
     */
    public static function getVipList(  )
    {
//        $categoryIds  = VipCategory::where('vip_id', \App\Model\VipCategory::TYPE_VIP_VIP_ID)
//                        ->get()
//                        ->pluck('category_id');
        $data        = self::where('status', 8)->where('is_online',0)
//                ->whereIn('id', $categoryIds)
                ->orderBy('rank', 'asc')
                ->select('id', 'name')
                ->get();

        // $data        = $data->toArray();
        $returnData = [];
        foreach( $data as $key => $value ) {
            $id = $value->id;

            // // 获取关联最多的标签列表
            // $tags = $value->video()->get()->pluck('tags')->toArray();
            // $newList = '';
            // foreach ($tags as  $tag) {
            //     $newList .= ','.$tag;
            // }
            // $tags = array_count_values(array_filter(explode(',', $newList)));
            // array_multisort($tags,SORT_DESC);
            // $newTags = array_chunk(array_keys($tags),9);

            // $tag = isset($newTags[0]) ? $newTags[0] : [];
            // array_unshift($tag,'全部');
            //返回值
            $returnData[] = [
                'id'=> hashid($id, 'category'),
                'name' => $value->name,
                'tags'=>[],
            ];
        }

        return $returnData;
    }

    /**
     * 通过ID获取分类
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function findById( $id )
    {
        return self::where('id', $id)->first();
    }


    /**
     * 关联视频表
     * @return [type] [description]
     */
    public function  video(){
        return $this->hasMany('\App\Model\Video');
    }

}
