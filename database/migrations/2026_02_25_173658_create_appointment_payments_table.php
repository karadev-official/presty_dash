<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');

            $table->boolean('is_deposit')->default(false);

            $table->string('notes')->nullable();

            $table->timestamp('paid_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['appointment_id']);
            $table->index(['payment_method_id']);
            $table->index(['is_deposit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_payments');
    }
};
