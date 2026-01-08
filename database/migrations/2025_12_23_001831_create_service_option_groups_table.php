<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_option_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // pro owner
            $table->string('client_id')->nullable(); // client side identifier
            $table->string('name'); // "Longueur", "Extra" "Couleur de cheveux"
            $table->string('slug')->unique()->nullable(); // "longueur", "extra" "couleur-de-cheveux"
            $table->enum('selection_type', ['single', 'multiple'])->default('single'); // radio/checkbox
            $table->boolean('is_required')->default(false);

            $table->unsignedInteger('min_select')->default(0);  // ex: 1 si obligatoire
            $table->unsignedInteger('max_select')->nullable();  // ex: 2

            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->index(['user_id', 'is_active', 'is_online', 'position', 'client_id'], 'usr_act_online_pos_cid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_option_groups');
    }
};
