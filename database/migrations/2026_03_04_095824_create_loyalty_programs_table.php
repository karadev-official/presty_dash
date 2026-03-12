<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Programme de fidélité (configuration globale)
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained('professional_profiles')->cascadeOnDelete();
            $table->string('name')->default('Programme de Fidélité');
            $table->text('description')->nullable();
            $table->unsignedInteger('min_appointment_amount')->default(0); // ✅ unsigned
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // ✅ Index
            $table->index(['professional_profile_id', 'is_active']);
        });

        // Paliers de récompenses
        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('required_visits'); // ✅ unsigned
            $table->unsignedInteger('discount_amount'); // ✅ unsigned
            $table->unsignedInteger('order')->default(0); // ✅ unsigned
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // ✅ Index
            $table->index(['loyalty_program_id', 'is_active', 'required_visits'], "loyalty_rewards_program_id");
            // ✅ Unique
//            $table->unique(['required_visits', 'loyalty_program_id'], 'unique_loyalty_rewards');
        });

        // Cartes de fidélité des clients
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loyalty_program_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_visits')->default(0); // ✅ unsigned
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // ✅ Index
            $table->index(['loyalty_program_id', 'is_active']);
            $table->unique(['customer_id', 'loyalty_program_id'], 'unique_customer_program');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_cards');
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_programs');
    }
};
