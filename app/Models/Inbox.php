<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    /** @use HasFactory<\Database\Factories\InboxFactory> */
    use HasFactory;

    protected $fillable = [
        'group_id',
        'inbox_name',
        'slug',
    ];

    protected $appends = [
        'access_token',
        'viewer_key',
        'viewer_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (Inbox $inbox): void {
            if (! empty($inbox->getRawOriginal('access_token')) || ! empty($inbox->attributes['access_token'] ?? null)) {
                return;
            }

            $token = $inbox->group?->viewer_token;

            if (! $token && $inbox->group_id) {
                $token = Group::query()->whereKey($inbox->group_id)->value('viewer_token');
            }

            if ($token) {
                $inbox->attributes['access_token'] = $token;
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function getAccessTokenAttribute(): string
    {
        return $this->group?->viewer_token ?? '';
    }

    public function getViewerKeyAttribute(): string
    {
        return "{$this->slug}-{$this->group->viewer_token}";
    }

    public function getViewerUrlAttribute(): string
    {
        return route('viewer.index', ['viewerKey' => $this->viewer_key], false);
    }
}
