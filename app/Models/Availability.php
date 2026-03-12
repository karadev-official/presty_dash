<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Availability extends Model
{
//    use HasFactory;
    protected $fillable = [
        'professional_profile_id',
        'timezone',
    ];

//    protected $casts = [
//        'professional_profile_id' => 'integer',
//    ];

    public function professionalProfile() : BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    public function weekDays(): HasMany
    {
        return $this->hasMany(AvailabilityWeekDay::class);
    }

    public function dateOverrides(): HasMany
    {
        return $this->hasMany(AvailabilityDateOverride::class);
    }
}
