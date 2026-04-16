import Dexie from 'dexie';

// Inisialisasi Database IndexedDB
export const db = new Dexie('KasirOfflineDB');

// Definisi Struktur Tabel (Hanya yang di-index)
db.version(3).stores({
  products: '++id, barcode, category, name, price, stock, note',
  transactions: '++id, invoice_number, created_at, payment_method',
  transaction_details: '++id, transaction_id, product_id, product_name',
  debts: '++id, nama_pengutang, total_hutang, sudah_dibayar, status, tanggal_terakhir_bon',
  debt_details: '++id, debt_id, nama_produk, jumlah, harga_satuan, total_harga, tanggal_bon, is_lunas',
  expenses: '++id, name, amount, note, date'
});

// Fungsi untuk Injeksi Data Awal dari JSON Master (jika ada)
export async function seedInitialData() {
  const productCount = await db.products.count();
  if (productCount === 0) {
    console.log("Database kosong, memulai proses seeding...");
    
    try {
      // Mencoba mengambil data dari file JSON hasil ekspor Laravel
      const response = await fetch('products_master.json');
      if (response.ok) {
        const masterData = await response.json();
        console.log(`Menemukan ${masterData.length} produk di master data. Mengimpor...`);
        await db.products.bulkAdd(masterData);
        console.log("✅ Seeding master data berhasil.");
        return;
      }
    } catch (error) {
      console.warn("Gagal memuat produk dari JSON, beralih ke data dummy.", error);
    }

    // Fallback: Data Dummy jika file JSON tidak ada atau gagal dimuat
    console.log("Menggunakan data dummy untuk inisialisasi...");
    await db.products.bulkAdd([
      { barcode: '111111', category: 'Sembako', name: 'Beras Premium 5Kg', price: 75000, stock: 50, note: 'Rak Depan (Dummy)' },
      { barcode: '222222', category: 'Sembako', name: 'Minyak Goreng 2L', price: 34000, stock: 30, note: '(Dummy)' },
      { barcode: '333333', category: 'Snack', name: 'Chitato Sapi Panggang', price: 11500, stock: 100, note: '(Dummy)' },
      { barcode: '444444', category: 'Umum', name: 'Rokok Surya 12', price: 24000, stock: 20, note: 'Rak Kasir (Dummy)' }
    ]);
  }
}

export async function forceSyncMaster() {
    console.log("Memulai sinkronisasi paksa master data...");
    
    try {
        const response = await fetch('products_master.json');
        if (response.ok) {
            const masterData = await response.json();
            if (masterData.length > 0) {
                await db.products.clear();
                await db.products.bulkAdd(masterData);
                console.log("✅ Sinkronisasi paksa berhasil.");
                return true;
            }
        }
    } catch (error) {
        console.error("Gagal sinkronisasi paksa:", error);
    }
    return false;
}
