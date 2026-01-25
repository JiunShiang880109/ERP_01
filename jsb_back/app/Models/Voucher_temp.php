<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Voucher_temp_item;

class Voucher_temp extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'voucher_temp';

    protected $casts = [
        'voucher_date' => 'date',
    ];

    protected $fillable = [
        'voucher_date',
        'voucher_code',
        'voucher_type',
        'employeeId',
        'note',
    ];

    public $timestamps = true;

    public function items()
    {
        return $this->hasMany(Voucher_temp_item::class, 'voucher_temp_id');
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
