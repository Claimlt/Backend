<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'user';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'contact_number',
        'nic',
        'role',
        'status',
    ];

    protected $hidden = [
        'nic',
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //  UUID settings
    public $incrementing = false;   // no auto increment
    protected $keyType = 'string';

    // Auto-generate UUID when creating user
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
    public function userDetails()
    {
        return $this->hasOne(UserDetails::class, 'user_id', 'id');
    }
}
