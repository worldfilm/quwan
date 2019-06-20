<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use DB;
class UserSearch extends Model {

    protected $table = 'search_record';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'content'
    ];
}
