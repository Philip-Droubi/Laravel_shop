<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Product;
use App\Models\Like;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'prof_img_url',
        'facebook_url',
        'whatsapp_url',
        'role_id',
        'is_email_verified',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];
    protected $casts = [
        'email_verified_at' => 'date',
    ];
    protected $table = "users";
    protected $primaryKey = "id";
    protected $timestamp = true;

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function providers()
    {
        return $this->hasMany(Provider::class, 'user_id', 'id');
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    ////Accessors
    public function getNameAttribute($name)
    {
        return ucfirst($name);
    }
    ////Mutators
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strtolower($name);
    }
    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }
}
