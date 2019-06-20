<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class Tag extends Model {

    protected $fillable = [
        'pid', 'title', 'rank','status'
    ];
    public $timestamps = false;
    public static function getList()
    {
        $redis_key = 'web_tag_list';
        Redis::select(10);
        if(Redis::exists($redis_key)){
            $tags = Redis::get($redis_key);
            $data = json_decode($tags,true);
        }else{
            $data = self::getTreeList();
            Redis::set($redis_key, json_encode($data));
        }

        $ret = [
            'list' => $data
        ];

        return $ret;
    }

    /*
     *获取视频标签及其子标签
     */
    public static function getTreeList($pid = 0,$key = ''){
        static $res = array();
        if($pid == 0){
            $data = self::where('pid',$pid)->select('id','title')->get();
            foreach($data as $k => $v){
                $res[$k] = $v;
                self::getTreeList($v['id'],$k);
            }
        }else{
            $data = self::where('pid',$pid)->select('id','title')->get();
            $res[$key]['childs'] = $data;
        }
        return $res;
    }

    /**
     * 关联视频表
     * @return [type] [description]
     */
    public function  video(){
        return $this->hasMany('\App\Model\Video');
    }
    public static function getTagsForM($page,$pagesize){
        $count=self::where('pid',">","0")->select('id','title')->count();
        $result["count"]=ceil($count/$pagesize);
        $result["data"]=self::where('pid',">","0")->select('id','title')->skip($page*$pagesize)->take($pagesize)->get();
        return $result;

    }

}
