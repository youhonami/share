<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Tweet;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function tweets(): HasMany
    {
        return $this->hasMany(Tweet::class);
    }

    /**
     * このユーザーが行ったブロックのレコード
     */
    public function userBlocks(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'user_id');
    }

    /**
     * このユーザーがブロックしているユーザー（多対多・中間: user_blocks）
     */
    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_blocks',
            'user_id',
            'blocked_user_id',
        )->withTimestamps();
    }

    /**
     * このユーザーをブロックしているユーザー（逆参照）
     */
    public function blockers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_blocks',
            'blocked_user_id',
            'user_id',
        )->withTimestamps();
    }

    /**
     * このユーザーをブロックしているユーザーID一覧（タイムラインで除外する投稿者）
     */
    public function getBlockerUserIds(): array
    {
        return UserBlock::query()
            ->where('blocked_user_id', $this->id)
            ->pluck('user_id')
            ->all();
    }

    /**
     * 指定ユーザー（投稿者など）がこのユーザーをブロックしているか
     */
    public function isBlockedByUserId(int $authorUserId): bool
    {
        return UserBlock::query()
            ->where('user_id', $authorUserId)
            ->where('blocked_user_id', $this->id)
            ->exists();
    }
}
