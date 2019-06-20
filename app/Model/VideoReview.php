<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VideoReview extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'video_id', 'user_id', 'content'
    ];


    /**
     * 获取评论列表
     * @return [type] [description]
     */
    public static function getlist( $videoId, $page = 1, $pageSize = 10 )
    {
        $pageSize = 10;
        $queryObj = self::query()->with('user');
        $queryObj->where('video_id', $videoId);

        $total = $queryObj->count();

        $data  = $queryObj
                ->orderBy('id', 'desc')
                ->select('content','user_id','created_at')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

        $data = $data->toArray();
        $list = [];
        foreach ($data as $key => $value) {
            $list[$key]['nickname'] = $value['user']['nickname'];
            $list[$key]['content']  = $value['content'];

            $list[$key]['creat_time']  = date('Y-m-d',strtotime($value['created_at']));
        }

        $page = [
            'total'   => ceil($total / $pageSize),
            'current' => $page,
            'size'    => $pageSize
        ];

        $ret = [
            'page' => $page,
            'list' => $list,
        ];

        return $ret;
    }

    /**
     * [getCount 获取评论条数]
     * @return [type] [description]
     */
    public static function getCount( $videoId )
    {
        $queryObj = self::query()->with('user');
        $queryObj->where('video_id', $videoId);

        $total = $queryObj->count();

        return $total;
    }


    /**
     * 反向关联User表
     * @return object
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User');
    }

    /**
     * 反向关联视频表
     * @return object
     */
    public function video()
    {
        return $this->belongsTo('App\Model\Video');
    }

}
