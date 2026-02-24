<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments');
            $table->foreignId('service_id')->constrained('services');
            $table->unsignedBigInteger('price');
            $table->integer('duration');
            $table->integer('quantity')->default(1)->comment('Quantité');
            $table->unsignedBigInteger('total')->comment('Prix total (price * quantity) en centimes');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['appointment_id', 'service_id'], 'appointment_service_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_services');
    }
};
