<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredients_purchase_cateMain extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $table = 'ingredients_purchase_categorymain';

    protected $fillable = [
        'storeId',
        'sort',
        'name',
        'enable',
    ];

    public $timestamps = true;

    protected $attributes = [
        'enable' => 1,
    ];
    
    public function ingredients(){
        return $this->hasMany(Ingredients_db::class, 'categoryMainId');
    }
}
