<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account_opening_balance extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_opening_balance';

    protected $casts = [
        'offset_start_date' => 'date',
        'is_offset' => 'boolean',
        'opening_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'main_code',
        'sub_code',
        'item_code',
        'ledger_code',
        'fiscal_year',
        'fiscal_month',
        'opening_amount',
        'dc',
        'is_offset',
        'offset_start_date',
        'employeeId'
    ];

    public $timestamps = true;

    //查詢
    public function scopeOfYear($query, int $year){
        return $query->where('fiscal_year', $year);
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
