<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pro_user_id',
        'specialty',
        'company_name',
        'siret',
        'description',
    ];


    /* ===========================
       Relations
    =========================== */
    public function pro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pro_user_id');
    }

    public function workplaces(): HasMany
    {
        return $this->hasMany(Workplace::class);
    }

    /**
     * Rendez-vous du professionnel
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function loyaltyProgram(): HasOne
    {
        return $this->hasOne(LoyaltyProgram::class);
    }

    public function loyaltyCards(): HasManyThrough
    {
        return $this->hasManyThrough(
            LoyaltyCard::class,
            LoyaltyProgram::class
        );
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }


    /* ===========================
       Accesseurs
    =========================== */
    /**
     * Rendez-vous à venir
     */
    public function upcomingAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->upcoming()
            ->with(['customer', 'services', 'products']);
    }

    public function todayAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class)
            ->today()
            ->with(['customer', 'services', 'products']);
    }
}
