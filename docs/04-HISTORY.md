# 04 - History

## 2026-06-08

### Instalasi Project

- Clone repo `https://github.com/fajarghifar/laravel-point-of-sale` ke `D:\Project\Web\pos3`.
- Branch yang digunakan: `main`.
- Composer global tidak tersedia, jadi `composer.phar` diunduh ke root project.
- PHP `C:\Server\php\php.exe` versi 8.5.5 tidak cocok dengan `phpoffice/phpspreadsheet 1.30.1` karena package membatasi PHP `<8.5`.
- Dependency PHP berhasil di-install memakai `C:\php\php.exe` versi 8.4.11.
- Node tersedia, dependency frontend berhasil di-install.
- Asset frontend berhasil di-build dengan `npm run build`.
- `.env` dibuat dari `.env.example`.
- `APP_KEY` berhasil dibuat.
- Database MariaDB yang berhasil dipakai adalah `127.0.0.1:3307`, user `root`, password kosong.
- Database `point_of_sale` dibuat.
- `php artisan migrate:fresh --seed` berhasil.
- `php artisan storage:link` berhasil.
- Server Laravel berjalan di `http://127.0.0.1:8084`.

### Analisis Alur Project

- Login default: `admin` / `password`.
- POS membuat order status `pending`.
- Detail order pending punya tombol `Complete Order`.
- Saat `Complete Order`, stok produk dikurangi sesuai `order_details.quantity`.
- Pending due dicatat dari selisih `total - pay_amount`.
- Pembayaran due mengurangi `due_amount` dan menambah `pay_amount`.
- Stock opname dan closing harian belum ada sebagai modul khusus.

### Dokumentasi

- Dibuat dokumen:
  - `docs/01-PRD.md`
  - `docs/02-PROGRESS.md`
  - `docs/03-TODO.md`
  - `docs/04-HISTORY.md`
  - `docs/05-PROJECT_STRUCTURE.md`

### Penguatan Dokumentasi Untuk AI Handoff

- `01-PRD.md` diperluas dengan instruksi wajib, sumber kebenaran, lingkup produk, hak akses konseptual, detail alur saat ini, batas perubahan teknis, standar data, standar UI, dan cara melanjutkan jika context AI terpotong.
- `03-TODO.md` diperluas dengan protokol kerja wajib, makna checklist, larangan implementasi, dan acceptance criteria per phase.
- `05-PROJECT_STRUCTURE.md` diperluas dengan aturan struktur non-negotiable, file dokumentasi, controller sensitif, kolom penting, invariant alur POS, template modul baru, sketsa relasi fitur baru, dan checklist sebelum final.
- `02-PROGRESS.md` diperluas dengan instruksi sesi lanjutan dan catatan anti-miskomunikasi.

Tujuan perubahan ini adalah mencegah AI/developer berikutnya salah tafsir, melakukan rewrite, merombak struktur awal, mengganti framework, atau mencentang TODO yang belum selesai.

## 2026-06-17

### Implementasi Phase 2 - Data Aman Untuk POS

- Validasi stok diimplementasikan di 4 titik: add cart, update cart, store order, dan complete order.
- Permission `allow-negative-stock` untuk user tertentu yang boleh bypass validasi stok.
- Status order baru: `cancelled` dan `void` (sebelumnya hanya `pending` dan `complete`).
- Fitur cancel pending order dengan alasan wajib (modal input).
- Fitur void complete order dengan alasan, permission `void.order`, dan pengembalian stok otomatis.
- Dashboard diperbaiki: total_paid, total_due, today_sales, top products, dan monthly chart sekarang hanya menghitung order `complete`.
- Audit log dasar dibuat: tabel `audit_logs`, model `AuditLog`, service `AuditService::log()`.
- Audit tercatat untuk: login, logout, create order, complete order, cancel order, void order, update due, update product.
- Model `Order` ditambahkan helper: `isFinalized()`, `canBeCancelled()`, `canBeVoided()`, relasi `voidedBy` dan `cancelledBy`.
- OrderController ditambahkan validasi due agar tidak negatif.
- Pending orders list sekarang menampilkan juga order cancelled. Complete orders list menampilkan juga order void.
- View details-order ditambahkan modal cancel/void dan informasi status cancelled/void.
- Error alert ditambahkan di halaman POS, pending orders, complete orders, dan order details.
- Permission baru ditambahkan via `Phase2PermissionSeeder`: `void.order`, `allow-negative-stock`, `audit.menu`.
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
- Audit log viewer belum tersedia di UI, disiapkan untuk Phase 7.
- Skenario kompleks (void lalu complete ulang) belum diuji.

## Format Update Berikutnya

Setiap perubahan berikutnya sebaiknya dicatat dengan format:

```md
## YYYY-MM-DD

### Judul Perubahan

- Ringkasan perubahan.
- File utama yang diubah.
- Migration/command/test yang dijalankan.
- Catatan risiko atau next step.
```
