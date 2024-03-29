<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfitSharesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profit_shares', function (Blueprint $table) {
            $table->id();
            $table->string('reseller_id');
            $table->string('order_id');
            $table->integer('type');
            $table->string('product_id');
            $table->float('product_price');
            $table->float('resell_price');
            $table->integer('quantity');
            $table->float('delivery_charge');
            $table->float('direct_commision');
            $table->float('team_commision');
            $table->float('total_amount');
            $table->float('profit');
            $table->float('profit_total');
            $table->integer('create_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profit_shares');
    }
}
