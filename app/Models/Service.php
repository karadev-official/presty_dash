<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    protected $table = 'services';
    protected $fillable = [
        'name',
        'slug',
        'service_category_id',
        'description',
        'duration',
        'price',
        'is_active',
        'is_online',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function optionGroups()
    {
        return $this->belongsToMany(\App\Models\ServiceOptionGroup::class, 'service_service_option_group')
            ->withPivot(['position'])
            ->withTimestamps()
            ->orderBy('service_service_option_group.position');
    }
}
