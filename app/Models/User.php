<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'profile_photo',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Set the user's email.
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute(string $value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Add attribute names to the appends property if they do
     * not have a corresponding column in the database. The
     * attribute should be in snake case.
     *
     * @var string[]
     */
    public $appends = [
        'profile_image_url',
    ];


    /**
     * The accessor required to make an attribute that does not
     * have a corresponding column in the database visible. The
     * accessor should be in camel case.
     *
     * @return string
     */
    public function getProfileImageUrlAttribute(): string
    {
        if (!$this->profile_photo) {
            $imagePath = 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
            return $imagePath;
        }
        $imagePath = '/uploads/profile_photos/' . $this->profile_photo;
        return asset($imagePath);
    }

    /**
     * User has many Todos
     * One to many relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function todos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Todo::class);
    }
}
