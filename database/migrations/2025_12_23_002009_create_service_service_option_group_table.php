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
        Schema::create('service_service_option_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('service_option_group_id')->constrained('service_option_groups')->cascadeOnDelete();

            // ordre d’affichage du groupe dans la prestation
            $table->unsignedInteger('position')->default(0);

            $table->timestamps();

            $table->unique(['service_id', 'service_option_group_id'], 'svc_opt_grp_unique');
            $table->index(['service_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_service_option_group');
    }
};
