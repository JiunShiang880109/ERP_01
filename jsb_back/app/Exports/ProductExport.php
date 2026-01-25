<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProductExport implements FromCollection, WithMapping,WithHeadings
{
  
    public function collection()
    {
        return Product::where('deleted_at', null)->get();
        // return Product::all();

    }

    public function map($product): array
    {
        return [
            $product->id ? $product->id : '',
            $product->storeId ? $product->storeId : '',
            $product->categoryId ? $product->categoryId : '',
            $product->price ? $product->price : '',
            $product->enable ? $product->enable : '',
            $product->imageUrl ? $product->imageUrl : '',
            $product->unit ? $product->unit : '',
            $product->productId ? $product->productId : '',
            $product->product_title ? $product->product_title : '',
            $product->taxType ? $product->taxType : '',
            $product->created_at ? $product->created_at : '',
            $product->deleted_at ? $product->deleted_at : '',
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'storeId',
            'categoryId',
            'price',
            'enable',
            'imageUrl',
            'unit',
            'productId',
            'product_title',
            'taxType',
            'created_at',
            'deleted_at',

        ];
    }
}
