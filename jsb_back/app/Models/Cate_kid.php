<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cate_kid extends Model
{
    protected $fillable = [
        'cateKidName',
        'cateMainId',
        'cateMidId',
        'enable',
        'sort'
    ];

    protected $table = 'cate_kid';
    public $timestamps = false;
    use HasFactory;
}
