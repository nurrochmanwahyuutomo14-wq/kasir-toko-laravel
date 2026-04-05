<div class="space-y-6">
    @if (session()->has('message'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm mb-4">
        {{ session('message') }}
    </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">📦 Master Barang</h1>
            <p class="text-sm text-gray-500">Total Produk di Database: {{ $products->total() }}</p>
        </div>

        <div class="flex gap-3 w-full md:w-auto">
            <input wire:model.live="search" type="text" placeholder="Cari nama / barcode..." class="w-full md:w-80 p-4 border-2 border-gray-100 rounded-2xl focus:border-blue-500 outline-none transition-all">

            <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl transition-all transform hover:scale-105">
                + TAMBAH BARANG
            </button>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm overflow-hidden border border-gray-100">
        <table class="w-full text-left">
            <thead class="bg-gray-50 text-gray-400 text-[10px] uppercase font-black tracking-widest border-b">
                <tr>
                    <th class="p-6">Produk & Barcode</th>
                    <th class="p-6">Harga Jual (Pcs/Rtg/Dus)</th>
                    <th class="p-6 text-center">Stok Gudang</th>
                    <th class="p-6 text-right">Tindakan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($products as $product)
                <tr class="hover:bg-blue-50/30 transition">
                    <td class="p-6">
                        <span class="text-lg font-bold text-gray-800 block">{{ $product->name }}</span>
                        <span class="text-xs font-mono bg-blue-100 text-blue-600 px-2 py-1 rounded">{{ $product->barcode ?? 'N/A' }}</span>
                    </td>
                    <td class="p-6">
                        <div class="flex gap-2">
                            @foreach($product->units as $unit)
                            <div class="bg-white border rounded-lg p-2 text-center min-w-[80px]">
                                <p class="text-[9px] text-gray-400 font-bold uppercase">{{ $unit->unit_name }}</p>
                                <p class="text-xs font-black text-blue-600">Rp{{ number_format($unit->price, 0, ',', '.') }}</p>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="p-6 text-center">
                        <span class="text-2xl font-black text-gray-800">{{ $product->batches->sum('stock_qty') }}</span>
                        <span class="text-[10px] block text-gray-400 font-bold">TOTAL PCS</span>
                    </td>
                    <td class="p-6 text-right">
                        <div class="flex justify-end gap-3">
                            <button wire:click="edit({{ $product->id }})" class="flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-bold py-3 px-5 rounded-xl transition shadow-md">
                                ✏️ EDIT HARGA
                            </button>
                            <button wire:click="openStock({{ $product->id }})" class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-5 rounded-xl transition shadow-md">
                                📦 + STOK
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-6 bg-gray-50 border-t">
            {{ $products->links() }}
        </div>
    </div>

    @if($isOpen)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="bg-blue-600 p-6 text-white flex justify-between">
                <h3 class="text-xl font-black uppercase tracking-tight">{{ $productId ? 'Edit Barang' : 'Tambah Barang Baru' }}</h3>
                <button wire:click="$set('isOpen', false)" class="text-2xl font-bold">&times;</button>
            </div>

            <form wire:submit="store">
                <div class="p-8 grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="text-xs font-bold text-gray-400 uppercase">Nama Barang</label>
                        <input type="text" wire:model="name" autofocus class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1 font-bold">
                        @error('name') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Barcode</label>
                        <input type="text" wire:model="barcode" class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Kategori</label>
                        <select wire:model="category" class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1">
                            <option value="Umum">Umum</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                        </select>
                    </div>
                    <div class="col-span-2 grid grid-cols-3 gap-4 bg-gray-50 p-6 rounded-2xl">
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Harga Pcs</label>
                            <input type="number" wire:model="price_pcs" class="w-full p-3 border border-gray-200 rounded-lg font-bold text-blue-600">
                            @error('price_pcs') <span class="text-red-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Harga Renteng</label>
                            <input type="number" wire:model="price_renteng" class="w-full p-3 border border-gray-200 rounded-lg font-bold text-blue-600">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase">Harga Kardus</label>
                            <input type="number" wire:model="price_dus" class="w-full p-3 border border-gray-200 rounded-lg font-bold text-blue-600">
                        </div>
                    </div>
                    @if(!$productId)
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Stok Awal (Pcs)</label>
                        <input type="number" wire:model="stock_initial" class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Tanggal Expired</label>
                        <input type="date" wire:model="expired_date" class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1">
                    </div>
                    @endif
                </div>
                <div class="p-6 bg-gray-50 text-right">
                    <button type="submit" wire:loading.attr="disabled" class="bg-blue-600 text-white font-black py-4 px-10 rounded-2xl shadow-lg hover:bg-blue-700 transition-all">
                        <span wire:loading.remove wire:target="store">SIMPAN DATA</span>
                        <span wire:loading wire:target="store">MENYIMPAN...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($isStockOpen)
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in duration-300">
            <div class="bg-green-600 p-6 text-white flex justify-between">
                <h3 class="text-xl font-black uppercase">Tambah Stok Gudang</h3>
                <button wire:click="$set('isStockOpen', false)" class="text-2xl font-bold">&times;</button>
            </div>

            <form wire:submit="addStockOnly">
                <div class="p-8 space-y-6">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Jumlah Masuk (Pcs)</label>
                        <input type="number" wire:model="stock_initial" autofocus class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1 text-2xl font-black">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase">Tanggal Kedaluwarsa</label>
                        <input type="date" wire:model="expired_date" class="w-full p-4 border-2 border-gray-100 rounded-xl focus:border-blue-500 outline-none mt-1">
                    </div>
                </div>
                <div class="p-6 bg-gray-50">
                    <button type="submit" wire:loading.attr="disabled" class="w-full bg-green-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-green-700">
                        <span wire:loading.remove wire:target="addStockOnly">UPDATE STOK</span>
                        <span wire:loading wire:target="addStockOnly">MEMPROSES...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>