<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class UserConsumptionLog extends Model
{
	protected $fillable = ['user_id','use_porint','unit','type'];
	protected $table = 'user_consumption_log';

	/**
     * 关联用户表
     * @return [type] [description]
     */
    public function user()
    {
    	return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }
}