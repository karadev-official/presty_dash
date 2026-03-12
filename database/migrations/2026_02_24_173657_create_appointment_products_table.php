<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('price');
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('total');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['appointment_id', 'product_id'], 'appointment_product_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_products');
    }
};
