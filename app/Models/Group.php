<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    /** @use HasFactory<\Database\Factories\GroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'viewer_token',
        'status',
    ];

    protected static function booted(): void
    {
        static::updated(function (Group $group): void {
            if (! $group->wasChanged('viewer_token')) {
                return;
            }

            $group->inboxes()->update([
                'access_token' => $group->viewer_token,
            ]);
        });
    }

    public function inboxes(): HasMany
    {
        return $this->hasMany(Inbox::class);
    }
}
