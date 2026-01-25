<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense_Cate_Main extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'expenses_category_main';

    protected $fillable = [
        'storeId',
        'sort',
        'name',
        'enable',
    ];

    public $timestamps = true;//因為只有created_at

    public function expenses(){
        return $this->hasMany(Expense::class, 'category_main_id');
    }

    protected $attributes = [
        'enable' => 1,
    ];
}
