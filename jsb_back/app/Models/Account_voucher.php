<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account_voucher_item;

class Account_voucher extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_voucher';

    protected $casts = [
        'voucher_date' => 'date',
    ];

    protected $fillable = [
        'voucher_date',
        'voucher_code',
        'voucher_type',
        'voucher_kind',
        'employeeId',
        'note',
    ];

    public $timestamps = true;

    public function items()
    {
        return $this->hasMany(Account_voucher_item::class, 'voucher_id');
    }

    protected static function booted(){
        static::deleting(function ($voucher){
            //關聯刪除
            $voucher->items()->delete();
        });

        static::restoring(function ($voucher){
            //復原用
            $voucher->items()->withTrashed()->restore();
        });
    }


}
