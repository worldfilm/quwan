<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Banner extends Model
{
	CONST TYPE_VIDEO = 1;
	public static function getList($type){
		$list = self::select('id','url','thumb_img_url','type','item_id')->where('type', \App\Model\Banner::TYPE_VIDEO)->orderBy('rank','asc')->orderBy('id','desc')->take(3)->get();
		$data = [];
		foreach ($list as $key => $value) {
			$data[] = [
				'id'	=>	hashid($value->id, 'video'),
				'title'	=>	'',
				'item_id'	=>	hashid($value->item_id, 'video'),
				'url'   =>  $value->url,
				'thumb_img_url'	=>	$value->thumb_img_url,
				'type'	=>	'video',
				'url_type'	=>	$value->url_type
			];
		}
		return $data;
	}

    public function video(){
    	return $this->hasOne('App\Model\Video','id', 'item_id');
    }
}