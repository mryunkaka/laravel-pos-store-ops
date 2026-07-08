# 02 - Progres

## Status Instalasi

- [x] Kode sumber `fajarghifar/laravel-point-of-sale` sudah di-clone ke `D:\Project\Web\pos3`.
- [x] Composer lokal tersedia sebagai `composer.phar`.
- [x] Dependency PHP sudah di-install memakai `C:\php\php.exe`.
- [x] Dependency Node sudah di-install.
- [x] Aset frontend sudah di-build dengan `npm run build`.
- [x] `.env` sudah dibuat dari `.env.example`.
- [x] `APP_KEY` sudah dibuat.
- [x] Database MariaDB `point_of_sale` sudah dibuat di port `3307`.
- [x] Migration dan seeder sudah dijalankan.
- [x] Storage link sudah dibuat.
- [x] Laravel server berjalan di `http://127.0.0.1:8084`.

## Status Analisis Fitur

- [x] Route utama sudah dicek di `routes/web.php`.
- [x] Sidebar/menu sudah dicek di `resources/views/dashboard/body/sidebar.blade.php`.
- [x] Alur POS sudah dicek di `PosController`.
- [x] Alur order sudah dicek di `OrderController`.
- [x] Alur produk sudah dicek di `ProductController`.
- [x] Ringkasan dashboard sudah dicek di `DashboardController`.

## Phase 2 - Selesai (2026-06-17)

### Validasi Stok
- [x] Validasi stok saat add product ke cart (`PosController@addCart`).
- [x] Validasi stok saat update qty cart (`PosController@updateCart`).
- [x] Validasi stok ulang saat order dibuat (`OrderController@storeOrder`).
- [x] Validasi stok ulang saat order di-complete (`OrderController@updateStatus`).
- [x] Permission `allow-negative-stock` untuk melewati validasi stok.

### Cancel & Void
- [x] Migration: kolom `cancel_reason`, `void_reason`, `voided_by`, `voided_at`, `cancelled_by`, `cancelled_at` di tabel `orders`.
- [x] Status order baru: `cancelled`, `void` (selain `pending`, `complete`).
- [x] Cancel pending order dengan alasan wajib (modal input).
- [x] Void complete order dengan alasan + permission `void.order` + stok dikembalikan.
- [x] Void hanya mengembalikan stok 1x.

### Perbaikan Dashboard
- [x] `total_paid` dan `total_due` hanya hitung order `complete`.
- [x] `today_sales` hanya hitung order `complete`.
- [x] Produk terlaris hanya hitung order `complete`.
- [x] Grafik bulanan hanya hitung order `complete`.

### Audit Log
- [x] Tabel `audit_logs` dengan kolom: user_id, module, action, reference_type, reference_id, old_values, new_values, ip_address, user_agent, description.
- [x] Model `AuditLog` dan `AuditService::log()`.
- [x] Audit tercatat untuk: login, logout, create order, complete order, cancel order, void order, update due, update product.

### Permission Baru
- `void.order` - diberikan ke SuperAdmin dan Manager.
- `allow-negative-stock` - diberikan ke SuperAdmin.
- `audit.menu` - disiapkan untuk penampil audit log (Phase 7).

### File Utama yang Diubah/Ditambah
- `database/migrations/2026_06_17_100000_create_audit_logs_table.php`
- `database/migrations/2026_06_17_100001_add_cancel_void_fields_to_orders_table.php`
- `database/seeders/Phase2PermissionSeeder.php`
- `app/Models/AuditLog.php`
- `app/Models/Order.php` (tambah kolom, helper method)
- `app/Services/AuditService.php`
- `app/Http/Controllers/Dashboard/OrderController.php` (validasi stok, cancel, void)
- `app/Http/Controllers/Dashboard/PosController.php` (validasi stok)
- `app/Http/Controllers/Dashboard/DashboardController.php` (fix query)
- `app/Http/Controllers/Dashboard/ProductController.php` (audit log)
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (audit log)
- `routes/web.php` (route cancel, void)
- `resources/views/orders/details-order.blade.php` (modal cancel/void, status display)
- `resources/views/orders/pending-orders.blade.php` (badge, error alert)
- `resources/views/orders/complete-orders.blade.php` (badge, error alert)
- `resources/views/pos/index.blade.php` (error alert)

