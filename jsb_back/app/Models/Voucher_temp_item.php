<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Voucher_temp;
use App\Models\Account_cate_main;

class Voucher_temp_item extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'voucher_temp_item';

    protected $fillable = [
        'voucher_temp_id',
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
        return $this->belongsTo(Voucher_temp::class, 'voucher_temp_id');
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
