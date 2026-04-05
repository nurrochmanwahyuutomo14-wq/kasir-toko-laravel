<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportBarangSeeder extends Seeder
{
    public function run(): void
    {
        $file = base_path('database/data/barang.csv');
        $data = array_map('str_getcsv', file($file));
        $header = array_shift($data);

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                // Lewati jika baris kosong
                if (empty($row[0])) continue;

                $item = array_combine($header, $row);

                // 1. Simpan ke Table Products (Dengan Proteksi Data Kosong)
                $product = Product::create([
                    'name'      => $item['name'],
                    // Jika barcode kosong, kasih null. Jika kategori kosong, kasih '-'
                    'barcode'   => !empty($item['barcode']) ? $item['barcode'] : null,
                    'category'  => !empty($item['category']) ? $item['category'] : '-',
                    // Jika min_stock bukan angka atau kosong, otomatis isi 10
                    'min_stock' => is_numeric($item['min_stock']) ? (int)$item['min_stock'] : 10,
                ]);

                // 2. Simpan ke Table ProductUnits
                ProductUnit::create([
                    'product_id'     => $product->id,
                    'unit_name'      => !empty($item['unit_name']) ? $item['unit_name'] : 'Pcs',
                    'price'          => is_numeric($item['price']) ? $item['price'] : 0,
                    'conversion_qty' => 1, // TAMBAHKAN BARIS INI (Set default ke 1)
                ]);

                // 3. Simpan ke Table ProductBatches (Stok)
                ProductBatch::create([
                    'product_id'   => $product->id,
                    'batch_number' => 'BATCH-' . now()->format('Ymd'),
                    'stock_qty'    => is_numeric($item['stock']) ? $item['stock'] : 0,
                    'expired_date' => now()->addYear(),
                ]);
            }
            DB::commit();
            $this->command->info("🎉 Mantap! Berhasil mengimpor barang meskipun ada data yang kosong.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Gagal Impor: " . $e->getMessage());
        }
    }
}
