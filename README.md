# TOKO SUMBER BERKAH — Point of Sale & Inventory Management System

Aplikasi berbasis web terintegrasi kelas enterprise untuk operasional toko retail & grosir: mencakup manajemen pembelian (Purchasing), multi-gudang dan lokasi rak, kartu stok, sistem multi-satuan dinamis, penentuan harga pokok persediaan berbasis **FIFO (First In First Out)**, kasir pintar (Point of Sale), manajemen hutang/piutang (AR/AP), buku kas, beban operasional, serta laporan keuangan akuntansi (Laba Rugi).

---

## 🚀 Fitur Utama & Arsitektur Sistem

### 1. Sistem Multi-Satuan & Konversi Dinamis
* Setiap produk memiliki **Satuan Dasar** (misal: `PCS`, `KG`, `METER`, `BOTOL`).
* Mendukung multi-level satuan kemasan (misal: 1 PACK = 100 PCS, 1 DUS = 24 BOTOL, 1 SAK = 50 KG).
* Pengurangan stok terjadi pada satuan dasar tanpa perlu memecah kemasan fisik secara manual.
* **Format Tampilan Multi-Satuan:** Jika stok awal 10 PACK (1.000 PCS) dan terjual 25 PCS, sistem otomatis menampilkan sisa: **`9 PACK + 75 PCS`** (975 PCS).

### 2. Mesin Persediaan FIFO (First In First Out)
* Setiap transaksi barang masuk (Purchase Receiving / Opname / Initial Stock) otomatis membentuk **Batch Persediaan** baru dengan tanggal terima, kuantitas, harga modal dasar, dan tanggal kedaluwarsa.
* Penjualan kasir otomatis mengambil stok dari batch tertua yang masih aktif (`status = active`).
* Batch kedaluwarsa dilewati secara otomatis saat transaksi kasir.
* Perhitungan **HPP (Harga Pokok Penjualan / COGS)** dihitung akurat per batch yang dikonsumsi:
  * *Contoh:* Batch A (100 unit @ Rp 1.000) dan Batch B (100 unit @ Rp 1.200). Penjualan 150 unit menghasilkan HPP = `(100 x 1.000) + (50 x 1.200) = Rp 160.000`. Batch A status `depleted` (0 unit) dan Batch B sisa 50 unit.
* Fitur **Void Transaksi** mengembalikan kuantitas persis ke batch FIFO asal secara atomik dan memulihkan saldo piutang.
* Menjamin **Pencegahan Stok Negatif (No Negative Inventory)** dengan pesanan rollback otomatis jika stok habis.

### 3. Kasir Pintar (Smart Point of Sale)
* Antarmuka cepat & intuitif berbasis **Alpine.js & Tailwind CSS**.
* Pencarian produk instan berdasarkan Nama, SKU, atau **Scan Barcode**.
* Pemilihan satuan jual fleksibel per item keranjang (otomatis konversi harga dan stok).
* Opsi harga Retail vs. Grosir.
* Diskon per item dan diskon transaksi keseluruhan.
* Beragam metode pembayaran: **Tunai (Cash), Transfer Bank, QRIS, Kartu Debit, Uang Muka (DP), dan Tempo (Piutang)**.
* Struk thermal otomatis format 58mm / 80mm dengan barcode faktur dan tombol cetak langsung.
* Riwayat transaksi kasir dan pembatalan pesanan (Void).

### 4. Manajemen Gudang & Stok Opname
* Struktur hierarki gudang: Nama Gudang, Area, Kode Rak, Baris, dan Kolom.
* Ringkasan stok fisik & aset persediaan real-time.
* Kartu Stok (*Stock Card*) dengan jejak pergerakan masuk, keluar, dan mutasi.
* Notifikasi & filter otomatis untuk **Stok Minimum** dan **Produk Mendekati Kedaluwarsa**.
* Modul **Stok Opname** lengkap dengan hitungan selisih fisik vs sistem dan tombol penyesuaian (*Adjustment*) otomatis.
* Transaksi **Barang Keluar non-penjualan** (rusak, sampel, expired, internal use) lengkap dengan export PDF & CSV.

