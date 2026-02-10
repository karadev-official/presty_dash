<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->default("")->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_category_id');
            $table->integer('position')->default(0);
            $table->integer('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('product_category_id')->references('id')->on('product_categories');
            $table->index(['user_id', 'position']);
            $table->index(['user_id', 'is_active', 'is_online'], 'user_ao_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
