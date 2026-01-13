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
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialty')->nullable()->after('name');
            $table->foreignId('avatar_image_id')->nullable()->after('specialty')->constrained('images')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('specialty');
            $table->dropForeign(['avatar_image_id']);
            $table->dropColumn('avatar_image_id');
        });
    }
};
