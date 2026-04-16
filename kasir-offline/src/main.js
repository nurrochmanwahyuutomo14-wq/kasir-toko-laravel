import { db, seedInitialData, forceSyncMaster } from './db.js';

let cart = [];

const MainApp = {
  async init() {
    await seedInitialData();
    this.loadDashboard();
    this.scanner = null;
  },

  // ==========================
  // DASHBOARD
  // ==========================
  async loadDashboard() {
    const today = new Date().toISOString().split('T')[0];
    const transactions = await db.transactions.toArray();
    
    // Filter hari ini
    const todayTrx = transactions.filter(t => t.created_at.startsWith(today));
    
    const omzet = todayTrx.reduce((sum, t) => sum + (t.amount_paid - (t.change_amount || 0)), 0);
    const trxCount = todayTrx.length;
    
    let itemsOut = 0;
    for(let t of todayTrx) {
      const details = await db.transaction_details.where({transaction_id: t.id}).toArray();
      itemsOut += details.length;
    }

    document.getElementById('dash-omzet').innerText = `Rp ${omzet.toLocaleString('id-ID')}`;
    document.getElementById('dash-trx').innerText = `${trxCount} Nota`;
    document.getElementById('dash-items').innerText = `${itemsOut} Item`;

    // Net Profit logic
    const expenses = await db.expenses.toArray();
    const todayExpenses = expenses.filter(e => e.date === today);
    const totalExpToday = todayExpenses.reduce((sum, e) => sum + e.amount, 0);
    
    const netProfit = omzet - totalExpToday;
    document.getElementById('current-date').innerText = `Laba Bersih Hari Ini: Rp ${netProfit.toLocaleString('id-ID')}`;
  },

  // ==========================
  // PENGELUARAN (EXPENSES)
  // ==========================
  async loadExpenses() {
    const today = new Date().toISOString().split('T')[0];
    const expenses = await db.expenses.toArray();
    expenses.sort((a,b) => b.id - a.id);

    const todayExp = expenses.filter(e => e.date === today);
    const totalToday = todayExp.reduce((sum, e) => sum + e.amount, 0);
    document.getElementById('expense-total-today').innerText = `Rp ${totalToday.toLocaleString('id-ID')}`;

    const list = document.getElementById('expense-list');
    list.innerHTML = expenses.map(e => `
      <div class="bg-white p-5 rounded-[20px] shadow-sm border border-gray-100 flex justify-between items-center">
        <div>
           <h3 class="font-black text-lg text-gray-800">${e.name}</h3>
           <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">${e.date} ${e.note ? '• ' + e.note : ''}</p>
        </div>
        <div class="text-right">
           <p class="font-black text-xl text-red-600">Rp ${e.amount.toLocaleString('id-ID')}</p>
        </div>
      </div>
    `).join('') || '<p class="text-center text-gray-400 mt-10 font-bold">Belum ada pengeluaran.</p>';
  },

  openAddExpense() {
    document.getElementById('exp-name').value = '';
    document.getElementById('exp-amount').value = '';
    document.getElementById('exp-note').value = '';
    document.getElementById('expense-modal').classList.remove('hidden');
    document.getElementById('expense-modal').classList.add('flex');
  },

  closeAddExpense() {
    document.getElementById('expense-modal').classList.add('hidden');
    document.getElementById('expense-modal').classList.remove('flex');
  },

  async saveExpense() {
    const name = document.getElementById('exp-name').value;
    const amount = parseFloat(document.getElementById('exp-amount').value) || 0;
    const note = document.getElementById('exp-note').value;
    const date = new Date().toISOString().split('T')[0];

    if(!name || amount <= 0) return alert('Nama dan Nominal wajib diisi!');

    await db.expenses.add({ name, amount, note, date });
    this.closeAddExpense();
    this.loadExpenses();
    this.loadDashboard();
  },

  // ==========================
  // RIWAYAT TRANSAKSI
  // ==========================
  async loadRiwayat() {
    const transactions = await db.transactions.toArray();
    transactions.sort((a,b) => b.id - a.id);

    document.getElementById('riwayat-total-count').innerText = `${transactions.length} Nota`;

    const list = document.getElementById('riwayat-list');
    list.innerHTML = transactions.map(t => {
      const date = new Date(t.created_at);
      const formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
      return `
      <div class="bg-white p-5 rounded-[20px] shadow-sm border border-gray-100 flex justify-between items-center active:scale-95 transition-all cursor-pointer hover:border-blue-200" onclick="MainApp.viewRiwayatDetails(${t.id})">
        <div>
           <div class="flex items-center gap-2 mb-1">
             <h3 class="font-black text-lg text-gray-800">${t.invoice_number}</h3>
             <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-widest">${t.payment_method}</span>
           </div>
           <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">${formattedDate}</p>
        </div>
        <div class="text-right">
           <p class="font-black text-xl text-blue-600">Rp ${t.total_price.toLocaleString('id-ID')}</p>
        </div>
      </div>
      `;
    }).join('') || '<p class="text-center text-gray-400 mt-10 font-bold">Belum ada transaksi offline.</p>';
  },

  async viewRiwayatDetails(id) {
    const trx = await db.transactions.get(id);
    if (!trx) return;

    const date = new Date(trx.created_at);
    document.getElementById('rdm-invoice-number').innerText = trx.invoice_number;
    document.getElementById('rdm-waktu').innerText = date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    document.getElementById('rdm-total').innerText = `Rp ${trx.total_price.toLocaleString('id-ID')}`;
    document.getElementById('rdm-change').innerText = `Rp ${(trx.change_amount || 0).toLocaleString('id-ID')}`;

    const details = await db.transaction_details.where({transaction_id: id}).toArray();
    const list = document.getElementById('rdm-items');
    list.innerHTML = details.map(d => `
      <div class="border border-gray-100 rounded-[16px] p-4 bg-gray-50 flex justify-between items-center shadow-sm">
         <p class="font-black text-gray-800">${d.product_name}</p>
         <p class="text-xs font-bold text-gray-500">${d.qty} x Rp ${d.price ? d.price.toLocaleString('id-ID') : '?'}</p>
      </div>
    `).join('') || '<p class="text-sm text-gray-500">Tidak ada rincian tercatat.</p>';

    let printBtn = document.getElementById('rdm-print-btn');
    if(!printBtn) {
        printBtn = document.createElement('button');
        printBtn.id = 'rdm-print-btn';
        printBtn.className = "w-full mt-4 bg-blue-600 text-white font-black py-4 rounded-[16px] shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2";
        printBtn.innerHTML = "🖨️ CETAK NOTA";
        document.getElementById('rdm-items').parentNode.appendChild(printBtn);
    }
    printBtn.onclick = () => this.printReceipt(id);

    document.getElementById('riwayat-detail-modal').classList.remove('hidden');
    document.getElementById('riwayat-detail-modal').classList.add('flex');
  },

  closeRiwayatDetails() {
    document.getElementById('riwayat-detail-modal').classList.add('hidden');
    document.getElementById('riwayat-detail-modal').classList.remove('flex');
  },

  async printReceipt(id) {
    const t = await db.transactions.get(id);
    const details = await db.transaction_details.where({transaction_id: id}).toArray();
    const date = new Date(t.created_at).toLocaleString('id-ID');
    
    // Format struk sebagai teks sederhana (kompatibel dengan Android)
    let receipt = "================================\n";
    receipt += "         TOKOKU KASIR\n";
    receipt += "================================\n";
    receipt += `Nota   : ${t.invoice_number}\n`;
    receipt += `Waktu  : ${date}\n`;
    receipt += "--------------------------------\n";
    
    details.forEach(d => {
      const qty = d.qty || 1;
      const price = d.price || 0;
      const subtotal = qty * price;
      receipt += `${d.product_name}\n`;
      receipt += `  ${qty} x Rp ${price.toLocaleString('id-ID')} = Rp ${subtotal.toLocaleString('id-ID')}\n`;
    });
    
    receipt += "--------------------------------\n";
    receipt += `TOTAL   : Rp ${t.total_price.toLocaleString('id-ID')}\n`;
    receipt += `BAYAR   : Rp ${t.amount_paid.toLocaleString('id-ID')}\n`;
    receipt += `KEMBALI : Rp ${t.change_amount.toLocaleString('id-ID')}\n`;
    receipt += "================================\n";
    receipt += "        Terima Kasih!\n";
    receipt += "================================\n";

    // Gunakan Web Share API (bisa share via WhatsApp, Bluetooth, dll.)
    if (navigator.share) {
      try {
        await navigator.share({
          title: `Nota ${t.invoice_number}`,
          text: receipt
        });
      } catch (err) {
        if (err.name !== 'AbortError') {
          // Fallback: tampilkan di alert
          alert(receipt);
        }
      }
    } else {
      // Fallback: tampilkan di alert untuk bisa copy-paste
      alert(receipt);
    }
  },

  async exportToCSV() {
    const trxs = await db.transactions.toArray();
    trxs.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));
    
    const BOM = '\uFEFF'; // BOM untuk encoding UTF-8 agar terbaca di Excel
    let csv = BOM + 'ID,Tanggal,Jam,Invoice,Total,Bayar,Kembali,Metode\n';
    trxs.forEach(t => {
      const d = new Date(t.created_at);
      const tgl = d.toLocaleDateString('id-ID');
      const jam = d.toLocaleTimeString('id-ID');
      csv += `${t.id},"${tgl}","${jam}","${t.invoice_number}",${t.total_price},${t.amount_paid || 0},${t.change_amount || 0},${t.payment_method}\n`;
    });

    // Gunakan Data URI - kompatibel dengan Android WebView
    const dataUri = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    
    // Coba Web Share API dulu (Android native share sheet)
    if (navigator.share) {
      try {
        // Share sebagai teks (karena file share butuh async blob)
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const file = new File([blob], `Laporan_Kasir_${new Date().toISOString().split('T')[0]}.csv`, { type: 'text/csv' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
          await navigator.share({ files: [file], title: 'Laporan Kasir Offline' });
          return;
        }
      } catch (err) {
        if (err.name === 'AbortError') return;
        // Jika gagal share file, fallback ke link download
      }
    }

    // Fallback: Buat link download dengan data URI
    try {
      const link = document.createElement('a');
      link.href = dataUri;
      link.download = `Laporan_Kasir_${new Date().toISOString().split('T')[0]}.csv`;
      link.style.display = 'none';
      document.body.appendChild(link);
      link.click();
      setTimeout(() => document.body.removeChild(link), 1000);
    } catch(e) {
      alert(`Rekap tidak bisa diunduh otomatis di perangkat ini.\n\nTotalnya: ${trxs.length} transaksi.`);
    }
  },

  // ==========================
  // SCANNER LOGIC (Android-Compatible)
  // ==========================
  async startScanner(target) {
     // Cek dan minta izin kamera secara eksplisit dulu
     try {
       const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
       stream.getTracks().forEach(t => t.stop()); // Hentikan stream percobaan
     } catch(permErr) {
       alert('Akses kamera ditolak. Harap izinkan kamera di Pengaturan Aplikasi Android Anda, lalu buka ulang aplikasinya.');
       return;
     }

     document.getElementById('scanner-container').style.display = 'flex';
     
     if (this.scanner) {
       try { await this.scanner.stop(); } catch(e) {}
     }
     
     this.scanner = new Html5Qrcode('reader');
     const config = { 
       fps: 10, 
       qrbox: { width: 250, height: 250 },
       aspectRatio: 1.0,
       showTorchButtonIfSupported: true
     };
     
     this.scanner.start(
       { facingMode: 'environment' }, 
       config, 
       (decodedText) => {
          this.stopScanner();
          if(target === 'kasir') {
             // Isi otomatis ke search dan filter
             const searchInput = document.getElementById('kasir-search');
             if(searchInput) searchInput.value = decodedText;
             this.loadKasirProducts(decodedText);
          } else if(target === 'product') {
             const barcodeInput = document.getElementById('prod-barcode');
             if(barcodeInput) barcodeInput.value = decodedText;
          }
       },
       (errorMessage) => { /* abaikan error frame */ }
     ).catch(err => {
        alert('Gagal membuka kamera: ' + err);
        document.getElementById('scanner-container').style.display = 'none';
     });
  },

  async stopScanner() {
     if(this.scanner) {
        try {
          await this.scanner.stop();
          await this.scanner.clear();
        } catch(e) {}
        this.scanner = null;
     }
     document.getElementById('scanner-container').style.display = 'none';
  },

  async syncMasterData() {
    if(confirm('Sinkronisasi akan menghapus data stok lokal Anda dan menggantinya dengan data master dari server. Lanjutkan?')) {
        const success = await forceSyncMaster();
        if(success) {
            alert('✅ Sinkronisasi master data berhasil!');
            this.loadProducts();
            this.loadKasirProducts();
        } else {
            alert('❌ Gagal sinkronisasi. Pastikan file master data tersedia.');
        }
    }
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
  // BUKU BON (HUTANG)
  // ==========================
  async loadDebts(status = 'belum') {
    if(status === 'belum') {
        document.getElementById('tab-bon-belum').classList.remove('text-gray-500', 'bg-transparent', 'border-transparent');
        document.getElementById('tab-bon-belum').classList.add('bg-white', 'text-orange-600', 'shadow-sm');
        
        document.getElementById('tab-bon-lunas').classList.remove('bg-white', 'text-orange-600', 'shadow-sm');
        document.getElementById('tab-bon-lunas').classList.add('text-gray-500', 'bg-transparent', 'border-transparent');
    } else {
        document.getElementById('tab-bon-lunas').classList.remove('text-gray-500', 'bg-transparent', 'border-transparent');
        document.getElementById('tab-bon-lunas').classList.add('bg-white', 'text-orange-600', 'shadow-sm');
        
        document.getElementById('tab-bon-belum').classList.remove('bg-white', 'text-orange-600', 'shadow-sm');
        document.getElementById('tab-bon-belum').classList.add('text-gray-500', 'bg-transparent', 'border-transparent');
    }

    const debts = await db.debts.where({status: status}).toArray();
    debts.sort((a,b) => b.id - a.id);

    const allBelum = await db.debts.where({status: 'belum'}).toArray();
    const totalPiutang = allBelum.reduce((sum, d) => sum + (d.total_hutang - d.sudah_dibayar), 0);
    document.getElementById('bon-total-piutang').innerText = `Rp ${totalPiutang.toLocaleString('id-ID')}`;

    const list = document.getElementById('debt-list');
    list.innerHTML = debts.map(d => `
      <div class="bg-white p-5 rounded-[20px] shadow-sm border border-gray-100 flex justify-between items-center active:scale-95 transition-all cursor-pointer" onclick="MainApp.viewDebtDetails(${d.id})">
        <div>
           <div class="flex items-center gap-2 mb-1">
             <h3 class="font-black text-lg text-gray-800">${d.nama_pengutang}</h3>
             ${d.status === 'lunas' ? '<span class="bg-green-100 text-green-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-widest">Lunas</span>' : ''}
           </div>
           <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">${d.tanggal_terakhir_bon}</p>
        </div>
        <div class="text-right">
           <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Sisa Hutang</p>
           <p class="font-black text-xl text-orange-600">Rp ${(d.total_hutang - d.sudah_dibayar).toLocaleString('id-ID')}</p>
        </div>
      </div>
    `).join('') || '<p class="text-center text-gray-400 mt-10 font-bold">Tidak ada data.</p>';
  },

  openAddDebt() {
    document.getElementById('bon-customer').value = '';
    document.getElementById('bon-product-name').value = '';
    document.getElementById('bon-price').value = '';
    document.getElementById('bon-qty').value = '1';
    document.getElementById('add-bon-modal').classList.remove('hidden');
    document.getElementById('add-bon-modal').classList.add('flex');
  },

  closeAddDebt() {
    document.getElementById('add-bon-modal').classList.add('hidden');
    document.getElementById('add-bon-modal').classList.remove('flex');
  },

  async saveDebt() {
    const customer = document.getElementById('bon-customer').value;
    const productName = document.getElementById('bon-product-name').value;
    const price = parseFloat(document.getElementById('bon-price').value) || 0;
    const qty = parseInt(document.getElementById('bon-qty').value) || 1;

    if(!customer || !productName || price <= 0) return alert('Nama, Barang, dan Harga wajib diisi!');

    const totalHarga = price * qty;
    const today = new Date().toISOString().split('T')[0];

    let debt = (await db.debts.toArray()).find(d => d.nama_pengutang.toLowerCase() === customer.toLowerCase() && d.status === 'belum');

    let debtId;
    if(debt) {
       debt.total_hutang += totalHarga;
       debt.tanggal_terakhir_bon = today;
       await db.debts.put(debt);
       debtId = debt.id;
    } else {
       debtId = await db.debts.add({
          nama_pengutang: customer,
          total_hutang: totalHarga,
          sudah_dibayar: 0,
          status: 'belum',
          tanggal_terakhir_bon: today
       });
    }

    await db.debt_details.add({
       debt_id: debtId,
       nama_produk: productName,
       jumlah: qty,
       harga_satuan: price,
       total_harga: totalHarga,
       tanggal_bon: today,
       is_lunas: 0
    });

    this.closeAddDebt();
    this.loadDebts();
  },

  async viewDebtDetails(id) {
    const debt = await db.debts.get(id);
    if (!debt) return;
    
    document.getElementById('dbm-customer-name').innerText = debt.nama_pengutang;
    document.getElementById('dbm-total').innerText = `Rp ${debt.total_hutang.toLocaleString('id-ID')}`;
    document.getElementById('dbm-paid').innerText = `Rp ${debt.sudah_dibayar.toLocaleString('id-ID')}`;
    document.getElementById('dbm-sisa').innerText = `Rp ${(debt.total_hutang - debt.sudah_dibayar).toLocaleString('id-ID')}`;
    
    if(debt.status === 'lunas') {
        document.getElementById('dbm-actions').classList.add('hidden');
        document.getElementById('dbm-actions').classList.remove('flex');
    } else {
        document.getElementById('dbm-actions').classList.remove('hidden');
        document.getElementById('dbm-actions').classList.add('flex');
    }

    const details = await db.debt_details.where({debt_id: id}).toArray();
    const list = document.getElementById('dbm-items');
    list.innerHTML = details.map(d => `
      <div class="border border-gray-100 rounded-[20px] p-4 bg-gray-50 flex justify-between items-center shadow-sm">
         <div>
            <p class="font-black text-gray-800">${d.nama_produk}</p>
            <p class="text-xs font-bold text-gray-500">${d.jumlah} x Rp ${d.harga_satuan.toLocaleString('id-ID')}</p>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">${d.tanggal_bon}</p>
         </div>
         <p class="font-black text-blue-700">Rp ${d.total_harga.toLocaleString('id-ID')}</p>
      </div>
    `).join('');

    this.activeDebtId = id;
    document.getElementById('detail-bon-modal').classList.remove('hidden');
    document.getElementById('detail-bon-modal').classList.add('flex');
  },

  closeDebtDetails() {
    document.getElementById('detail-bon-modal').classList.add('hidden');
    document.getElementById('detail-bon-modal').classList.remove('flex');
  },

  openPayDebtModal() {
     this.closeDebtDetails();
     document.getElementById('ppm-amount').value = '';
     document.getElementById('pay-partial-modal').classList.remove('hidden');
     document.getElementById('pay-partial-modal').classList.add('flex');
     this.updatePpmSisa();
  },

  async updatePpmSisa() {
     const debt = await db.debts.get(this.activeDebtId);
     const sisa = debt.total_hutang - debt.sudah_dibayar;
     document.getElementById('ppm-sisa').innerText = `Rp ${sisa.toLocaleString('id-ID')}`;
  },

  async processPayDebtPartial() {
     const amount = parseFloat(document.getElementById('ppm-amount').value) || 0;
     if(amount <= 0) return alert('Masukkan nominal yang valid');
     
     const debt = await db.debts.get(this.activeDebtId);
     const sisa = debt.total_hutang - debt.sudah_dibayar;
     const byr = Math.min(amount, sisa);

     debt.sudah_dibayar += byr;
     
     if (debt.sudah_dibayar >= debt.total_hutang) {
         debt.status = 'lunas';
     }
     
     await db.debts.put(debt);
     
     document.getElementById('pay-partial-modal').classList.add('hidden');
     document.getElementById('pay-partial-modal').classList.remove('flex');
     alert(`✅ Pembayaran Rp ${byr.toLocaleString('id-ID')} berhasil dicatat.`);
     this.loadDebts();
  },

  async payDebtAll() {
     if(confirm('Yakin ingin melunasi seluruh hutang ini?')) {
        const debt = await db.debts.get(this.activeDebtId);
        debt.sudah_dibayar = debt.total_hutang;
        debt.status = 'lunas';
        await db.debts.put(debt);
        this.closeDebtDetails();
        alert('Telah Lunas!');
        this.loadDebts();
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
    
    const trxId = await db.transactions.add({
      invoice_number: 'OFF-' + new Date().getTime(),
      created_at: new Date().toISOString(),
      payment_method: 'Cash',
      amount_paid: paid,
      change_amount: change,
      total_price: total
    });

    for(let item of cart) {
       await db.transaction_details.add({
         transaction_id: trxId,
         product_id: item.id,
         product_name: item.name,
         qty: item.qty,
         price: item.price
       });
       
       const p = await db.products.get(item.id);
       if(p) {
          p.stock = p.stock - item.qty;
          await db.products.put(p);
       }
    }
    
    if(confirm(`✅ Pembayaran Berhasil!\nTotal Belanja: Rp ${total}\nKembalian: Rp ${change}\n\nIngin cetak struk?`)) {
       this.printReceipt(trxId);
    }
    cart = [];
    this.updateCartUI();
    this.closeCheckoutModal();
    this.toggleCartDrawer();
  }
};

window.MainApp = MainApp;
MainApp.init();
