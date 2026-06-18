# 04 - Riwayat

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
