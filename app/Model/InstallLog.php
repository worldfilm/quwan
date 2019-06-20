<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class InstallLog extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'identifier', 'os', 'created_at','channel'
    ];

    public $timestamps = false;

    /**
     * @return [type] [description]
     */
    public static function checkCreate($identifier,$os,$channel)
    {
        if(!$log = self::where('identifier',$identifier)->first()){
            self::create(['identifier'=>$identifier,'created_at'=>date('Y-m-d H:i:s'),'os'=>$os,'channel'=>$channel]);
        }
    }

}
