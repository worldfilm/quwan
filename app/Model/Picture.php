<?php

namespace App\Model;
use DB;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model{
    protected $fillable = [
        'source_flag'
    ];
	public static function getList($page = 1, $pageSize = 20){
		$queryObj = self::where('id', '>', 0);
		$total = $queryObj->count();
		$data = $queryObj
					->orderBy('rank','asc')
					->orderBy('created_at','desc')
					->select('id', 'title', 'thumb')
					->skip( ($page - 1) * $pageSize )
					->take($pageSize)
					->get();

		$data = $data->toArray();
		foreach ($data as $key => $value) {
			$data[$key]['id'] = hashid($value['id'],'picture');
		}
		$page = [
			'total'	=>	ceil($total / $pageSize),
			'current'	=>	$page,
			'size'	=>	$pageSize
		];
		$ret = [
			'page'	=>	$page,
			'list'	=>	$data,
		];

		return $ret;
	}

	public static function detail($id){
		$data = self::where('id', $id)->first();
		if( !empty($data) ){
			//增加查看次数记录
			$data->view_times = $data->view_times+1;
			$data->save();
			$data = $data->toArray();
			$data['content'] = json_decode($data['content'],true);
			$data['total_num'] = count($data['content']);
			unset($data['created_at']);
			unset($data['updated_at']);
			unset($data['rank']);
			unset($data['category_id']);
			$data['id'] = hashid($id, 'picture');
			$data['advert'] = DB::table('picture_ad')->select('thumb_img_url','url_type','url')->where('picture_id',$id)->first();
			return $data;
		}
		else{
			return false;
		}
	}
}