## Phase 8 - Selesai (2026-06-17)

### Migrasi Bahasa Tampilan ke Indonesia

- [x] Semua 85 file Blade view telah diterjemahkan ke bahasa Indonesia.
- [x] Sidebar: menu dan submenu (Dashboard, Order, Produk, Karyawan, Pelanggan, Pemasok, Gaji, Absensi, Pengguna, Bantuan).
- [x] Navbar: placeholder search, profil, keluar.
- [x] Dashboard: judul, label kartu metrik, tabel, chart (label 'Penjualan', bulan dalam bahasa Indonesia).
- [x] POS: label, placeholder, pesan, cart sidebar, struk, faktur.
- [x] Orders: pending, complete, detail, invoice, struk, piutang — semua diterjemahkan.
- [x] Products: index, create, edit, show, import — semua diterjemahkan.
- [x] Categories: index, create, edit — semua diterjemahkan.
- [x] Customers: index, create, edit, show — semua diterjemahkan.
- [x] Suppliers: index, create, edit, show — semua diterjemahkan.
- [x] Employees: index, create, edit, show — semua diterjemahkan.
- [x] Attendance: index, create, edit — semua diterjemahkan.
- [x] Salary: advance-salary (index, create, edit), pay-salary (index, create, create_single, pay-all, history, history-details) — semua diterjemahkan.
- [x] Roles: permission (create, edit, index), role (create, edit, index), role-permission (create, edit, index) — 9 file diterjemahkan.
- [x] Users: index, create, edit — semua diterjemahkan.
- [x] Auth: login, register, forgot-password, reset-password, confirm-password, verify-email — 6 file diterjemahkan.
- [x] Profile: partials (change-password-form, delete-account-form, edit-profile-form, left-profile, navbar-profile, show-profile) — 6 file diterjemahkan.
- [x] Database backup, help, errors (403, 404), welcome — semua diterjemahkan.
- [x] Footer: Kebijakan Privasi, Syarat Penggunaan.
- [x] Istilah teknis tetap dalam bahasa Inggris: POS, Role, Permission, Email, Username, Password, Barcode, QRIS, Excel, PDF.

### File Utama yang Diubah
- 85 file Blade view di `resources/views/` — semua modul diterjemahkan.
- Tidak ada perubahan pada controller, model, migration, atau route.

## Phase 3 - Inti Inventaris (2026-06-19)

### Migration yang Dibuat/Dijalankan
- `2026_06_18_235822_create_stock_movements_table` - Tabel pergerakan stok.
- `2026_06_19_000818_create_purchase_orders_table` - Tabel order pembelian.
- `2026_06_19_001741_create_purchase_order_details_table` - Detail order pembelian.
- `2026_06_19_001742_create_purchase_receivings_table` - Tabel penerimaan barang.
- `2026_06_19_002156_create_purchase_receiving_details_table` - Detail penerimaan barang.
- `2026_06_19_002245_create_stock_adjustments_table` - Tabel penyesuaian stok.
- `2026_06_19_002323_create_stock_movement_details_table` - Detail pergerakan stok.
- `2026_06_19_010621_create_purchase_returns_table` - Tabel retur pembelian.
- `2026_06_19_010623_create_purchase_return_details_table` - Detail retur pembelian.
- `2026_06_19_091500_add_stock_snapshots_to_stock_adjustments_table` - Snapshot stok lama/baru untuk penyesuaian stok.
- `2026_06_19_120000_create_stock_locations_table` - Struktur awal lokasi stok.
- `2026_06_19_120001_create_stock_transfers_table` - Tabel transfer stok.
- `2026_06_19_120002_create_stock_transfer_details_table` - Detail transfer stok.

