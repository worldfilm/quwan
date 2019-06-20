<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOutnoUsersvip extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_vips', function ($table) {
            $table->string('out_trade_no',64)->nullable();
            $table->index('user_id');
            $table->index('out_trade_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_vips', function ($table) {
            $table->dropColumn(['out_trade_no']);
        });
    }
}
