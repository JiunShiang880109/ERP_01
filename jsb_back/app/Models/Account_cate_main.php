<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account_cate_sub;
use App\Models\Account_voucher_item;

class Account_cate_main extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_category_main';

    protected $fillable = [
        'code',
        'name',
    ];

    public $timestamps = true;

    public function subCates()
    {
        return $this->hasMany(
            Account_cate_sub::class,
            'main_code', // sub 表的欄位
            'code'       // main 表的欄位
        );
    }

    //主分類 → 多個科目
    public function accountItems()
    {
        return $this->hasMany(
            Account_item::class,
            'main_code',
            'code'
        );
    }

    //主分類 → 多個子科目
    public function accountLedger()
    {
        return $this->hasMany(
            Account_ledger::class,
            'main_code',
            'code'
        );
    }

    public function accountVoucherItems()
    {
        return $this->hasMany(
            Account_ledger::class,
            'main_code',
            'code'
        );
    }

    public function openAccounts(){
        return $this->hasMany(
            Account_opening_balance::class,
            'main_code',
            'code'
        );
    }

}
