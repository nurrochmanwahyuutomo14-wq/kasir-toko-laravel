<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductBatch;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tanam Data Produk
        $product = Product::create([
            'name' => 'Kopi Kapal Api 20g',
            'barcode' => '123456789',
            'min_stock_alert' => 20,
        ]);

        // 2. Tanam Data Satuan (Harga Bertingkat)
        // Eceran
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'Pcs',
            'conversion_qty' => 1,
            'price' => 1500,
        ]);

        // Grosir Level 1 (Renteng)
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'Renteng',
            'conversion_qty' => 10,
            'price' => 14000, // Lebih murah daripada beli 10 pcs eceran
        ]);

        // Grosir Level 2 (Kardus)
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_name' => 'Kardus',
            'conversion_qty' => 120, // Misal 1 dus isi 12 renteng
            'price' => 160000,
        ]);

        // 3. Tanam Stok Awal (Batch)
        ProductBatch::create([
            'product_id' => $product->id,
            'stock_qty' => 240, // Kita punya stok 240 pcs (setara 2 Dus)
            'expired_date' => '2027-12-31',
        ]);
    }
}
