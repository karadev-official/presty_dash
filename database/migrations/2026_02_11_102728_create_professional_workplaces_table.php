<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('professional_workplaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained('professional_profiles');
            $table->foreignId('address_id')->constrained('addresses');
            $table->string('location_name', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['professional_profile_id', 'is_primary'], 'pro_profile_primary_index');
            $table->unique(['professional_profile_id', 'address_id'], 'pro_address_unq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_workplaces');
    }
};
