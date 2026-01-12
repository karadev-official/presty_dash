<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    public const TYPE_SELF = 'self';
    public const TYPE_EMPLOYEE = 'employee';
    public const TYPE_COLLABORATOR = 'collaborator';

    protected $fillable = [
        'pro_user_id',
        'name',
        'specialty',
        'type',
        'is_default',
        'is_active',
        'linked_user_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_SELF,
            self::TYPE_EMPLOYEE,
            self::TYPE_COLLABORATOR,
        ];
    }

    public function pro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pro_user_id');
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }
}
