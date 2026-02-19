<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained('professional_profiles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('avatar_image_id')->constrained('images')->onDelete('cascade');
            $table->string('display_name')->nullable()->comment('Surnom/nom d\'affichage personnalisé');
            $table->text('notes')->nullable()->comment('Notes privées du pro sur ce client');
            $table->string('custom_phone')->nullable()->comment('Téléphone alternatif spécifique');
            $table->string('custom_email')->nullable()->comment('Email alternatif spécifique');
            $table->json('tags')->nullable()->comment('Tags personnalisés ["VIP", "Fidèle", etc.]');
            $table->json('preferences')->nullable()->comment('Préférences spécifiques');

            $table->boolean('is_favorite')->default(false)->comment('Client favori');
            $table->boolean('is_blocked')->default(false)->comment('Client bloqué');

            // Statistiques
            $table->timestamp('first_visit_at')->nullable()->comment('Première visite chez ce pro');
            $table->timestamp('last_visit_at')->nullable()->comment('Dernière visite');
            $table->integer('total_appointments')->default(0)->comment('Nombre de RDV');
            $table->unsignedBigInteger('total_spent')->default(0)->comment('Montant total dépensé en centimes');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['professional_profile_id', 'user_id'], 'unique_pro_user');

            $table->index(['professional_profile_id', 'is_favorite'], 'idx_pro_favorites');
            $table->index(['professional_profile_id', 'is_blocked'], 'idx_pro_blocked');
            $table->index(['professional_profile_id', 'total_spent'], 'idx_pro_spent');
            $table->index('user_id', 'idx_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
