<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredients_purchase_order_detail extends Model
{
    use SoftDeletes;

    protected $table = 'ingredients_purchase_order_detail';

    protected $fillable = [
        'orderId',
        'ingredientId',
        'categoryMainId',
        'quantity',
        'unitPrice',
        'total'
    ];

    public $timestamps = true;

    //主單
    public function order()
    {
        return $this->belongsTo(Ingredients_purchase_order::class, 'orderId');
    }

    //項目
    public function ingredient()
    {
        return $this->belongsTo(Ingredients_db::class, 'ingredientId')->withTrashed();
    }

    //類別
    public function category()
    {
        return $this->belongsTo(Ingredients_purchase_cateMain::class, 'categoryMainId')->withTrashed();
    }
}
