<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timezone',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weekDays()
    {
        return $this->hasMany(AvailabilityWeekDay::class);
    }

    public function dateOverrides()
    {
        return $this->hasMany(AvailabilityDateOverride::class);
    }
}