### Model yang Dibuat
- `StockMovement` - Catat perubahan stok.
- `StockAdjustment` - Catat penyesuaian stok manual.
- `PurchaseOrder` - Manajemen order pembelian.
- `PurchaseOrderDetail` - Detail item order pembelian.
- `PurchaseReceiving` - Catat penerimaan barang.
- `PurchaseReceivingDetail` - Detail penerimaan barang.
- `PurchaseReturn` - Catat retur pembelian.
- `PurchaseReturnDetail` - Detail retur pembelian.
- `StockLocation` - Lokasi stok awal.
- `StockTransfer` - Header transfer stok.
- `StockTransferDetail` - Detail item transfer stok.

### Fitur yang Diimplementasikan
- [x] Catat stok keluar otomatis saat order selesai.
- [x] Catat stok masuk saat penerimaan barang diselesaikan.
- [x] Penyesuaian stok manual dengan alasan dan snapshot stok lama/baru.
- [x] Riwayat pergerakan stok dengan filter produk, tipe, dan tanggal.
- [x] Order pembelian dari pemasok.
- [x] Penerimaan barang dibuat sebagai `pending`, stok baru bertambah saat diselesaikan.
- [x] Retur pembelian dibuat sebagai `pending`, stok baru berkurang saat diselesaikan.
- [x] Transfer stok antar lokasi dibuat sebagai struktur awal dengan lokasi `Toko Utama` dan `Gudang Toko`.
- [x] Halaman index/create/show untuk order pembelian, penerimaan barang, retur pembelian, dan penyesuaian stok.

### Routes yang Ditambahkan/Diperbaiki
- `/stock-movements`
- `/stock-movements/history/{product}`
- `/stock-movements/adjust/{product}`
- `/stock-adjustments`
- `/stock-transfers`
- `/purchase-orders`
- `/purchase-orders/{purchaseOrder}/cancel`
- `/purchase-receivings`
- `/purchase-receivings/{receiving}/complete`
- `/purchase-returns`
- `/purchase-returns/{return}/complete`

### Validasi yang Dijalankan
- `php -l` untuk controller/model inventaris yang diubah.
- `php artisan route:list --name=purchase`
- `php artisan route:list --name=stock`
- `php artisan route:list`
- `php artisan view:cache`
- `php artisan migrate`
- `php artisan migrate:status`

### Catatan
|- Phase 3 sudah clear berdasarkan checklist `03-TODO.md`.
|- Item aktif berikutnya adalah Phase 4: modul shift kasir dan tutup kasir.
|- Penampil audit log tetap direncanakan untuk Phase 7.

## Phase 4 - Operasional Kasir dan Tutup Kasir (2026-06-19)

### Migration yang Dibuat/Dijalankan
|- `2026_06_19_061809_create_cash_shifts_table` - Tabel cash shift kasir.
|- `2026_06_19_061810_create_cash_shift_details_table` - Detail transaksi per shift.
|- `2026_06_19_061810_create_cash_closings_table` - Tabel tutup kasir harian.
|- `2026_06_19_061811_create_cash_closing_details_table` - Detail per shift dalam closing.

### Model yang Dibuat
|- `CashShift` - Manajemen shift kasir.
|- `CashShiftDetail` - Detail transaksi per shift.
|- `CashClosing` - Tutup kasir harian outlet.
|- `CashClosingDetail` - Detail per shift dalam closing.

### Fitur yang Diimplementasikan
|- [x] Buat modul shift kasir dengan kas awal.
|- [x] Catat transaksi (sale, refund, void, cash_in, cash_out) per shift.
|- [x] Tutup shift dengan kas fisik dan selisih.
|- [x] Buat tutup kasir harian dari kumpulan shift.
|- [x] Verifikasi tutup kasir oleh supervisor.
|- [x] Multi pembayaran (tunai, QRIS, debit, transfer, e-wallet) tersedia di struktur.
|- [x] Validasi shift aktif di PosController (kasir harus buka shift sebelum transaksi).
|- [x] Kunci transaksi yang sudah masuk tutup kasir (cancel, void, bayar piutang).
|- [x] Cetak laporan shift kasir.
|- [x] Cetak laporan tutup kasir harian.

