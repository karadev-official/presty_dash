<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOptionGroup extends Model
{
    use HasFactory;

    protected $table = 'service_option_groups';

    protected $fillable = [
        'user_id',
        'title',
        'selection_type',
        'is_required',
        'min_select',
        'max_select',
        'is_active',
        'position',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function options()
    {
        return $this->hasMany(ServiceOption::class)->orderBy('position');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_service_option_group')
            ->withPivot(['position'])
            ->withTimestamps()
            ->orderBy('service_service_option_group.position');
    }
}
