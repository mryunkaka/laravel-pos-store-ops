# 04 - Riwayat

## 2026-07-10

### Deployment Hosting Rumahweb - POS3

- Investigasi hosting `pos.fourhz.com` via FTP/HTTP menemukan project Laravel berada langsung di `public_html/pos3`, sementara web root domain membuka folder tersebut sehingga sebelumnya menampilkan directory listing dan bukan `public/index.php`.
- Composer di hosting berhasil dipakai dengan PHP 8.4 karena dependency `phpoffice/phpspreadsheet 1.30.1` membatasi PHP `<8.5`; default PHP 8.5 tidak cocok untuk dependency tersebut.
- Root cause HTTP 500 setelah rewrite adalah `.env` production tidak valid: `APP_NAME=Laravel POS Store Ops` harus diberi kutip karena mengandung spasi.
- Dibuat template `public_html_pos3_root.htaccess` untuk rewrite request root ke folder `public/`, memblokir akses langsung ke folder/file Laravel sensitif, dan memaksa handler PHP 8.4 khusus POS3.
- Catatan: file `.env` tetap tidak dilacak Git dan harus diperbaiki langsung di hosting/cPanel.

## 2026-06-20

### Tambahan - WhatsApp Invoice Otomatis

#### Migration yang Dibuat/Dijalankan
- `2026_06_20_020000_add_whatsapp_settings_and_product_print_fields` - Tambah konfigurasi WhatsApp di store_settings dan field bahan/ukuran/keterangan cetak di products
- `2026_06_20_020001_create_whatsapp_message_logs_table` - Log pengiriman WhatsApp

#### Fitur yang Diimplementasikan
- WhatsApp bot bisa diaktifkan dari Pengaturan Toko.
- Konfigurasi WhatsApp Cloud API tersedia: API version, Phone Number ID, access token, base URL invoice, dan instruksi transfer.
- Order yang berhasil tersimpan mengirim ringkasan invoice otomatis ke nomor customer setelah commit database.
- Link invoice mobile publik memakai token terenkripsi: `/e-invoice-mobile/{token}`.
- Produk punya data pendukung untuk pesan: bahan, ukuran, dan keterangan cetak.
- Pengiriman WhatsApp non-blocking terhadap checkout; gagal kirim dicatat di log.

#### File Utama
- `WhatsappNotificationService`
- `InvoiceMobileController`
- `resources/views/invoices/mobile.blade.php`
- `whatsapp_message_logs`

### Phase 7 - Laporan, Audit, dan Administrasi Lanjutan (Clear)

#### Migration yang Dibuat/Dijalankan
- `2026_06_20_010000_add_phase7_reporting_fields` - Tambah `orders.user_id`, `order_details.buying_price`, dan `products.minimum_stock`
- `2026_06_20_010001_create_store_settings_table` - Tabel pengaturan toko

#### Seeder yang Dibuat/Dijalankan
- `Phase7PermissionSeeder` - Permission `report.menu`, `settings.menu`, `restore-database`, `discount.order`, dan `edit-price.order`

#### Fitur yang Diimplementasikan
- Laporan penjualan per tanggal, kasir, produk, metode pembayaran, piutang, laba kotor, stok minimum, dan produk dekat kedaluwarsa
- Export laporan ke Excel memakai PhpSpreadsheet
- Export PDF memakai halaman print browser dengan dataset sama seperti tampilan
- Audit log viewer read-only dengan filter pengguna, tanggal, modul, dan aksi
- Restore database dari file `.sql` atau `.zip` lewat UI backup dengan permission `restore-database`
- Pengaturan toko untuk nama, alamat, telepon, logo, pajak default, dan mata uang
- Permission role kasir lebih detail untuk diskon invoice, edit harga, void, dan akses laporan

#### Controller dan View yang Ditambahkan/Diupdate
- `ReportController` dan view `reports/*`
- `AuditLogController` dan view `audit-logs/*`
- `StoreSettingController` dan view `settings/store`
- `DatabaseBackupController` dan view `database/index`
- Sidebar ditambah menu Laporan, Audit Log, dan Pengaturan Toko

