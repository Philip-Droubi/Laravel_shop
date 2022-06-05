<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = "roles";
    protected $primaryKey = "id";
    protected $fillable = ['name', 'description', 'ability'];
    protected $timestamp = true;

    protected $casts = [
        'ability' => 'array'
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
