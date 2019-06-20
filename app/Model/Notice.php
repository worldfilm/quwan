<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model {

    public static function getByOS( $ch, $os, $vc )
    {
        $data = [
            'id'          => hashid(10, 'video'),
            'title'       => '公告测试',
            'description' => '公告测试<br>',
            'url'         => 'http://www.google.com',
            'force'       => false,
        ];

        return $data;
    }

}
