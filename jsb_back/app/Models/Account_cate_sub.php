<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account_cate_main;
use App\Models\Account_item;
class Account_cate_sub extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_category_sub';

    protected $fillable = [
        'main_code',
        'code',
        'name',
    ];

    public $timestamps = true;

    public function mainCate()
    {
        return $this->belongsTo(
            Account_cate_main::class,
            'main_code', // sub 表欄位
            'code'       // main 表欄位
        );
    }

}
