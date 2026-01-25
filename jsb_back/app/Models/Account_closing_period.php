<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account_closing_period extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_closing_period';

    protected $casts = [
        'closed_at' => 'date',
        'is_closed' => 'boolean',
    ];

    protected $fillable = [
        'fiscal_year',
        'fiscal_month',
        'closed_at',
        'is_closed',
        'note',
        'employeeId'
    ];

    public $timestamps = true;

    //查詢
    public function scopeOfYear($query, int $year){
        return $query->where('fiscal_year', $year);
    }
    //已關帳查詢
    public function scopeClosed($query){
        return $query->where('is_closed', 1);
    }
    //Helper:是否已關帳
    public static function isClosed(int $year, $month = null): bool{
        $query = self::where('fiscal_year', $year)
            ->where('is_closed', 1);

        if(is_null($month)){
            $query->whereNull('fiscal_month');//年結
        }else{
            $query->where('fiscal_month', $month);//月結
        }

        return $query->exists();
    }
}