### 5. Modul Keuangan & Akuntansi (Finance & P&L)
* **Buku Piutang Pelanggan (Accounts Receivable):** Monitoring jatuh tempo, status bayar (`unpaid`, `partial`, `paid`, `overdue`), dan modal pembayaran cicilan/pelunasan.
* **Buku Hutang Supplier (Accounts Payable):** Pelacakan kewajiban toko ke mitra distributor dan pencatatan pembayaran faktur.
* **Buku Kas (Cash Flow):** Rekapitulasi mutasi kas masuk (*cash in*) dan kas keluar (*cash out*).
* **Beban Operasional:** Pencatatan biaya utilitas (listrik, air, internet, gaji, sewa, ATK) yang langsung terintegrasi dengan arus kas.
* **Laporan Laba Rugi Akuntansi (Comprehensive Income Statement):**
  * `Penjualan Bersih = Penjualan Kotor - Retur Penjualan`
  * `Laba Kotor (Gross Profit) = Penjualan Bersih - HPP FIFO`
  * `Laba Bersih (Net Profit) = Laba Kotor - Total Beban Operasional Toko`
  * Dilengkapi tombol cetak **PDF Resmi**.

### 6. Role-Based Access Control (RBAC) & Audit Trail
* Multi-user dengan 6 role: **Super Admin, Admin, Kasir, Staff Gudang, Finance, dan Owner**.
* Kasir dibatasi hanya untuk transaksi POS dan riwayat penjualan, tidak dapat mengakses pengaturan toko atau melihat harga modal.
* **Audit Trail / Activity Log:** Merekam setiap aktivitas login, pembuatan transaksi, pembatalan void, mutasi stok, perubahan harga, dan pengaturan sistem beserta timestamp, nama pengguna, dan IP address.

---

## 🛠️ Technology Stack

| Komponen | Teknologi |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.2+ / PHP 8.3+) |
| **Database** | SQLite (Default Zero-Config) / MySQL / MariaDB |
| **Authentication & RBAC** | Laravel Auth & `spatie/laravel-permission` v6.25 |
| **Document Export** | `barryvdh/laravel-dompdf` v3.1 & `maatwebsite/excel` v3.1 |
| **Frontend Framework** | Blade Templates & Tailwind CSS v4 |
| **Reactivity** | Alpine.js v3 |
| **Data Visualization** | Chart.js v4 |
| **Alerts & UI Feedback** | SweetAlert2 & Lucide Icons |

---

## 👥 Akun Demo & Kredensial Pengguna

Seluruh akun telah disiapkan pada seeder dengan kata sandi default: **`password`**.

| Role | Nama Pengguna | Username | Email | Hak Akses Utama |
|---|---|---|---|---|
| **Super Admin** | Super Administrator | `superadmin` | `superadmin@sumberberkah.com` | Akses penuh seluruh modul dan pengaturan |
| **Admin** | Administrator Toko | `admin` | `admin@sumberberkah.com` | Master data, gudang, pembelian, mitra, laporan |
| **Kasir** | Kasir Toko | `kasir` | `kasir@sumberberkah.com` | Kasir pintar POS, cetak struk, histori penjualan |
| **Staff Gudang** | Staff Gudang & Logistik | `gudang` | `gudang@sumberberkah.com` | Penerimaan PO, mutasi rak, barang keluar, opname |
| **Finance** | Bagian Keuangan | `finance` | `finance@sumberberkah.com` | Buku kas, piutang, hutang, beban operasional |
| **Owner** | Pemilik Toko | `owner` | `owner@sumberberkah.com` | Dashboard eksekutif dan seluruh laporan keuangan |

> *Catatan:* Pada halaman login (`/login`), tersedia tombol cepat (**Quick Demo Login**) untuk masuk dengan sekali klik tanpa perlu mengetik kredensial.

---

## 💻 Panduan Instalasi & Menjalankan Aplikasi

### Persyaratan Sistem
1. PHP 8.2 atau 8.3 (disarankan ekstensi: `pdo_sqlite`, `pdo_mysql`, `mbstring`, `openssl`, `gd`, `bcmath`).
2. Composer 2.x.
3. Node.js 18+ & NPM.

### Langkah 1: Clone / Siapkan Direktori
```bash
cd "c:\Users\ryanf\Sistem POS Toko Sumber Berkah"
```

### Langkah 2: Salin Environment
```bash
cp .env.example .env
php artisan key:generate
```

