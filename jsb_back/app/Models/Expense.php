<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Employee_db;

class Expense extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'expenses';

    protected $fillable = [
        'storeId',
        'employeeId',
        'employeeName',
        'category_main_id',
        'category_sub',
        'amount',
        'note',
        'payMethod',
        'date'
    ];

    public $timestamps = true;//因為只有created_at

    public function categoryMain(){
        return $this->belongsTo(Expense_Cate_Main::class, 'category_main_id')->withTrashed();;
    }

    //關聯員工資料
    public function employee()
    {
        return $this->belongsTo(Employee_db::class, 'employeeId', 'employeeId');
    }
}
