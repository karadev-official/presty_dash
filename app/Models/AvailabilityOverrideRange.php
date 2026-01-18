<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilityOverrideRange extends Model
{
    use HasFactory;

    protected $table = 'availability_override_ranges';

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
