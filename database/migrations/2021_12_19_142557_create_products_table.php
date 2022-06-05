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
            $table->string('name');
            $table->float('price');
            $table->longText('description')->default('no description');
            $table->longtext('img_url')->default('pxcLtWJY7ahgoCE5toU8EtJ0OvYnJxPuioAoyXzhUj71k8DA0kdefaultcategoryimg.png');
            $table->integer('quantity')->default(1);
            $table->integer('views')->default(0);
            $table->date('exp_date')->nullable();
            $table->foreignId('category_id')->default(1)->constrained('categories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

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