### Routes yang Ditambahkan
|- `/cash-shifts` - Daftar shift kasir.
|- `/cash-shifts/create` - Form buka shift baru.
|- `/cash-shifts/{shift}` - Detail shift.
|- `/cash-shifts/{shift}/close` - Tutup shift.
|- `/cash-shifts/{shift}/print` - Cetak laporan shift.
|- `/cash-closings` - Daftar tutup kasir.
|- `/cash-closings/create` - Form buat tutup kasir.
|- `/cash-closings/{closing}` - Detail tutup kasir.
|- `/cash-closings/{closing}/print` - Cetak laporan tutup kasir.
|- `/cash-closings/{closing}/verify` - Verifikasi tutup kasir.

### View yang Dibuat
|- `cash-shifts/index` - Daftar shift kasir dengan filter.
|- `cash-shifts/create` - Form buka shift dengan kas awal.
|- `cash-shifts/show` - Detail shift dengan transaksi dan summary.
|- `cash-shifts/print` - Cetak laporan shift kasir.
|- `cash-closings/index` - Daftar tutup kasir harian.
|- `cash-closings/show` - Detail tutup kasir dengan per shift breakdown.
|- `cash-closings/print` - Cetak laporan tutup kasir harian.

### Controller yang Dibuat/Diupdate
|- `CashShiftController` - CRUD, close shift, dan print laporan.
|- `CashClosingController` - CRUD, verify, summary closing, dan print laporan.
|- `PosController` - Tambah validasi shift aktif.
|- `OrderController` - Tambah validasi lock untuk order yang sudah masuk cash closing.

### Validasi yang Dijalankan
|- `php artisan route:list --name=cash`
|- `php artisan view:cache`
|- `php artisan migrate`
|- `php artisan migrate:status`

### Validasi Ulang dan Perbaikan Phase 4 (2026-06-19)
|- `CashClosingController@create` diperbaiki agar menerima `Request` dan tidak error saat membuka form tutup kasir harian.
|- View `cash-closings/create.blade.php` ditambahkan untuk membuat tutup kasir dari shift tertutup.
|- View `cash-shifts/*` dan `cash-closings/*` disesuaikan ke `@section('container')` agar tampil di layout utama.
|- `CashShiftController` ditambah pencatatan kas masuk/kas keluar selama shift aktif.
|- Tutup shift sekarang menyimpan snapshot total sales, tunai, non-tunai, void, refund, uang fisik, catatan, dan user penutup.
|- POS checkout sekarang mengirim detail split payment dan `OrderController` menyimpan setiap metode pembayaran ke `cash_shift_details`.
|- Cancel/void order membuat catatan koreksi pada shift sesuai metode pembayaran asli.
|- `total_due` pada tutup kasir harian dihitung dari `orders.due_amount`, bukan disamakan dengan total non-tunai.
|- Shift yang sudah masuk tutup kasir harian tidak muncul lagi sebagai kandidat closing baru.
|- UI Phase 4 disamakan dengan halaman inventaris: `inventory-card`, `inventory-filter`, `inventory-actions`, `inventory-table`, select `form-control`, tombol tambah di kanan, dan action button tidak berdempetan.

### Catatan
|- Phase 4 sesuai dengan checklist `03-TODO.md`.
|- Item aktif berikutnya adalah Phase 5: stock opname dan retur penjualan.

## Phase 5 - Stock Opname dan Retur Penjualan (Mulai 2026-06-19)

### Stock Opname yang Diimplementasikan
|- [x] Modul stock opname batch.
|- [x] Generate daftar produk dari snapshot `products.stock`.
|- [x] Input stok fisik manual per produk.
|- [x] Import hasil opname dari Excel.
|- [x] Hitung selisih stok sistem vs stok fisik.
|- [x] Submit hasil opname untuk persetujuan.
|- [x] Persetujuan opname membuat `StockAdjustment` dan `StockMovement`.
|- [x] Simpan riwayat opname per batch.

### Retur Penjualan yang Diimplementasikan
|- [x] Retur penjualan terhubung ke order asal yang sudah `complete`.
|- [x] Retur bisa dipilih sebagai refund atau tukar barang.
|- [x] Qty retur divalidasi agar tidak melebihi sisa item yang bisa diretur.
|- [x] Barang layak jual dikembalikan ke stok saat retur diselesaikan.
|- [x] Barang rusak dicatat di detail retur dan tidak kembali ke stok jual.
|- [x] Refund tercatat di header retur dan masuk ke shift aktif sebagai transaksi refund jika ada shift aktif.

