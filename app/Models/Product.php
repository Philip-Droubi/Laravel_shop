<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = "products";
    protected $primaryKey = "id";
    protected $fillable = ['name', 'price',  'description', 'exp_date', 'img_url', 'quantity', 'category_id', 'user_id', 'views'];
    protected $timestamp = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function category()
    {
        return $this->belongsTo(Categorie::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function images()
    {
        return $this->hasMany(Prodimages::class);
    }
    public function sales()
    {
        return $this->hasOne(Sale::class);
    }
}
