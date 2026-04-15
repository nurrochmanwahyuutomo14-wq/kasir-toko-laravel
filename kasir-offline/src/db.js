import Dexie from 'dexie';

// Inisialisasi Database IndexedDB
export const db = new Dexie('KasirOfflineDB');

// Definisi Struktur Tabel (Hanya yang di-index)
db.version(1).stores({
  products: '++id, barcode, category, name, price, stock, note',
  transactions: '++id, invoice_number, created_at, payment_method',
  transaction_details: '++id, transaction_id, product_id, product_name',
  debts: '++id, customer_name, status, created_at'
});

// Fungsi untuk Injeksi Data Awal (Dummy) jika Kosong
export async function seedInitialData() {
  const productCount = await db.products.count();
  if (productCount === 0) {
    console.log("Seeding data stok awal...");
    await db.products.bulkAdd([
      { barcode: '111111', category: 'Sembako', name: 'Beras Premium 5Kg', price: 75000, stock: 50, note: 'Rak Depan' },
      { barcode: '222222', category: 'Sembako', name: 'Minyak Goreng 2L', price: 34000, stock: 30, note: '' },
      { barcode: '333333', category: 'Snack', name: 'Chitato Sapi Panggang', price: 11500, stock: 100, note: '' },
      { barcode: '444444', category: 'Umum', name: 'Rokok Surya 12', price: 24000, stock: 20, note: 'Rak Kasir' }
    ]);
  }
}
