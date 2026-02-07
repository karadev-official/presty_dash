<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
$table->unsignedBigInteger('user_id');
$table->string('name');
$table->string('slug');
$table->integer('position');
$table->timestamps();
$table->softDeletes();
$table->foreign('user_id')->references('id')->on('users');
$table->index(['user_id', 'position']);//
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
};
