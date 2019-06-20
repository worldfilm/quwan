<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class VideoAssessLogs extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'video_id','assess_type'
    ];

    public $timestamps = false;

    /**
     * 通过条件查询log
     * @param  [type] $userid  [description]
     * @param  [type] $videoid [description]
     * @return [type]          [description]
     */
    static public  function getLogByItem($userid,$videoid)
    {
    	$queryObject = self::query();

    	if($userid){
    		$queryObject->where('user_id',$userid);
    	}

    	if($videoid){
    		$queryObject->where('video_id',$videoid);
    	}

    	return $queryObject->first();
    }

}