### File Utama yang Ditambah/Diubah
|- `database/migrations/2026_06_19_130000_create_stock_opnames_table.php`
|- `database/migrations/2026_06_19_130001_create_stock_opname_details_table.php`
|- `database/migrations/2026_06_19_130002_add_fields_to_sales_returns_tables.php`
|- `app/Models/StockOpname.php`
|- `app/Models/StockOpnameDetail.php`
|- `app/Models/SalesReturn.php`
|- `app/Models/SalesReturnDetail.php`
|- `app/Http/Controllers/Dashboard/StockOpnameController.php`
|- `app/Http/Controllers/Dashboard/SalesReturnController.php`
|- `resources/views/stock-opnames/index.blade.php`
|- `resources/views/stock-opnames/create.blade.php`
|- `resources/views/stock-opnames/show.blade.php`
|- `resources/views/stock-opnames/import.blade.php`
|- `resources/views/sales-returns/index.blade.php`
|- `resources/views/sales-returns/create.blade.php`
|- `resources/views/sales-returns/show.blade.php`
|- `routes/web.php`
|- `resources/views/dashboard/body/sidebar.blade.php`

### Validasi yang Dijalankan
|- `php -l app/Http/Controllers/Dashboard/StockOpnameController.php`
|- `php -l app/Models/StockOpname.php`
|- `php -l app/Models/StockOpnameDetail.php`
|- `php -l app/Http/Controllers/Dashboard/SalesReturnController.php`
|- `php -l app/Models/SalesReturn.php`
|- `php -l app/Models/SalesReturnDetail.php`
|- `php artisan migrate`
|- `php artisan route:list --name=stock-opnames`
|- `php artisan route:list --name=sales-returns`
|- `php artisan view:cache`
|- `php artisan migrate:status`

### Catatan Phase 5
|- Phase 5 sudah clear berdasarkan checklist `03-TODO.md`.
|- Migration awal `sales_returns` dan `sales_return_details` sempat kosong, lalu dilengkapi lewat migration alter `2026_06_19_130002_add_fields_to_sales_returns_tables.php`.

## Kesimpulan Teknis Saat Ini

- POS membuat order dengan status `pending`.
- Stok produk dikurangi saat order diubah menjadi `complete`.
- Piutang dicatat lewat `due_amount`.
- Pembayaran piutang mengubah `pay_amount` dan `due_amount`.
- Produk punya `stock`, `buying_price`, `selling_price`, dan `expire_date`.
- Modul shift kasir dan tutup kasir harian sudah tersedia.
- Modul stock opname batch sudah tersedia sampai import Excel dan approval.
- Modul retur penjualan sudah tersedia.
- Modul harga, promo, pajak, biaya layanan, barcode scanner, label barcode, dan struk thermal sudah tersedia.
- Modul laporan lanjutan, audit viewer, restore database, dan pengaturan toko sudah tersedia.

## Phase 6 - Progress (2026-06-20)

### Status Phase 6
- Phase 6 sudah clear berdasarkan checklist `03-TODO.md`.
- Diskon per item, diskon invoice, voucher periode aktif, harga grosir, pajak produk/kategori, dan biaya layanan tersimpan di transaksi.
- POS sudah punya input voucher, diskon invoice, biaya layanan, serta alur barcode scanner yang menambah qty item yang sama.
- Produk dan kategori punya field konfigurasi harga/diskon/pajak.
- Voucher/promo punya CRUD dan bisa dinonaktifkan.
- Produk punya halaman cetak label barcode.
- Struk thermal sudah memakai layout khusus 80mm dan auto-print setelah order selesai.
- Validasi: `php -l` file Phase 6, `php artisan migrate`, `php artisan route:list`, dan `php artisan view:cache`.

## Phase 7 - Progress (2026-06-20)

