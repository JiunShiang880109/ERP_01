<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Custom_category extends Model
{
    protected $table = 'custom_category';

    protected $fillable = [
        'storeId',
        'cateId',
        'customCateTitle',
        'enable',
    ];
}
