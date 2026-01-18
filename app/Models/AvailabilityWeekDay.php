<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityWeekDay extends Model
{
    use HasFactory;

    protected $table = 'availability_week_days';

    protected $fillable = [
        'availability_id',
        'weekday',
        'enabled',
        'slot_duration_min',
    ];

    protected $casts = [
        'availability_id' => 'integer',
        'weekday' => 'integer',
        'enabled' => 'boolean',
        'slot_duration_min' => 'integer',
    ];

    public function availability()
    {
        return $this->belongsTo(Availability::class);
    }

    public function ranges()
    {
        return $this->hasMany(AvailabilityWeekRange::class, 'week_day_id');
    }

    public function blockedSlots()
    {
        return $this->hasMany(AvailabilityDayBlockedSlot::class, 'week_day_id');
    }
}
