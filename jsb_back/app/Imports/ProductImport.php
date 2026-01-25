<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductImport implements ToModel
{


    public function model(array $row)
    {
        return new Product([
            // 'id' => $row[0],
            'storeId' => $row[1],
            'categoryId' => $row[2],
            'price' => $row[3],
            'enable' => $row[4],
            'imageUrl' => $row[5],
            'unit' => $row[6],
            'productId' => $row[7],
            'product_title' => $row[8],
            'taxType' => $row[9],
            'created_at' =>isset( $row[10]) ? date('Y-m-d H:i:s', strtotime($row[10])) : null,
            'deleted_at' =>isset( $row[11]) ? date('Y-m-d H:i:s', strtotime($row[11])) : null,
            
        ]);
    }

    
}
