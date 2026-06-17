# 02 - Progress

## Status Instalasi

- [x] Source code `fajarghifar/laravel-point-of-sale` sudah di-clone ke `D:\Project\Web\pos3`.
- [x] Composer lokal tersedia sebagai `composer.phar`.
- [x] Dependency PHP sudah di-install memakai `C:\php\php.exe`.
- [x] Dependency Node sudah di-install.
- [x] Asset frontend sudah di-build dengan `npm run build`.
- [x] `.env` sudah dibuat dari `.env.example`.
- [x] `APP_KEY` sudah dibuat.
- [x] Database MariaDB `point_of_sale` sudah dibuat di port `3307`.
- [x] Migration dan seeder sudah dijalankan.
- [x] Storage link sudah dibuat.
- [x] Laravel server berjalan di `http://127.0.0.1:8084`.

## Status Analisis Fitur

- [x] Route utama sudah dicek di `routes/web.php`.
- [x] Sidebar/menu sudah dicek di `resources/views/dashboard/body/sidebar.blade.php`.
- [x] POS flow sudah dicek di `PosController`.
- [x] Order flow sudah dicek di `OrderController`.
- [x] Product flow sudah dicek di `ProductController`.
- [x] Dashboard summary sudah dicek di `DashboardController`.

## Phase 2 - Selesai (2026-06-17)

### Validasi Stok
- [x] Validasi stok saat add product ke cart (`PosController@addCart`).
- [x] Validasi stok saat update qty cart (`PosController@updateCart`).
- [x] Validasi stok ulang saat order dibuat (`OrderController@storeOrder`).
- [x] Validasi stok ulang saat order di-complete (`OrderController@updateStatus`).
- [x] Permission `allow-negative-stock` untuk bypass validasi stok.

### Cancel & Void
- [x] Migration: kolom `cancel_reason`, `void_reason`, `voided_by`, `voided_at`, `cancelled_by`, `cancelled_at` di tabel `orders`.
- [x] Status order baru: `cancelled`, `void` (selain `pending`, `complete`).
- [x] Cancel pending order dengan alasan wajib (modal input).
- [x] Void complete order dengan alasan + permission `void.order` + stok dikembalikan.
- [x] Void hanya mengembalikan stok 1x.

### Dashboard Fix
- [x] `total_paid` dan `total_due` hanya hitung order `complete`.
- [x] `today_sales` hanya hitung order `complete`.
- [x] Top selling product hanya hitung order `complete`.
- [x] Monthly chart hanya hitung order `complete`.

### Audit Log
- [x] Tabel `audit_logs` dengan kolom: user_id, module, action, reference_type, reference_id, old_values, new_values, ip_address, user_agent, description.
- [x] Model `AuditLog` dan `AuditService::log()`.
- [x] Audit tercatat untuk: login, logout, create order, complete order, cancel order, void order, update due, update product.

### Permission Baru
- `void.order` - diberikan ke SuperAdmin dan Manager.
- `allow-negative-stock` - diberikan ke SuperAdmin.
- `audit.menu` - disiapkan untuk audit log viewer (Phase 7).

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

## Kesimpulan Teknis Saat Ini

- POS membuat order dengan status `pending`.
- Stok produk dikurangi saat order diubah menjadi `complete`.
- Piutang dicatat lewat `due_amount`.
- Pembayaran piutang mengubah `pay_amount` dan `due_amount`.
- Produk punya `stock`, `buying_price`, `selling_price`, dan `expire_date`.
- Belum ada tabel mutasi stok, closing, shift, purchase, retur, audit, atau stock opname.

## Risiko Saat Ini

- `composer.phar` masih file lokal untracked, dipakai supaya tidak perlu Composer global.
- Audit log viewer belum tersedia (direncanakan di Phase 7).
- Stok yang sudah pernah di-void dan di-complete ulang belum diuji skenario kompleks.

## Next Step

Kerjakan `03-TODO.md` Phase 3 - Inventory Core. Jangan lanjut Phase 4 sebelum semua checklist Phase 3 selesai.

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

- Project ini bukan rewrite.
- Project ini bukan permintaan desain ulang UI total.
- Project ini bukan migrasi framework.
- Project ini bukan penggantian database.
- Project ini adalah pengembangan bertahap di atas struktur Laravel POS yang sudah ada.
- Fitur lama harus tetap hidup.
- Jika ada ide fitur baru di luar TODO, catat dulu, jangan langsung implement.
