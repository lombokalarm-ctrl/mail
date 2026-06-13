<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    /** @use HasFactory<\Database\Factories\InboxFactory> */
    use HasFactory;

    protected $fillable = [
        'inbox_name',
        'slug',
        'access_token',
    ];

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function getViewerKeyAttribute(): string
    {
        return "{$this->slug}-{$this->access_token}";
    }

    public function getViewerUrlAttribute(): string
    {
        return route('viewer.index', ['viewerKey' => $this->viewer_key], false);
    }
}