### Status Phase 7
- Phase 7 sudah clear berdasarkan checklist `03-TODO.md`.
- Modul laporan terpadu tersedia untuk penjualan per tanggal, kasir, produk, metode pembayaran, piutang, laba kotor, stok minimum, dan produk dekat kedaluwarsa.
- Laporan memakai order `complete`; laporan penjualan tanggal memperhitungkan retur selesai sebagai pengurang netto.
- Laba kotor memakai snapshot `order_details.buying_price`; transaksi lama yang belum punya snapshot memakai fallback `products.buying_price`.
- Export Excel memakai PhpSpreadsheet; export PDF memakai halaman print browser dengan dataset yang sama.
- Audit log viewer tersedia read-only dengan filter pengguna, tanggal, modul, dan aksi.
- Restore database tersedia dari UI backup dengan permission khusus `restore-database`.
- Pengaturan toko tersedia: nama, alamat, telepon, logo, pajak default, dan mata uang.
- Permission kasir lebih detail tersedia: `discount.order`, `edit-price.order`, `void.order`, dan `report.menu`.
- Validasi: `php -l` file Phase 7, `php artisan migrate`, `php artisan db:seed --class=Phase7PermissionSeeder`, `php artisan route:list`, dan `php artisan view:cache`.

## Risiko Saat Ini

- `composer.phar` masih file lokal yang tidak terlacak, dipakai supaya tidak perlu Composer global.
- Stok yang sudah pernah di-void dan diselesaikan ulang belum diuji skenario kompleks.
- Export PDF memakai print browser, bukan generator PDF server-side.
- WhatsApp otomatis membutuhkan WhatsApp Business Cloud API aktif dari Meta: `Phone Number ID`, permanent access token, nomor customer valid, dan domain publik untuk link invoice mobile.

### Langkah Selanjutnya

Phase 7 sudah selesai. Phase 8 di `03-TODO.md` sudah tercatat selesai dari pekerjaan sebelumnya. Tambahan WhatsApp invoice otomatis juga sudah tersedia dan bisa diaktifkan dari Pengaturan Toko.

## Tambahan WhatsApp Invoice Otomatis (2026-06-20)

- Pengaturan Toko sekarang punya konfigurasi WhatsApp bot: aktif/nonaktif, API version, Phone Number ID, access token, base URL invoice, dan instruksi transfer.
- Order sukses akan menjadwalkan pengiriman WhatsApp setelah transaksi database commit.
- Pesan WhatsApp berisi salam, nomor pesanan, tanggal, daftar produk, bahan, qty, harga, ukuran, keterangan, total order, total bayar, sisa pembayaran, status pembayaran, link invoice mobile, dan instruksi transfer.
- Link invoice mobile tersedia di `/e-invoice-mobile/{token}` dengan token terenkripsi.
- Produk punya field pendukung `material`, `print_size`, dan `print_notes`.
- Pengiriman dicatat di tabel `whatsapp_message_logs`.
- Jika WhatsApp belum aktif atau nomor customer kosong, checkout tetap berhasil; pengiriman akan dilewati/dicatat.

## Instruksi Untuk Sesi Lanjutan

Jika sesi AI/developer berikutnya dimulai dari nol:

1. Baca `01-PRD.md` untuk memahami batasan dan larangan perubahan.
2. Baca file ini untuk memahami status terakhir.
3. Baca `03-TODO.md` untuk menentukan phase aktif.
4. Baca `04-HISTORY.md` untuk melihat keputusan sebelumnya.
5. Baca `05-PROJECT_STRUCTURE.md` untuk memahami struktur yang tidak boleh dirombak.
6. Jalankan `git status --short`.
7. Jangan langsung membuat kode sebelum tahu item TODO paling atas yang belum selesai.

## Catatan Anti-Miskomunikasi

- Project ini bukan penulisan ulang.
- Project ini bukan permintaan desain ulang UI total.
- Project ini bukan migrasi framework.
- Project ini bukan penggantian database.
- Project ini adalah pengembangan bertahap di atas struktur Laravel POS yang sudah ada.
- Fitur lama harus tetap hidup.
- Jika ada ide fitur baru di luar TODO, catat dulu, jangan langsung implementasikan.
