<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Video;
use Illuminate\Support\Facades\Redis;

// use Illuminate\Notifications\Notifiable;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class PictureCategories extends Model {
    /**
     * 获取类别列表
     * return array
     */
    protected $fillable = ['id','name','href'];

}
