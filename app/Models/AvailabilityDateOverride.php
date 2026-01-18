<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityDateOverride extends Model
{
    use HasFactory;

    protected $table = 'availability_date_overrides';

    protected $fillable = [
        'availability_id',
        'date',
        'is_off',
    ];

    protected $casts = [
        'availability_id' => 'integer',
        'is_off' => 'boolean',
        'date' => 'date:Y-m-d',
    ];

    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function ranges()
    {
        return $this->hasMany(AvailabilityOverrideRange::class, 'override_id');
    }

    public function blockedSlots()
    {
        return $this->hasMany(AvailabilityDateBlockedSlot::class, 'override_id');
    }
}
