<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account_voucher;
use App\Models\Account_cate_main;

class Account_voucher_item extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_voucher_item';

    protected $fillable = [
        'voucher_id',
        'main_code',
        'sub_code',
        'item_code',
        'ledger_code',
        'dc',           //借/貸
        'amount',
        'note',
    ];

    public $timestamps = true;

    public function voucher()
    {
        return $this->belongsTo(Account_voucher::class, 'voucher_id');
    }

    public function mainCate()
    {
        return $this->belongsTo(
            Account_cate_main::class,
            'main_code',
            'code'
        );
    }
}
