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
        Schema::table('service_options', function (Blueprint $table) {
            //changer le photo_url par image_id nullable unsignedBigInteger
            $table->foreignId('image_id')->nullable()->after('duration')->constrained('images')->nullOnDelete();
            $table->dropColumn('photo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_options', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('duration');
            $table->dropForeign(['image_id']);
            $table->dropColumn('image_id');
        });
    }
};
