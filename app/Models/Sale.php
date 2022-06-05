<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $table = "sales";
    protected $primaryKey = "id";
    protected $fillable = ['start', 'end', 'discount', 'product_id'];
    protected $timestamp = true;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
