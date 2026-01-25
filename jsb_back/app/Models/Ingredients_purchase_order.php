<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredients_purchase_order extends Model
{
    use SoftDeletes;

    protected $table = 'ingredients_purchase_order';

    protected $fillable = [
        'storeId',
        'employeeId',
        'employeeName',
        'buyer',
        'status',
        'supplier',
        'invoiceNumber',
        'purchaseDate',
        'note',
        'total'
    ];

    public $timestamps = true;

    //明細
    public function details()
    {
        return $this->hasMany(Ingredients_purchase_order_detail::class, 'orderId');
    }

    //員工資料
    public function employee()
    {
        return $this->belongsTo(Employee_db::class, 'employeeId', 'employeeId');
    }
}
