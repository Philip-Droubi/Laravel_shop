<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = "categories";
    protected $primaryKey = "id";
    protected $fillable = ['name', 'description', 'img_url'];
    protected $timestamp = true;

    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function setNameAttribute($val)
    {
        $this->attributes['name'] = strtolower($val);
    }
}
