<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->float('price');
            $table->integer('category');
            $table->integer('team_commision');
            $table->integer('direct_commision');
            $table->integer('is_store_pick');
            $table->integer('waranty');
            $table->string('description');
            $table->string('supplier_name');
            $table->integer('stock_count');
            $table->string('images');
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
        Schema::dropIfExists('products');
    }
}
