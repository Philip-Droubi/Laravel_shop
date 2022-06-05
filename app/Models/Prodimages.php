<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodimages extends Model
{
    use HasFactory;
    protected $table = "prodimages";
    protected $primaryKey = "id";
    protected $fillable = ['path', 'product_id', 'user_id'];
    protected $timestamp = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
