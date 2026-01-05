<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ServiceOption extends Model
{
    protected $table = 'service_options';

    protected $fillable = [
        'service_option_group_id',
        'client_id',
        'name',
        'slug',
        'duration',
        'price',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function setSlugAttribute($value)
    {
        $originalSlug = Str::slug($value);
        $slug = $originalSlug;
        $count = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        $this->attributes['slug'] = $slug;
    }

    public function group()
    {
        return $this->belongsTo(ServiceOptionGroup::class, 'service_option_group_id');
    }
}
