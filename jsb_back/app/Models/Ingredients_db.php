<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Ingredients_purchase_order_detail;

class Ingredients_db extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'ingredients';

    protected $fillable = [
        'storeId',
        'categoryMainId',
        'imageUrl',
        'name',
        'unit',
        'safeAmount',
        'stockAmount',
        'enable',
    ];

    public $timestamps = true;

    protected $attributes = [
        'enable' => 1,
    ];

    protected $casts = [
        'safeAmount' => 'float',
        'stockAmount' => 'float',
        'enable' => 'boolean',
    ];

    public function ingredientsCateMain(){
        return $this->belongsTo(Ingredients_purchase_cateMain::class, 'categoryMainId')->withTrashed();;
    }

    public function lastPurchase(){
        return $this->hasOne(Ingredients_purchase_order_detail::class, 'ingredientId')
                    ->latestOfMany('created_at');
    }

    public function recipes(){
        return $this->hasMany(Product_recipe::class, 'ingredientId', 'id');
    }

}