#### Catatan
- Order baru menyimpan `user_id` sebagai kasir dan `buying_price` di detail order untuk laporan laba kotor.
- Transaksi lama yang belum punya snapshot harga beli memakai fallback harga beli produk saat laporan dibuat.
- Restore database adalah aksi destruktif dan dikunci permission khusus.
- View Phase 7 divalidasi dengan `php artisan view:cache`.
- Route Phase 7 divalidasi dengan `php artisan route:list`.

### Phase 6 - Harga, Promo, Pajak, dan Barcode (Clear)

#### Migration yang Dibuat/Dijalankan
- `2026_06_19_231025_add_discount_to_order_details` - Tambah kolom discount dan discount_type di order_details
- `2026_06_19_231147_add_discount_to_products` - Tambah kolom discount, discount_type, wholesale_price, wholesale_qty, tax_rate di products
- `2026_06_19_231220_add_discount_to_orders` - Tambah kolom discount, discount_type, service_charge, tax_total, tax_type di orders
- `2026_06_19_231728_add_voucher_table` - Tabel vouchers untuk promo
- `2026_06_19_231942_create_order_discounts_table` - Tabel order_discounts untuk mencatat semua diskon per order
- `2026_06_20_000001_add_tax_rate_to_categories_table` - Tambah tax_rate di categories

#### Model yang Dibuat/Diupdate
- `Voucher` - Manajemen voucher/promo dengan periode aktif, batas penggunaan
- `OrderDetails` - Tambah fillable dan casts untuk discount dan discount_type
- `Product` - Tambah fillable dan casts untuk discount, discount_type, wholesale_price, wholesale_qty, tax_rate
- `Order` - Tambah fillable dan casts untuk discount, discount_type, service_charge, tax_total, tax_type
- `Category` - Tambah fillable tax_rate untuk pajak per kategori

#### Fitur yang Diimplementasikan
- Diskon per item dari konfigurasi produk, tersimpan di order_details dan order_discounts
- Diskon per invoice dari input POS, tersimpan di orders dan order_discounts
- Voucher/promo dengan periode aktif, batas pemakaian, min purchase, max discount, dan status aktif/nonaktif
- Harga grosir otomatis aktif saat qty memenuhi wholesale_qty
- Pajak fleksibel per produk dengan fallback pajak kategori
- Biaya layanan opsional dari POS dan tersimpan di orders
- Barcode scanner di POS bisa scan kode, menambah produk, dan menaikkan qty item yang sama
- Cetak label barcode dari detail produk
- Struk thermal layout 80mm dan auto-print setelah order selesai

#### Controller yang Diupdate
- `PosController` - Hitung harga grosir, diskon item, pajak, barcode add/update qty, dan opsi cart
- `OrderController` - Hitung summary order, diskon invoice/voucher, pajak, biaya layanan, dan catatan order_discounts
- `ProductController` - Default field diskon/pajak/harga grosir dan halaman cetak label barcode
- `VoucherController` - CRUD voucher/promo
- `BarcodeController` - Endpoint search dan quickAdd untuk barcode scanner

#### Routes yang Ditambahkan
- `POST /pos/barcode/search` - Cari produk berdasarkan barcode
- `POST /pos/barcode/quick-add` - Quick add produk ke cart via barcode
- `GET /products/{product}/barcode-label` - Cetak label barcode produk
- Resource `/vouchers` - Manajemen voucher/promo

#### Catatan
- Cart menggunakan session Gloudemans Shoppingcart, diskon disimpan di options
- OrderController@storeOrder menyimpan diskon detail, diskon invoice/voucher, pajak, dan biaya layanan
- View Phase 6 divalidasi dengan `php artisan view:cache`
- Route Phase 6 divalidasi dengan `php artisan route:list`

## 2026-06-08

### Instalasi Project

- Clone repo `https://github.com/fajarghifar/laravel-point-of-sale` ke `D:\Project\Web\pos3`.
- Branch yang dipakai: `main`.
- Composer global tidak tersedia, jadi `composer.phar` diunduh ke akar project.
- PHP `C:\Server\php\php.exe` versi 8.5.5 tidak cocok dengan `phpoffice/phpspreadsheet 1.30.1` karena package membatasi PHP `<8.5`.
- Dependensi PHP berhasil di-install memakai `C:\php\php.exe` versi 8.4.11.
- Node tersedia, dependensi frontend berhasil di-install.
- Aset frontend berhasil di-build dengan `npm run build`.
- `.env` dibuat dari `.env.example`.
- `APP_KEY` berhasil dibuat.
- Database MariaDB yang berhasil dipakai adalah `127.0.0.1:3307`, pengguna `root`, password kosong.
- Database `point_of_sale` dibuat.
- `php artisan migrate:fresh --seed` berhasil.
- `php artisan storage:link` berhasil.
- Server Laravel berjalan di `http://127.0.0.1:8084`.