### Langkah 3: Migrasi Database & Jalankan Seeder
Aplikasi sudah terkonfigurasi dengan database SQLite mandiri (`database/database.sqlite`). Untuk migrasi dan pengisian data dummy:
```bash
php artisan migrate:fresh --seed
```
*Jika menggunakan MySQL (XAMPP):* Buat database `toko_sumber_berkah` di phpMyAdmin, sesuaikan `DB_CONNECTION=mysql` pada file `.env`, lalu jalankan perintah di atas.

### Langkah 4: Compile Asset Frontend (Tailwind & Alpine)
```bash
npm install
npm run build
```

### Langkah 5: Jalankan Server Lokal
```bash
php artisan serve
```
Buka browser pada alamat: **`http://localhost:8000`**

---

## 🧪 Pengujian Otomatis (Automated Acceptance Tests)

Aplikasi telah dilengkapi suite unit & feature test yang menguji 7 skenario operasional:

1. **Test 1: Multi-Unit Conversion:** Konversi 10 PACK (1.000 PCS) terjual 25 PCS -> sisa 975 PCS (`9 PACK + 75 PCS`).
2. **Test 2: FIFO Engine & COGS:** Deduct stok dari Batch tertua dan kalkulasi HPP aktual.
3. **Test 3: DP & Tempo Piutang:** Transaksi Rp 1.000.000, bayar DP Rp 400.000 -> sisa piutang Rp 600.000.
4. **Test 4: Goods Receipt:** Penerimaan PO 10 PACK x 100 PCS -> stok bertambah +1.000 PCS dan batch FIFO terbuat.
5. **Test 5: Void Reversal:** Pembatalan transaksi kasir mengembalikan persis kuantitas ke batch FIFO asal.
6. **Test 6: RBAC Protection:** Kasir dilarang mengakses `/settings` (HTTP 403 Forbidden).
7. **Test 7: No Negative Stock:** Upaya menjual melebihi stok yang tersedia ditolak dan transaksi di-rollback.

Jalankan pengujian:
```bash
php artisan test
```
*Hasil:* **`9 passed (45 assertions) — 100% Passed`**

---

## 📂 Struktur Direktori Utama

```
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php             # Login, Logout, Profil
│   │   ├── DashboardController.php        # 11 KPI Toko & Chart.js data
│   │   ├── Pos/PosController.php          # Smart POS, Struk, Void
│   │   ├── Master/                        # Master Produk, Kategori, Satuan, Gudang, Mitra
│   │   ├── Inventory/                     # Ringkasan, Kartu Stok, Opname, Kedaluwarsa
│   │   ├── Transaction/                   # Barang Masuk (PO) & Barang Keluar
│   │   ├── Finance/                       # Piutang, Hutang, Buku Kas, Beban Operasional
│   │   ├── Report/                        # Laporan Penjualan, Pembelian, Persediaan, P&L
│   │   └── System/                        # Manajemen User, Audit Logs, Settings
│   ├── Models/                            # 30 Model Eloquent terintegrasi
│   └── Services/
│       ├── UnitConversionService.php      # Engine multi-satuan & composite formatter
│       ├── FifoInventoryService.php       # Engine batch FIFO, lockForUpdate, & reverse
│       ├── PosSaleService.php             # Checkout kasir atomik & invoicing
│       ├── PurchaseService.php            # PO & penerimaan barang gudang
│       └── FinancialService.php           # Laba Rugi akuntansi & cash handling
├── database/
│   ├── migrations/                        # 12 file migrasi terstruktur
│   └── seeders/                           # Role, permission, dan master data toko
├── resources/views/
│   ├── layouts/                           # Layout master, accordion sidebar, navbar realtime
│   ├── pos/                               # Halaman kasir Alpine.js & struk thermal
│   ├── master/                            # Tampilan CRUD Master Data
│   ├── inventory/                         # Tampilan persediaan & stok opname
│   ├── transactions/                      # Tampilan PO barang masuk & keluar
│   ├── finance/                           # Tampilan piutang, hutang, buku kas, beban
│   ├── reports/                           # Tampilan laporan interaktif & template PDF
│   └── system/                            # Tampilan manajemen user, audit trail, settings
└── tests/Feature/
    └── PosAndInventoryTest.php            # Acceptance criteria test suite
```

---

## 📄 Lisensi

Dikembangkan secara khusus untuk operasional **TOKO SUMBER BERKAH**. Hak cipta dilindungi undang-undang.
