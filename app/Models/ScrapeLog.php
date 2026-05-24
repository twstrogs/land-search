<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapeLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'facebook_group_id',
        'user_id',
        'action',
        'level',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_SUCCESS = 'success';

    public function facebookGroup(): BelongsTo
    {
        return $this->belongsTo(FacebookGroup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $action,
        string $message,
        string $level = self::LEVEL_INFO,
        ?FacebookGroup $group = null,
        ?User $user = null,
        ?array $context = null
    ): self {
        return static::create([
            'facebook_group_id' => $group?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public static function info(string $action, string $message, ?FacebookGroup $group = null, ?array $context = null): self
    {
        return static::log($action, $message, self::LEVEL_INFO, $group, null, $context);
    }

    public static function success(string $action, string $message, ?FacebookGroup $group = null, ?array $context = null): self
    {
        return static::log($action, $message, self::LEVEL_SUCCESS, $group, null, $context);
    }

    public static function warning(string $action, string $message, ?FacebookGroup $group = null, ?array $context = null): self
    {
        return static::log($action, $message, self::LEVEL_WARNING, $group, null, $context);
    }

    public static function error(string $action, string $message, ?FacebookGroup $group = null, ?array $context = null): self
    {
        return static::log($action, $message, self::LEVEL_ERROR, $group, null, $context);
    }
}