### Analisis Alur Project

- Login default: `admin` / `password`.
- POS membuat order status `pending`.
- Detail order tertunda punya tombol `Complete Order`.
- Saat `Complete Order`, stok produk dikurangi sesuai `order_details.quantity`.
- Piutang tertunda dicatat dari selisih `total - pay_amount`.
- Pembayaran piutang mengurangi `due_amount` dan menambah `pay_amount`.
- Stock opname dan tutup kasir harian belum ada sebagai modul khusus.

### Dokumentasi

- Dibuat dokumen:
  - `docs/01-PRD.md`
  - `docs/02-PROGRESS.md`
  - `docs/03-TODO.md`
  - `docs/04-HISTORY.md`
  - `docs/05-PROJECT_STRUCTURE.md`

### Penguatan Dokumentasi Untuk Serah Terima AI

- `01-PRD.md` diperluas dengan instruksi wajib, sumber kebenaran, lingkup produk, hak akses konseptual, detail alur saat ini, batas perubahan teknis, standar data, standar UI, dan cara melanjutkan jika konteks AI terpotong.
- `03-TODO.md` diperluas dengan protokol kerja wajib, makna checklist, larangan implementasi, dan kriteria penerimaan per phase.
- `05-PROJECT_STRUCTURE.md` diperluas dengan aturan struktur yang tidak bisa ditawar, file dokumentasi, controller sensitif, kolom penting, aturan tetap alur POS, templat modul baru, sketsa relasi fitur baru, dan checklist sebelum selesai.
- `02-PROGRESS.md` diperluas dengan instruksi sesi lanjutan dan catatan anti-miskomunikasi.

Tujuan perubahan ini adalah mencegah AI/developer berikutnya salah tafsir, melakukan perombakan, merombak struktur awal, mengganti framework, atau mencentang TODO yang belum selesai.

## 2026-06-17

### Implementasi Phase 2 - Data Aman Untuk POS

- Validasi stok diimplementasikan di 4 titik: tambah cart, ubah cart, buat order, dan selesaikan order.
- Permission `allow-negative-stock` untuk pengguna tertentu yang boleh melewati validasi stok.
- Status order baru: `cancelled` dan `void` (sebelumnya hanya `pending` dan `complete`).
- Fitur cancel order tertunda dengan alasan wajib (modal input).
- Fitur void order selesai dengan alasan, permission `void.order`, dan pengembalian stok otomatis.
- Dashboard diperbaiki: total_paid, total_due, today_sales, produk terlaris, dan grafik bulanan sekarang hanya menghitung order `complete`.
- Audit log dasar dibuat: tabel `audit_logs`, model `AuditLog`, layanan `AuditService::log()`.
- Audit tercatat untuk: login, logout, buat order, selesaikan order, batalkan order, void order, bayar piutang, perbarui produk.
- Model `Order` ditambahkan bantuan: `isFinalized()`, `canBeCancelled()`, `canBeVoided()`, relasi `voidedBy` dan `cancelledBy`.
- OrderController ditambahkan validasi piutang agar tidak negatif.
- Daftar order tertunda sekarang menampilkan juga order yang dibatalkan. Daftar order selesai menampilkan juga order yang di-void.
- View detail order ditambahkan modal cancel/void dan informasi status dibatalkan/divoid.
- Notifikasi error ditambahkan di halaman POS, order tertunda, order selesai, dan detail order.
- Permission baru ditambahkan melalui `Phase2PermissionSeeder`: `void.order`, `allow-negative-stock`, `audit.menu`.
- Role Manager mendapat permission `void.order`.

Migration yang dijalankan:
- `2026_06_17_100000_create_audit_logs_table`
- `2026_06_17_100001_add_cancel_void_fields_to_orders_table`

