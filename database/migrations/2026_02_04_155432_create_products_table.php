<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
$table->string('name');
$table->string('slug');
$table->text('description');
$table->unsignedBigInteger('user_id');
$table->unsignedBigInteger('product_category_id');
$table->integer('position');
$table->integer('price');
$table->string('quantity')->nullable();
$table->boolean('is_active');
$table->boolean('is_online');
$table->timestamps();
$table->softDeletes();
$table->foreign('user_id')->references('id')->on('users');
$table->foreign('product_category_id')->references('id')->on('product_categories');
$table->index(['user_id', 'position']);
$table->index(['user_id', 'is_active', 'is_online'], 'user_ao_index');//
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
