<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'email',
        'password',
        'guid',
        'role',
        'google2fa_secret',
        'google2fa_enabled',
        'two_factor_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_verified_at' => 'datetime',
            'google2fa_enabled' => 'boolean',
        ];
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }

    public function sources()
    {
        return $this->belongsToMany(Source::class, 'user_source')->withTimestamps();
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'user_report');
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function dashboards()
    {
        return $this->hasMany(Dashboard::class)->orderBy('default', 'desc');
    }

    public function userGroups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_user');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function pinnedGraphs()
    {
        return $this->hasMany(PinnedGraph::class);
    }

    public function needsTwoFactorVerification(): bool
    {
        if (!$this->google2fa_enabled) {
            return false;
        }

        if (!$this->two_factor_verified_at) {
            return true;
        }

        return $this->two_factor_verified_at->diffInDays(now()) >= 14;
    }

    /**
     * Mark 2FA as verified
     */
    public function markTwoFactorAsVerified(): void
    {
        $this->update(['two_factor_verified_at' => now()]);
    }
}