Seeder yang dijalankan:
- `Phase2PermissionSeeder`

File utama yang ditambah:
- `app/Models/AuditLog.php`
- `app/Services/AuditService.php`
- `database/migrations/2026_06_17_100000_create_audit_logs_table.php`
- `database/migrations/2026_06_17_100001_add_cancel_void_fields_to_orders_table.php`
- `database/seeders/Phase2PermissionSeeder.php`

File utama yang diubah:
- `app/Http/Controllers/Dashboard/OrderController.php`
- `app/Http/Controllers/Dashboard/PosController.php`
- `app/Http/Controllers/Dashboard/DashboardController.php`
- `app/Http/Controllers/Dashboard/ProductController.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Models/Order.php`
- `routes/web.php`
- `database/seeders/RolePermissionSeeder.php`
- `resources/views/orders/details-order.blade.php`
- `resources/views/orders/pending-orders.blade.php`
- `resources/views/orders/complete-orders.blade.php`
- `resources/views/pos/index.blade.php`

Catatan risiko:
- Penampil audit log belum tersedia di UI, disiapkan untuk Phase 7.
- Skenario kompleks (void lalu selesaikan ulang) belum diuji.

## 2026-06-17
### Phase 8 — Migrasi Bahasa Tampilan ke Indonesia

#### Ringkasan Perubahan
- Semua teks tampilan aplikasi (UI) dalam Blade views diterjemahkan dari bahasa Inggris ke bahasa Indonesia.
- Total 85+ file blade view di seluruh modul telah diterjemahkan.
- Tidak ada perubahan pada controller, model, migration, atau route.
- Istilah teknis tetap dalam bahasa Inggris: POS, Role, Permission, Email, Username, Password, QRIS, Excel, PDF.

