<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Service\Help\UserService;

class Actor extends Model {

    /**
     * 免费视频分类ID
     */
    const FREE_VIDEO_CATEGORY_ID = 1;
    protected $table = 'actor';

    protected $fillable = [
        'id','name','english_name','name_first_char','origin_head_url','local_head_url','birthday','age','height','cup','bust','waist','hip','birthplace','hobby','movie_num','local_head_zimg','weight','description'
    ];
}
