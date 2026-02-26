<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_service_id')->constrained('appointment_services')->onDelete('cascade');
            $table->foreignId('service_option_id')->constrained('service_options')->onDelete('cascade');
            $table->foreignId('service_option_group_id')->constrained('service_option_groups')->onDelete('cascade');
            $table->string('option_name');
            $table->string('group_name');
            $table->integer('price')->default(0);
            $table->integer('duration')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_service_options');
    }
};
