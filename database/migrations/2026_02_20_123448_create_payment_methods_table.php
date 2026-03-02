<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->default("#6B7280");
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['is_active']);
            $table->index(['position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
