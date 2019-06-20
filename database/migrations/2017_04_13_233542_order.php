<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Order extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('title',1000);
            $table->string('out_trade_no',100);
            $table->string('pay_info',4096);
            $table->string('pay_method',50);
            $table->string('type',100);
            $table->integer('item_id')->default(0);
            $table->string('item_info',1000);
            $table->string('price',100);
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->index('out_trade_no','o_no');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
