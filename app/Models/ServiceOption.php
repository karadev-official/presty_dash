<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOption extends Model
{
    protected $table = 'service_options';

    protected $fillable = [
        'service_option_group_id',
        'name',
        'price',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(ServiceOptionGroup::class, 'service_option_group_id');
    }
}
