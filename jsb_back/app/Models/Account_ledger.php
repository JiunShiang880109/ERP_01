<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account_cate_sub;
use App\Models\Employee_db;
use App\Models\Account_cate_main;
use App\Models\Account_item;
class Account_ledger extends Model
{
    //use SoftDeletes;

    protected $table = 'account_ledger';

    protected $fillable = [
        'main_code',
        'sub_code',
        'item_code',
        'code',
        'employeeId',
        'name',
        'enable',
        'created_by',
    ];

    //關聯員工資料
    public function employee()
    {
        return $this->belongsTo(Employee_db::class, 'employeeId', 'employeeId');
    }
    //關聯主類別

            public function mainCate()
    {
        return $this->belongsTo(
            Account_cate_main::class,
            'main_code',
            'code'
        );
    }

}

