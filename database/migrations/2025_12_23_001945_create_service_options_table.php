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
        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_option_group_id')
                ->constrained('service_option_groups')
                ->cascadeOnDelete();

            $table->string('client_id')->nullable(); // client side identifier

            $table->string('name'); // "Court", "Moyen", "Extra 1"
            $table->string('slug')->unique()->nullable(); // "court", "moyen", "extra-1"
            $table->integer('price')->default(0);     // centimes (peut être 0)
            $table->integer('duration')->default(0);  // minutes (peut être 0)

            $table->string('photo_url')->nullable(); // ou photo_url plus tard
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->default(false);
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->index(['service_option_group_id', 'is_active', 'is_online', 'position', 'client_id'], 'sog_act_online_pos_cid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_options');
    }
};
