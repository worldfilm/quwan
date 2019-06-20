<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VideoTags extends Model {

    protected $fillable = [
        'video_id', 'tag_id'
    ];

    /**
     * 关联视频表
     * @return [type]
     */
    public function video()
    {
        return $this->belongsTo('App\Model\Video', 'video_id', 'id');
    }

    /**
     * 关联标签表
     * @return [type]
     */
    public function tags()
    {
        return $this->belongsTo('App\Model\Tags', 'tag_id', 'id');
    }

}
