<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'role' => UserRole::class,
        ];
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class, 'owner_id');
    }

    /**
     * Generate a new remember token for the user.
     *
     * @return string
     */
    public function setRememberToken($value)
    {
        $this->{$this->getRememberTokenName()} = $value;
        
        // Log remember token generation untuk audit
        Log::info('Remember token generated', [
            'user_id' => $this->id,
            'email' => $this->email,
            'timestamp' => now()
        ]);
    }

    /**
     * Clear the remember token.
     *
     * @return void
     */
    public function clearRememberToken()
    {
        $this->setRememberToken(null);
        $this->save();
        
        Log::info('Remember token cleared', [
            'user_id' => $this->id,
            'email' => $this->email,
            'timestamp' => now()
        ]);
    }

    /**
     * Check if user has a valid remember token.
     *
     * @return bool
     */
    public function hasRememberToken(): bool
    {
        return !empty($this->{$this->getRememberTokenName()});
    }
}
