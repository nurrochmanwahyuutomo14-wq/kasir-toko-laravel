<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class ExportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:products-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all products to JSON for offline app';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mengambil data produk dari database...');

        $products = Product::with(['units', 'batches'])->get();

        $data = $products->map(function ($p) {
            // Ambil harga PCS
            $price = $p->units->where('unit_name', 'Pcs')->first()->price ?? 0;
            
            // Hitung total stok dari semua batch
            $stock = $p->batches->sum('stock_qty');

            return [
                'id' => $p->id,
                'barcode' => $p->barcode,
                'category' => $p->category ?? 'Umum',
                'name' => $p->name,
                'price' => (int) $price,
                'stock' => (int) $stock,
                'note' => $p->keterangan ?? ''
            ];
        });

        $json = json_encode($data, JSON_PRETTY_PRINT);
        
        // Path tujuan: kasir-offline/public/products_master.json
        $outputPath = base_path('kasir-offline/public/products_master.json');

        if (!File::isDirectory(dirname($outputPath))) {
            $this->error('Folder kasir-offline/public tidak ditemukan!');
            return;
        }

        File::put($outputPath, $json);

        $this->info("✅ Sukses! " . $data->count() . " produk telah diekspor ke:");
        $this->line($outputPath);
    }
}
