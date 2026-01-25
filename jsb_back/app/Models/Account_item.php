<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Account_cate_main;
use App\Models\Account_cate_sub;
use App\Models\Account_ledger;

class Account_item extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'account_item';

    protected $fillable = [
        'main_code',
        'sub_code',
        'code',
        'name',
    ];

    public $timestamps = true;

    public function mainCate()
    {
        return $this->belongsTo(
            Account_cate_main::class,
            'main_code',
            'code'
        );
    }


}
