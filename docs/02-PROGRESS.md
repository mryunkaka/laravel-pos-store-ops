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

|## Phase 3 - Inti Inventaris (2026-06-19)
|
|### Migration yang Dibuat
|- `2026_06_18_235822_create_stock_movements_table` - Tabel pergerakan stok
|- `2026_06_19_000818_create_purchase_orders_table` - Tabel purchase orders
|- `2026_06_19_001741_create_purchase_order_details_table` - Detail PO
|- `2026_06_19_001742_create_purchase_receivings_table` - Tabel penerimaan
|- `2026_06_19_002156_create_purchase_receiving_details_table` - Detail receiving
|- `2026_06_19_002245_create_stock_adjustments_table` - Tabel penyesuaian stok
|- `2026_06_19_002323_create_stock_movement_details_table` - Detail pergerakan stok
|
|### Model yang Dibuat
|- `StockMovement` - Catat semua perubahan stok (in/out/adjustment)
|- `PurchaseOrder` - Manajemen order pembelian
|- `PurchaseOrderDetail` - Detail item PO
|- `PurchaseReceiving` - Catat penerimaan barang
|- `PurchaseReceivingDetail` - Detail penerimaan barang
|
|### Fitur yang Diimplementasikan
|- [x] Catat stok keluar otomatis saat order selesai (via StockMovement::recordOut)
|- [x] Catat stok masuk saat receiving barang (via StockMovement::recordIn)
|- [x] Buat purchase order dari pemasok
|- [x] Buat penerimaan barang/purchase receiving
|- [x] Stock movement controller untuk history & adjustment
|- [x] Purchase order controller (index, create, store, show, destroy)
|- [x] Purchase receiving controller (index, create, store, show, complete, destroy)
|
|### Routes yang Ditambahkan
|- `/stock-movements` - Daftar pergerakan stok
|- `/stock-movements/history/{product}` - Riwayat stok per produk
|- `/stock-movements/adjust/{product}` - Form penyesuaian stok
|- `/stock-movements/adjust` - Proses penyesuaian stok
|- `/purchase-orders` - Daftar purchase order
|- `/purchase-orders/create` - Form create PO
|- `/purchase-orders` - Store PO
|- `/purchase-orders/{po}` - Detail PO
|- `/purchase-orders/{po}` - Hapus PO
|- `/purchase-receivings` - Daftar penerimaan
|- `/purchase-receivings/create` - Form create receiving
|- `/purchase-receivings` - Store receiving
|- `/purchase-receivings/{receiving}` - Detail receiving
|- `/purchase-receivings/{receiving}/complete` - Selesaikan receiving
|
|### Catatan
|- Stok otomatis dikurangi saat order complete (tidak perlu input manual)
|- Stok otomatis ditambah saat receiving selesai (tidak perlu input manual)
|- Stock movement mencatat reference ke transaksi asal (polymorphic)
|- Purchase receiving belum otomatis menambah stok sebelum di-complete
|- Retur pembelian dan stock adjustment masih perlu implementasi

## Kesimpulan Teknis Saat Ini

- POS membuat order dengan status `pending`.
- Stok produk dikurangi saat order diubah menjadi `complete`.
- Piutang dicatat lewat `due_amount`.
- Pembayaran piutang mengubah `pay_amount` dan `due_amount`.
- Produk punya `stock`, `buying_price`, `selling_price`, dan `expire_date`.
- Belum ada tabel mutasi stok, tutup kasir, shift, pembelian, retur, audit, atau stock opname.

## Risiko Saat Ini

- `composer.phar` masih file lokal yang tidak terlacak, dipakai supaya tidak perlu Composer global.
- Penampil audit log belum tersedia (direncanakan di Phase 7).
- Stok yang sudah pernah di-void dan diselesaikan ulang belum diuji skenario kompleks.

### Langkah Selanjutnya

Kerjakan `03-TODO.md` Phase 3 - Inti Inventaris (stock movements, purchase orders, purchase receiving, stock adjustment).

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
