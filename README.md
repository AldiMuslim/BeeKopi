# BeeKopi - Sistem Manajemen Lapak & Operasional Kopi

BeeKopi adalah aplikasi Point of Sales (POS) dan Enterprise Resource Planning (ERP) terpadu untuk jaringan gerai kopi. Sistem ini mengintegrasikan seluruh alur operasional mulai dari rantai pasok bahan baku, pabrikasi racik batch minuman, alokasi pasokan ke armada lapak, hingga rekonsiliasi kas dan pelaporan laba rugi harian.

## Modul & Fitur Utama

### 1. Modul Sales (Armada Lapak)
- **Kasir POS Lapak**: Transaksi penjualan pesanan pelanggan (Tunai / QRIS), pengecekan stok otomatis, dan cetak struk nota belanja.
- **Penerimaan Stok**: Verifikasi pasokan cup yang dikirim dari Gudang Pusat dengan aksi Terima atau Tolak.
- **Laporan Penutupan Shift**: Rekonsiliasi kas omzet fisik dan sistem, pencatatan sisa fisik (opname), pelaporan cup rusak, uang makan penjaga, serta biaya operasional lapak.

### 2. Modul Produksi (Gudang Pusat & Roastery)
- **Racik Batch Kopi**: Produksi batch minuman terstandar dengan kalkulasi otomatis pemotongan stok bahan baku mentah (biji kopi, susu, sirup, bubuk, cup) berdasarkan resep formula.
- **Kelola Master Bahan Baku**: Manajemen persediaan bahan baku mentah, peringatan batas stok kritis, dan buku mutasi (Log Belanja Masuk & Log Pemakaian Keluar).
- **Formula Varian & Resep**: Pembuatan varian baru dengan kalkulasi HPP otomatis per cup.
- **Monitoring Restock**: Dashboard stok produk siap jual di pusat dan tiket alokasi aktif.

### 3. Modul Manajemen (Supervisi & Eksekutif)
- **Alokasi Distribusi Stok**: Penerbitan tiket pengiriman produk dari gudang pusat ke armada lapak cabang.
- **Verifikasi Laporan Shift**: Audit rekonsiliasi fisik vs kasir, evaluasi nota pengeluaran, dan persetujuan penutupan shift.
- **Analisis Penjualan & Jam Sibuk**: Wawasan produk terlaris (Top 5 Menu), jam sibuk (Peak Hours), dan komparasi performa antar lapak cabang.
- **Dashboard Laba Rugi**: Laporan laba rugi standar akuntansi (Omzet, Biaya Bahan Baku, Biaya Operasional, dan Net Profit) dengan opsi ekspor CSV dan cetak PDF.

### 4. Modul Administrator
- **Dashboard Eksekutif**: Tren omzet harian dan distribusi penjualan per cabang.
- **Kelola Pengguna**: Manajemen akun dan hak akses peran (Admin, Management, Produksi, Sales).
- **Kelola Armada Lapak**: Manajemen titik cabang gerai dan status operasional.
- **Kelola Katalog Produk**: Pengaturan harga jual, HPP, dan formula resep varian.
- **Audit Log Sistem**: Rekam jejak aktivitas operasional dan keamanan sistem.

## Akun Demo & Quick Switcher
Gunakan menu dropdown profil di sudut kanan atas untuk berganti peran dengan 1 klik:
- **Admin**: `admin@beekopi.com`
- **Management**: `management@beekopi.com`
- **Produksi**: `produksi@beekopi.com`
- **Sales Lapak A**: `salesa@beekopi.com`
- **Sales Lapak B**: `salesb@beekopi.com`

## Menjalankan Aplikasi
```bash
npm install
npm run dev
```
Aplikasi berjalan pada `http://localhost:3000`.
