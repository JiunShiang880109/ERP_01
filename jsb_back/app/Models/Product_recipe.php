<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Product_recipe extends Model
{
    use HasFactory;
    
    protected $table = 'product_recipe';

    public $timestamps = true;

    # id, productId, ingredientId, usageQty, unit, created_at, updated_at
    protected $fillable = [
        'productId',
        'ingredientId',
        'usageQty',
        'unit',
        'created_at',
        'updated_at'
    ];
    

    public function product(){
        return $this->belongsTo(Product::class, 'productId', 'id');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredients_db::class, 'ingredientId', 'id');
    }
}
