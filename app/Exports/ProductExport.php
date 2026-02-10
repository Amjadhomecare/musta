<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::with('category')->get()->map(function ($product) {
            return [
                'product_name' => $product->product_name,
                'category_name' => $product->category->category_name , // Accessing the name from the category relationship
                'supplier_id' => $product->supplier_id,
                // ... other product fields
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Category Name',
            'Supplier ID',
            // ... other headings
        ];
    }
}