#### File Utama yang Diubah (Blade Views)
**Modul:**
- `resources/views/dashboard/` (sidebar, navbar, footer, index)
- `resources/views/pos/` (index, cart-sidebar, print-invoice, print-receipt)
- `resources/views/orders/` (pending-orders, complete-orders, details-order, invoice-order, pending-due, print-invoice, print-receipt)
- `resources/views/products/` (index, create, edit, show, import)
- `resources/views/categories/` (index, create, edit)
- `resources/views/customers/` (index, create, edit, show)
- `resources/views/suppliers/` (index, create, edit, show)
- `resources/views/employees/` (index, create, edit, show)
- `resources/views/attendance/` (index, create, edit)
- `resources/views/advance-salary/` (index, create, edit)
- `resources/views/pay-salary/` (index, create, create_single, pay-all, history, history-details)
- `resources/views/roles/` (9 files: permission-create/edit/index, role-create/edit/index, role-permission-create/edit/index)
- `resources/views/users/` (index, create, edit)
- `resources/views/auth/` (login, register, forgot-password, reset-password, confirm-password, verify-email)
- `resources/views/profile/` (index, edit, change-password, delete, partials/*)
- `resources/views/database/index.blade.php`
- `resources/views/help/index.blade.php`
- `resources/views/errors/403.blade.php, errors/404.blade.php`
- `resources/views/welcome.blade.php`

#### Migration/command/test yang Dijalankan
- Tidak ada migration baru.
- Tidak ada command artisan yang dijalankan.
- Tidak ada test yang diubah/ditambahkan.

#### Catatan Risiko
- Istilah teknis seperti "POS", "Role", "Permission" sengaja dibiarkan dalam bahasa Inggris karena sudah umum dipahami pengguna Indonesia.
- Bulan di chart penjualan sudah diterjemahkan ke format Bahasa Indonesia (Januari, Februari, dll.).
- Status order badge tetap dalam bahasa Inggris: Pending, Complete, Cancelled, Void (sesuai kriteria acceptance).

#### Langkah Selanjutnya
- Lanjut ke Phase 3 - Inti Inventaris (stock movements, purchase orders, purchase receiving).

|## 2026-06-19
|### Phase 3 — Inti Inventaris
|
|#### Ringkasan Perubahan
|- Migration stock movements dan relasi dibuat (7 tabel baru).
|- Model StockMovement, PurchaseOrder, PurchaseOrderDetail, PurchaseReceiving, PurchaseReceivingDetail dibuat.
|- OrderController diupdate: catat stock movement otomatis saat order complete/void.
|- StockMovementController dibuat untuk history & adjustment stok.
|- PurchaseOrderController dibuat untuk manajemen PO.
|- PurchaseReceivingController dibuat untuk manajemen penerimaan.
|- Routes untuk stock-movements, purchase-orders, purchase-receivings ditambahkan.
|
|#### Migration yang Dijalankan
|- `2026_06_18_235822_create_stock_movements_table` - Tabel pergerakan stok
|- `2026_06_19_000818_create_purchase_orders_table` - Tabel purchase orders
|- `2026_06_19_001741_create_purchase_order_details_table` - Detail PO
|- `2026_06_19_001742_create_purchase_receivings_table` - Tabel penerimaan
|- `2026_06_19_002156_create_purchase_receiving_details_table` - Detail receiving
|- `2026_06_19_002245_create_stock_adjustments_table` - Tabel penyesuaian stok
|- `2026_06_19_002323_create_stock_movement_details_table` - Detail pergerakan stok
|
|#### Model Baru
|- `app/Models/StockMovement.php` - Catat semua perubahan stok (in/out/adjustment)
|- `app/Models/PurchaseOrder.php` - Manajemen order pembelian
|- `app/Models/PurchaseOrderDetail.php` - Detail item PO
|- `app/Models/PurchaseReceiving.php` - Catat penerimaan barang
|- `app/Models/PurchaseReceivingDetail.php` - Detail penerimaan barang
|
|#### Controller Baru
|- `app/Http/Controllers/Dashboard/StockMovementController.php`
|- `app/Http/Controllers/Dashboard/PurchaseOrderController.php`
|- `app/Http/Controllers/Dashboard/PurchaseReceivingController.php`
|
|#### Route Baru
|- `/stock-movements` - Daftar pergerakan stok
|- `/stock-movements/history/{product}` - Riwayat stok per produk
|- `/stock-movements/adjust/{product}` - Form penyesuaian stok
|- `/stock-movements/adjust` - Proses penyesuaian stok
|- `/purchase-orders` - Manajemen purchase order
|- `/purchase-receivings` - Manajemen penerimaan
|
|#### Catatan Risiko
|- Retur pembelian belum diimplementasikan.
|- Stock adjustment manual belum diimplementasikan.
|- Transfer stok antar lokasi belum diimplementasikan.
|- View/form untuk stock-movements, purchase-orders, purchase-receivings belum dibuat.
|- Filter riwayat stok belum diimplementasikan di controller.
|
|#### Langkah Selanjutnya
|- Buat view/form untuk stock movements history & adjustment.
|- Buat view/form untuk purchase orders.
|- Buat view/form untuk purchase receivings.
|- Implementasi retur pembelian.
|- Implementasi stock adjustment manual.

## 2026-06-19

### Perbaikan Validasi Phase 3 - Inti Inventaris

#### Ringkasan Perubahan
- Parse error di `PurchaseReceivingController` diperbaiki.
- Penerimaan barang sekarang menyimpan detail saat status `pending`, dan stok baru bertambah saat penerimaan diselesaikan.
- Retur pembelian ditambahkan dengan alur `pending` lalu `completed`; stok berkurang dan stock movement tercatat saat retur diselesaikan.
- `StockAdjustment` model ditambahkan, controller penyesuaian stok disesuaikan dengan migration, dan stok minus dicegah kecuali user punya permission `allow-negative-stock`.
- `StockMovement` diperbaiki agar selalu menyimpan `reference_type` dan `reference_id`.
- View detail untuk order pembelian, penerimaan barang, retur pembelian, dan penyesuaian stok ditambahkan.
- Label UI inventaris yang masih Inggris dirapikan ke bahasa Indonesia.

#### Migration yang Dijalankan
- `2026_06_19_010621_create_purchase_returns_table`
- `2026_06_19_010623_create_purchase_return_details_table`
- `2026_06_19_091500_add_stock_snapshots_to_stock_adjustments_table`

#### File Utama yang Diubah/Ditambah
- `app/Http/Controllers/Dashboard/PurchaseReceivingController.php`
- `app/Http/Controllers/Dashboard/PurchaseReturnController.php`
- `app/Http/Controllers/Dashboard/StockAdjustmentController.php`
- `app/Http/Controllers/Dashboard/StockMovementController.php`
- `app/Models/StockAdjustment.php`
- `app/Models/StockMovement.php`
- `app/Models/PurchaseOrder.php`
- `routes/web.php`
- `resources/views/purchase-orders/show.blade.php`
- `resources/views/purchase-receivings/show.blade.php`
- `resources/views/purchase-returns/show.blade.php`
- `resources/views/stock-adjustments/show.blade.php`

#### Validasi
- `php -l` untuk controller/model inventaris yang diubah.
- `php artisan route:list --name=purchase`
- `php artisan route:list --name=stock`
- `php artisan route:list`
- `php artisan view:cache`
- `php artisan migrate`
- `php artisan migrate:status`

#### Catatan Risiko
- Transfer stok antar lokasi sudah tersedia sebagai struktur awal. Stok per lokasi belum dipisah; transfer mencatat dokumen dan pergerakan stok referensial tanpa mengubah total `products.stock`.
- Belum dilakukan uji browser manual end-to-end oleh pengguna.

## 2026-06-19

### Penutupan Phase 3 - Transfer Stok

#### Ringkasan Perubahan
- Modul transfer stok ditambahkan sebagai item terakhir Phase 3.
- Struktur lokasi stok awal dibuat dengan `Toko Utama` dan `Gudang Toko`.
- Transfer stok punya status `pending` dan `completed`.
- Saat transfer diselesaikan, sistem mencatat pergerakan stok keluar dan masuk dengan referensi dokumen transfer.
- Sidebar Inventaris ditambah menu `Transfer Stok`.

#### Migration yang Dijalankan
- `2026_06_19_120000_create_stock_locations_table`
- `2026_06_19_120001_create_stock_transfers_table`
- `2026_06_19_120002_create_stock_transfer_details_table`

#### File Utama yang Ditambah
- `app/Models/StockLocation.php`
- `app/Models/StockTransfer.php`
- `app/Models/StockTransferDetail.php`
- `app/Http/Controllers/Dashboard/StockTransferController.php`
- `resources/views/stock-transfers/index.blade.php`
- `resources/views/stock-transfers/create.blade.php`
- `resources/views/stock-transfers/show.blade.php`

#### Validasi
- `php -l app/Http/Controllers/Dashboard/StockTransferController.php`
- `php artisan migrate`
- `php artisan view:cache`
- `php artisan route:list --name=stock`
- `php artisan migrate:status`

#### Status
- Phase 3 sudah clear.
- Lanjut Phase 4: modul shift kasir.

## 2026-06-19

### Validasi dan Perbaikan Phase 4 - Operasional Kasir

#### Ringkasan Perubahan
- Validasi ulang checklist Phase 4 menemukan beberapa fitur sudah dicentang tetapi belum siap dipakai dari UI.
- `CashClosingController@create` diperbaiki agar menerima `Request`.
- Halaman `cash-closings/create.blade.php` ditambahkan untuk membuat tutup kasir harian.
- View cash shift dan cash closing disesuaikan ke section layout `container`.
- Kas masuk/kas keluar ditambahkan dari halaman detail shift aktif.
- Tutup shift sekarang menyimpan snapshot total sales, tunai, non-tunai, void, refund, uang fisik, catatan, dan user penutup.
- POS mendukung input split payment dengan metode tunai, QRIS, debit, transfer, dan e-wallet.
- Detail pembayaran POS dicatat ke `cash_shift_details` per metode pembayaran.
- Cancel/void order mencatat koreksi shift sesuai metode pembayaran asli.
- Piutang pada tutup kasir harian dihitung dari `orders.due_amount`, bukan dari total non-tunai.
- Shift yang sudah masuk closing harian tidak dapat dipakai ulang untuk closing berikutnya.
- UI Phase 4 dirapikan agar konsisten dengan halaman inventaris: filter/select, tombol tambah kanan, spacing tombol aksi, dan table style.

#### File Utama yang Diubah/Ditambah
- `app/Http/Controllers/Dashboard/CashShiftController.php`
- `app/Http/Controllers/Dashboard/CashClosingController.php`
- `app/Http/Controllers/Dashboard/OrderController.php`
- `app/Http/Requests/Order/StoreOrderRequest.php`
- `app/Models/CashShift.php`
- `routes/web.php`
- `resources/views/cash-shifts/index.blade.php`
- `resources/views/cash-shifts/create.blade.php`
- `resources/views/cash-shifts/show.blade.php`
- `resources/views/cash-closings/index.blade.php`
- `resources/views/cash-closings/show.blade.php`
- `resources/views/cash-closings/create.blade.php`
- `resources/views/pos/cart-sidebar.blade.php`
- `resources/views/pos/index.blade.php`

#### Validasi
- `php -l` untuk controller/model yang diubah.
- Validasi route/view/migration dijalankan setelah perubahan.

#### Status
- Phase 4 sudah clear setelah perbaikan validasi.
- Lanjut Phase 5: stock opname dan retur penjualan.

## 2026-06-19

### Phase 5 - Stock Opname dan Retur Penjualan

#### Ringkasan Perubahan
- Modul stock opname batch dibuat.
- Batch opname otomatis membuat detail dari semua produk dan menyimpan snapshot stok sistem.
- User bisa input stok fisik manual per produk.
- Hasil opname bisa diimport dari Excel.
- Sistem menghitung selisih stok fisik vs stok sistem.
- Draft opname bisa disubmit untuk persetujuan.
- Approval opname mengubah stok produk, membuat `StockAdjustment`, dan mencatat `StockMovement`.
- Riwayat opname per batch tersedia di halaman daftar dan detail.
- Modul retur penjualan dibuat dan terhubung ke invoice/order asal.
- Retur penjualan mendukung tipe refund dan tukar barang.
- Qty retur divalidasi terhadap sisa item yang bisa diretur.
- Barang layak jual dikembalikan ke stok dan tercatat di stock movement saat retur selesai.
- Barang rusak dicatat di detail retur dan tidak kembali ke stok jual.
- Refund retur tercatat ke shift aktif sebagai transaksi refund jika ada shift aktif.
- Sidebar Inventaris ditambah menu `Stock Opname` dan `Retur Penjualan`.

#### File Utama yang Ditambah/Diubah
- `database/migrations/2026_06_19_130000_create_stock_opnames_table.php`
- `database/migrations/2026_06_19_130001_create_stock_opname_details_table.php`
- `database/migrations/2026_06_19_130002_add_fields_to_sales_returns_tables.php`
- `app/Models/StockOpname.php`
- `app/Models/StockOpnameDetail.php`
- `app/Models/SalesReturn.php`
- `app/Models/SalesReturnDetail.php`
- `app/Http/Controllers/Dashboard/StockOpnameController.php`
- `app/Http/Controllers/Dashboard/SalesReturnController.php`
- `resources/views/stock-opnames/index.blade.php`
- `resources/views/stock-opnames/create.blade.php`
- `resources/views/stock-opnames/show.blade.php`
- `resources/views/stock-opnames/import.blade.php`
- `resources/views/sales-returns/index.blade.php`
- `resources/views/sales-returns/create.blade.php`
- `resources/views/sales-returns/show.blade.php`
- `routes/web.php`
- `resources/views/dashboard/body/sidebar.blade.php`

#### Validasi
- `php -l` untuk controller/model stock opname.
- `php -l` untuk controller/model retur penjualan.
- `php artisan migrate`
- `php artisan route:list --name=stock-opnames`
- `php artisan route:list --name=sales-returns`
- `php artisan view:cache`
- `php artisan migrate:status`

#### Status
- Phase 5 sudah clear berdasarkan checklist `03-TODO.md`.
- Migration awal `sales_returns` dan `sales_return_details` sempat kosong, lalu dilengkapi lewat migration alter `2026_06_19_130002_add_fields_to_sales_returns_tables.php`.
- Lanjut Phase 6: harga, promo, pajak, dan barcode.

## Format Pembaruan Selanjutnya

Setiap perubahan berikutnya sebaiknya dicatat dengan format:

```md
## YYYY-MM-DD

### Judul Perubahan

- Ringkasan perubahan.
- File utama yang diubah.
- Migration/command/test yang dijalankan.
- Catatan risiko atau langkah selanjutnya.
```
