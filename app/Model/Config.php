<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Config extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'content', 'alias_name','status'
    ];


    /**
     * 获取配置列表
     * @return [type] [description]
     */
    public static function getConfig()
    {
        $configs = self::select('title','alias_name','description','content','status')->where('status','!=',0)->get();
        $data = [];
        foreach ($configs as $key => $value) {
            $value->content = json_decode($value->content,true);
            $data[$value->alias_name] = $value;
        }

        return $data;
    }

    /**
     * 通过别名获取配置信息
     * @return [type] [description]
     */
    public static  function getConfigByAlias($alias_name)
    {
        $config = self::select('title','alias_name','description','content','status')->where('alias_name',$alias_name)->where('status',8)->first();
        if($config){
            $config->content = json_decode($config->content,true);
            return $config->toArray();
        }
        return false;
    }

}
