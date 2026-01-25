<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cate_mid extends Model
{
    protected $fillable = [
        'cateMidName',
        'cateMainId',
        'enable',
        'sort',
    ];

    protected $table = 'cate_mid';
    public $timestamps = false;    
    use HasFactory;
}
