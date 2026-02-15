<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfessionalProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pro_user_id',
        'specialty',
        'company_name',
        'siret',
        'description',
    ];

    public function pro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pro_user_id');
    }

    public function workLocations(): HasMany
    {
        return $this->hasMany(ProfessionalWorkLocation::class);
    }
}
