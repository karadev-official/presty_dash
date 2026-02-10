<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceOptionGroup extends Model
{
    use HasFactory;

    protected $table = 'service_option_groups';

    protected $fillable = [
        'user_id',
        'client_id',
        'name',
        'slug',
        'selection_type',
        'is_required',
        'min_select',
        'max_select',
        'is_active',
        'is_online',
        'position',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->hasMany(ServiceOption::class)->orderBy('position');
    }

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

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_service_option_group')
            ->withPivot(['position'])
            ->withTimestamps()
            ->orderBy('service_service_option_group.position');
    }
}
