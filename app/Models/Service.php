<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{

    protected $table = 'services';
    protected $fillable = [
        'user_id',
        'service_category_id',
        'name',
        'slug',
        'description',
        'position',
        'duration', // minutes
        'price', // centimes
        'is_active',
        'is_online',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online' => 'boolean',
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

    public function images()
    {
        return $this->belongsToMany(Image::class, 'image_service');
    }
}
