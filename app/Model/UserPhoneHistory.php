<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserPhoneHistory extends Model {

    protected $table = 'user_phone_history';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'phone'
    ];

}
