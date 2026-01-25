<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cate_main extends Model
{
    protected $fillable = [
        'cateMainName',
        'enable',
        'sort',
    ];

    protected $table = 'cate_main';
    public $timestamps = false;
    use HasFactory;
}
