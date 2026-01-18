<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityDayBlockedSlot extends Model
{
    use HasFactory;

    protected $table = 'availability_day_blocked_slots';

    protected $fillable = [
        'week_day_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'week_day_id' => 'integer',
    ];

    public function weekDay()
    {
        return $this->belongsTo(AvailabilityWeekDay::class, 'week_day_id');
    }
}
