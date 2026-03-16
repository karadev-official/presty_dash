<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_profile_id')->constrained('professional_profiles')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('resource_id')->nullable()->constrained('resources')->onDelete('set null');

            $table->foreignId('workplace_id')->nullable()->constrained('workplaces')->onDelete('set null');

            // Date et heure
            $table->date('date')->comment('Date du rendez-vous');
            $table->time('start_time')->comment('Heure de début');
            $table->time('end_time')->nullable()->comment('Heure de fin');
            $table->integer('duration')->comment('Durée totale en minutes');

            // status
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->comment("Statut du rendez-vous");

            // Prix
            $table->unsignedBigInteger('subtotal')->default(0)->comment('Sous-total en centimes (prestations + produits)');
            $table->unsignedBigInteger('discount')->default(0)->comment('Remise en centimes');
            $table->unsignedBigInteger('total')->default(0)->comment('Total final en centimes (subtotal - discount)');

            // Notes
            $table->text('customer_notes')->nullable()->comment("Notes du client");
            $table->text('internal_notes')->nullable()->comment("Notes privées du pro");

            // Annulation
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Rappels
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();

            // Confirmation
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['professional_profile_id', 'date', 'start_time'], 'idx_pro_datetime');
            $table->index(['customer_id', 'date'], 'idx_customer_date');
            $table->index(['status', 'date'], 'idx_status_date');
            $table->index(['date', 'start_time'], 'idx_datetime');
            $table->index(['workplace_id', 'date'], 'idx_workplace_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
