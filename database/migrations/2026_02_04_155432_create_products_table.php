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
            $table->text('description')->nullable();
            $table->foreignId('product_category_id')->constrained()->cascadeOnDelete();
            $table->integer('position')->default(0);
            $table->integer('price')->default(0);
            $table->integer('quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['position']);
            $table->index(['is_active', 'is_online'], 'user_ao_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
