<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp', 'svg'];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function hasImage(): bool
    {
        foreach (self::IMAGE_EXTENSIONS as $ext) {
            if (Storage::exists("user_images/{$this->id}.{$ext}")) return true;
        }
        return false;
    }

    protected $fillable = [
        'name',
    ];

    protected static function booted(): void
    {
        self::deleting(function (User $user) {
            $user->tokens()->delete();
        });
    }

}
