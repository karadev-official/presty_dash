<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() : void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->integer('position');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_online')->nullable()->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['professional_profile_id', 'slug']);
            $table->index(['professional_profile_id', 'is_active', 'is_online', 'position'], 'pro_ao_index');
        });
    }

    public function down() : void
    {
        Schema::dropIfExists('product_categories');
    }
};
