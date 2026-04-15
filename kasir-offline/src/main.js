import { db, seedInitialData } from './db.js';

let cart = [];

const MainApp = {
  async init() {
    await seedInitialData();
    this.loadDashboard();
  },

  // ==========================
  // DASHBOARD
  // ==========================
  async loadDashboard() {
    const today = new Date().toISOString().split('T')[0];
    const transactions = await db.transactions.toArray();
    
    // Filter hari ini (menggunakan awalan tanggal ISO format)
    const todayTrx = transactions.filter(t => t.created_at.startsWith(today));
    
    const omzet = todayTrx.reduce((sum, t) => sum + (t.amount_paid - (t.change_amount || 0)), 0);
    const trxCount = todayTrx.length;
    
    // Total barang terjual (hanya hitung dari transaksi hari ini)
    let itemsOut = 0;
    for(let t of todayTrx) {
      const details = await db.transaction_details.where({transaction_id: t.id}).toArray();
      itemsOut += details.length; // Sederhana: hitung QTY baris, bisa ekspansi nanti
    }

    document.getElementById('dash-omzet').innerText = `Rp ${omzet.toLocaleString('id-ID')}`;
    document.getElementById('dash-trx').innerText = `${trxCount} Nota`;
    document.getElementById('dash-items').innerText = `${itemsOut} Item`;
  },

  // ==========================
  // BARANG
  // ==========================
  async loadProducts() {
    const products = await db.products.toArray();
    const list = document.getElementById('product-list');
    list.innerHTML = products.map(p => `
      <div class="bg-white p-4 rounded-[16px] shadow-sm flex justify-between items-center" onclick="MainApp.editProduct(${p.id})">
        <div>
          <h3 class="font-black text-lg">${p.name}</h3>
          <p class="text-sm text-gray-500">${p.barcode ? p.barcode + ' • ' : ''} Stok: <span class="font-bold border px-2 py-0.5 rounded ${p.stock <= 0 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700'}">${p.stock}</span></p>
          ${p.note ? `<p class="text-sm italic text-gray-400 mt-1">📝 ${p.note}</p>` : ''}
        </div>
        <p class="font-black text-blue-700">Rp ${p.price.toLocaleString('id-ID')}</p>
      </div>
    `).join('') || '<p class="text-center text-gray-400">Belum ada barang.</p>';
  },

  openAddProduct() {
    document.getElementById('prod-id').value = '';
    document.getElementById('prod-name').value = '';
    document.getElementById('prod-barcode').value = '';
    document.getElementById('prod-price').value = '';
    document.getElementById('prod-stock').value = '';
    document.getElementById('prod-cat').value = 'Umum';
    document.getElementById('prod-note').value = '';
    document.getElementById('prod-modal-title').innerText = '📦 Tambah Barang Baru';
    document.getElementById('prod-del-btn').classList.add('hidden');
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
  },

  async editProduct(id) {
    const p = await db.products.get(id);
    document.getElementById('prod-id').value = p.id;
    document.getElementById('prod-name').value = p.name;
    document.getElementById('prod-barcode').value = p.barcode || '';
    document.getElementById('prod-price').value = p.price;
    document.getElementById('prod-stock').value = p.stock;
    document.getElementById('prod-cat').value = p.category;
    document.getElementById('prod-note').value = p.note || '';
    document.getElementById('prod-modal-title').innerText = '✏️ Edit Barang';
    document.getElementById('prod-del-btn').classList.remove('hidden');
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
  },

  closeAddProduct() {
    document.getElementById('product-modal').classList.add('hidden');
    document.getElementById('product-modal').classList.remove('flex');
  },

  async saveProduct() {
    const id = document.getElementById('prod-id').value;
    const data = {
      name: document.getElementById('prod-name').value,
      barcode: document.getElementById('prod-barcode').value,
      price: parseFloat(document.getElementById('prod-price').value) || 0,
      stock: parseInt(document.getElementById('prod-stock').value) || 0,
      category: document.getElementById('prod-cat').value,
      note: document.getElementById('prod-note').value
    };

    if(!data.name || data.price <= 0) return alert('Nama dan Harga wajib diisi!');

    if (id) await db.products.update(parseInt(id), data);
    else await db.products.add(data);

    this.closeAddProduct();
    this.loadProducts();
  },

  async deleteProduct() {
      const id = parseInt(document.getElementById('prod-id').value);
      if(confirm('Yakin hapus barang ini?')) {
          await db.products.delete(id);
          this.closeAddProduct();
          this.loadProducts();
      }
  },

  // ==========================
  // KASIR
  // ==========================
  async loadKasirProducts(search = '') {
    let products = await db.products.toArray();
    if (search) {
      const q = search.toLowerCase();
      products = products.filter(p => p.name.toLowerCase().includes(q) || (p.barcode && p.barcode.includes(q)));
    }
    
    const list = document.getElementById('kasir-product-list');
    list.innerHTML = products.map(p => `
      <div class="bg-white p-4 rounded-[16px] shadow-sm border ${p.stock <= 0 ? 'border-red-200' : 'border-transparent'}">
        <div class="flex justify-between items-start mb-2">
          <div>
            <h3 class="font-black text-lg">${p.name} <span class="bg-blue-100 text-blue-700 text-xs px-2 rounded">${p.category}</span></h3>
            ${p.note ? `<p class="text-sm text-gray-500 italic mt-1">${p.note}</p>` : ''}
          </div>
          <p class="text-sm font-bold ${p.stock <= 0 ? 'text-red-500' : 'text-green-600'}">Stok: ${p.stock}</p>
        </div>
        <button onclick="MainApp.addToCart(${p.id})" ${p.stock <= 0 ? 'disabled' : ''} class="w-full mt-2 font-black text-lg flex justify-between px-4 py-3 rounded-xl transition ${p.stock <= 0 ? 'bg-gray-100 text-gray-400' : 'bg-blue-50 text-blue-700 active:bg-blue-600 active:text-white'}">
           <span>+ Tambah</span>
           <span>Rp ${p.price.toLocaleString('id-ID')}</span>
        </button>
      </div>
    `).join('') || '<p class="text-center text-gray-400 mt-10">Pencarian tidak ditemukan.</p>';
  },

  filterKasirProducts(query) {
    this.loadKasirProducts(query);
  },

  async addToCart(id) {
    const p = await db.products.get(id);
    const existing = cart.find(x => x.id === id);
    if(existing) {
       if(existing.qty >= p.stock) return alert('Stok tidak cukup!');
       existing.qty++;
    } else {
       cart.push({...p, qty: 1});
    }
    this.updateCartUI();
  },

  updateCartQty(id, delta) {
    const idx = cart.findIndex(x => x.id === id);
    if(idx > -1) {
       cart[idx].qty += delta;
       if(cart[idx].qty <= 0) cart.splice(idx, 1);
    }
    this.updateCartUI();
  },

  clearCart() {
      cart = [];
      this.updateCartUI();
      this.toggleCartDrawer();
  },

  updateCartUI() {
    const total = cart.reduce((sum, x) => sum + (x.price * x.qty), 0);
    const badge = cart.reduce((sum, x) => sum + x.qty, 0);
    
    document.getElementById('cart-badge').innerText = badge;
    document.getElementById('cart-total').innerText = `Rp ${total.toLocaleString('id-ID')}`;
    document.getElementById('drawer-total').innerText = `Rp ${total.toLocaleString('id-ID')}`;
    
    const items = document.getElementById('cart-items');
    items.innerHTML = cart.map(x => `
      <div class="flex justify-between items-center border-b pb-3">
        <div class="flex-1">
          <p class="font-bold text-gray-800">${x.name}</p>
          <p class="text-sm text-gray-500">Rp ${x.price.toLocaleString('id-ID')}</p>
        </div>
        <div class="flex items-center gap-2">
          <button onclick="MainApp.updateCartQty(${x.id}, -1)" class="w-10 h-10 bg-red-100 text-red-600 rounded-full font-black text-xl">-</button>
          <span class="w-6 text-center font-black">${x.qty}</span>
          <button onclick="MainApp.updateCartQty(${x.id}, 1)" class="w-10 h-10 bg-green-100 text-green-700 rounded-full font-black text-xl">+</button>
        </div>
      </div>
    `).join('') || '<p class="text-center text-gray-400 py-10">Keranjang Kosi, eh kosong...</p>';
  },

  toggleCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    if(drawer.classList.contains('hidden')) {
      drawer.classList.remove('hidden');
      drawer.classList.add('flex');
    } else {
      drawer.classList.add('hidden');
      drawer.classList.remove('flex');
    }
  },

  openCheckoutModal() {
    if(cart.length === 0) return alert('Keranjang masih kosong!');
    const total = cart.reduce((sum, x) => sum + (x.price * x.qty), 0);
    
    document.getElementById('checkout-total').innerText = `Rp ${total.toLocaleString('id-ID')}`;
    document.getElementById('input-discount').value = '';
    document.getElementById('input-amount').value = '';
    document.getElementById('input-note').value = '';
    this.calculateChange();
    
    document.getElementById('checkout-modal').classList.remove('hidden');
    document.getElementById('checkout-modal').classList.add('flex');
  },

  closeCheckoutModal() {
    document.getElementById('checkout-modal').classList.add('hidden');
    document.getElementById('checkout-modal').classList.remove('flex');
  },

  calculateChange() {
    const d = parseFloat(document.getElementById('input-discount').value) || 0;
    const a = parseFloat(document.getElementById('input-amount').value) || 0;
    const total = cart.reduce((sum, x) => sum + (x.price * x.qty), 0) - d;
    
    let change = a - total;
    if(change < 0) change = 0;
    
    document.getElementById('checkout-change').innerText = `Rp ${change.toLocaleString('id-ID')}`;
  },

  setAmountPas() {
    const d = parseFloat(document.getElementById('input-discount').value) || 0;
    const total = cart.reduce((sum, x) => sum + (x.price * x.qty), 0) - d;
    document.getElementById('input-amount').value = total;
    this.calculateChange();
  },

  addAmountPaid(val) {
    const current = parseFloat(document.getElementById('input-amount').value) || 0;
    document.getElementById('input-amount').value = current + val;
    this.calculateChange();
  },

  async processPayment() {
    const discount = parseFloat(document.getElementById('input-discount').value) || 0;
    const paid = parseFloat(document.getElementById('input-amount').value) || 0;
    const subtotal = cart.reduce((sum, x) => sum + (x.price * x.qty), 0);
    const total = subtotal - discount;
    const note = document.getElementById('input-note').value;
    
    if(paid < total) return alert('Uang yang dibayar kurang dari total tagihan!');

    const change = paid - total;
    
    // Create transaction
    const trxId = await db.transactions.add({
      invoice_number: 'OFF-' + new Date().getTime(),
      created_at: new Date().toISOString(),
      payment_method: 'Cash',
      amount_paid: paid,
      change_amount: change,
      total_price: total
    });

    // Reduce stock and create details
    for(let item of cart) {
       await db.transaction_details.add({
         transaction_id: trxId,
         product_id: item.id,
         product_name: item.name
       });
       
       // Sederhana: Update stok
       const p = await db.products.get(item.id);
       if(p) {
          p.stock = p.stock - item.qty;
          await db.products.put(p);
       }
    }
    
    // Jika ada hutang logic (diskip untuk kesederhanaan, hanya cash)

    alert(`✅ Pembayaran Berhasil!\nTotal Belanja: Rp ${total}\nKembalian: Rp ${change}\nStruk siap dicetak (Offline).`);
    cart = [];
    this.updateCartUI();
    this.closeCheckoutModal();
    this.toggleCartDrawer();
  }
};

window.MainApp = MainApp;
MainApp.init();
