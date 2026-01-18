<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityDateBlockedSlot extends Model
{
    use HasFactory;

    protected $table = 'availability_date_blocked_slots';

    protected $fillable = [
        'override_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'override_id' => 'integer',
    ];

    public function override()
    {
        return $this->belongsTo(AvailabilityDateOverride::class, 'override_id');
    }
}
