<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'vacation_days_yearly',
        'is_manager',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'is_manager' => 'boolean',
        ];
    }

    public function vacationRequests(): HasMany
    {
        return $this->hasMany(VacationRequest::class);
    }

    public function vacationDaysUsed(): int
    {
        return $this->vacationRequests()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');
    }

    public function vacationDaysRemaining(): int
    {
        return $this->vacation_days_yearly - $this->vacationDaysUsed();
    }

    public function isAdmin(): bool
    {
        return $this->allTeams()->contains('name', 'Admin');
    }

    public function isManager(): bool
    {
        return $this->allTeams()->contains('name', 'Manager');
    }

    public function isStaff(): bool
    {
        return $this->allTeams()->contains('name', 'Staff');
    }
}
