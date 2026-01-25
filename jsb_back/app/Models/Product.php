<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Custom_category;


class Product extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'storeId',
        'categoryId',
        'price',
        'enable',
        'imageUrl',
        'unit',
        'productId',
        'product_title',
        'taxType',
        'created_at',
        'deleted_at'
    ];
    protected $table = 'products';

    public function recipe(){
        return $this->hasMany(Product_recipe::class, 'productId', 'id');
    }

    public function pd_taste(){
        return $this->belongsToMany(
            Custom_category::class,
            'products_with_custom',
            'productId',
            'customCateId',
            'productId',
            'id'
        );
    }

    //成本計算
    public function getCost(){
        return $this->recipe()->join('ingredients','ingredients.id', '=', 'product_recipe.ingredientId')
            ->selectRaw('SUM(product_recipe.usageQty * ingredients.costPerUnit) as cost')
            ->value('cost')??0;
    }

}
