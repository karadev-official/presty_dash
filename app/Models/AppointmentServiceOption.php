<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentServiceOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'appointment_service_id',
        'service_option_id',
        'service_option_group_id',
        'option_name',
        'group_name',
        'price',
        'duration',
    ];

    public function appointmentService(): BelongsTo
    {
        return $this->belongsTo(AppointmentService::class);
    }

    public function serviceOption(): BelongsTo
    {
        return $this->belongsTo(ServiceOption::class);
    }

    public function serviceOptionGroup(): BelongsTo
    {
        return $this->belongsTo(ServiceOptionGroup::class);
    }
}
