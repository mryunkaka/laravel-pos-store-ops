# 05 - Struktur Project

Dokumen ini menjelaskan struktur yang harus dipertahankan. AI/developer berikutnya tidak boleh menganggap struktur ini sebagai saran opsional. Fitur baru harus menempel ke struktur yang ada, bukan mengganti pondasi project.

## Aturan Struktur Yang Tidak Bisa Ditawar

- Pertahankan Laravel 10 sebagai framework.
- Pertahankan Blade view di `resources/views`.
- Pertahankan controller dashboard di namespace `App\Http\Controllers\Dashboard`.
- Pertahankan model di `app/Models`.
- Pertahankan route web utama di `routes/web.php`.
- Pertahankan Spatie Permission sebagai role/permission.
- Pertahankan layout dashboard yang ada.
- Pertahankan nama tabel yang ada.
- Pertahankan alur order tertunda lalu selesai, kecuali perubahan dilakukan bertahap sesuai TODO dan tetap kompatibel ke depan.
- Jangan membuat folder paralel seperti `src`, `frontend`, `api-v2`, atau `new-pos` untuk menggantikan aplikasi lama.

## Root Penting

- `app/Http/Controllers/Auth`: controller auth bawaan Laravel Breeze.
- `app/Http/Controllers/Dashboard`: controller modul dashboard/POS/admin.
- `app/Http/Requests`: form request validation.
- `app/Models`: model Eloquent.
- `config`: konfigurasi Laravel dan package.
- `database/migrations`: struktur tabel.
- `database/seeders`: data awal role, user, product, category, customer, supplier, employee.
- `public`: web root, asset public, build Vite.
- `resources/views`: Blade views.
- `routes/web.php`: route utama aplikasi.
- `storage`: logs, cache, uploads, backup.
- `docs`: dokumentasi kerja project ini.

## File Dokumentasi

- `docs/01-PRD.md`: visi produk, batasan, persona, prinsip, dan instruksi wajib.
- `docs/02-PROGRESS.md`: status pekerjaan, risiko, dan langkah selanjutnya.
- `docs/03-TODO.md`: checklist phase yang harus dikerjakan berurutan.
- `docs/04-HISTORY.md`: histori perubahan dan keputusan penting.
- `docs/05-PROJECT_STRUCTURE.md`: struktur project, alur data, dan panduan penambahan modul.

Jika AI lain hanya diberi satu instruksi, instruksinya adalah: baca lima dokumen ini sebelum coding.

## Controller Utama

- `DashboardController`: ringkasan dashboard, metrik, order terbaru, produk terlaris, grafik bulanan.
- `PosController`: halaman POS, tambah/ubah/hapus cart, buat pelanggan AJAX, cari pelanggan.
- `OrderController`: buat order, order tertunda/selesai, detail order, selesaikan order, invoice, struk, bayar piutang.
- `ProductController`: CRUD produk, impor/ekspor Excel, tampilan barcode.
- `CategoryController`: CRUD kategori.
- `CustomerController`: CRUD pelanggan.
- `SupplierController`: CRUD pemasok.
- `EmployeeController`: CRUD karyawan.
- `AttendanceController`: absensi.
- `PaySalaryController`: bayar gaji dan riwayat.
- `AdvanceSalaryController`: gaji di muka.
- `RoleController`: permission, role, role-permission.
- `UserController`: manajemen pengguna.
- `DatabaseBackupController`: backup database.

## Controller Yang Jangan Diubah Sembarangan

- `OrderController`: sensitif karena mengubah status order, due, invoice, dan stok.
- `PosController`: sensitif karena cart dan create order dipakai kasir.
- `ProductController`: sensitif karena impor/ekspor dan perbarui stok produk.
- `DashboardController`: sensitif karena angka laporan bisa menyesatkan jika query salah.
- `RoleController` dan `UserController`: sensitif karena permission dan akses.

Jika harus mengubah controller ini, perubahan harus kecil, sesuai item TODO, dan dicatat di `04-HISTORY.md`.

## Model Saat Ini

- `User`
- `Product`
- `Category`
- `Customer`
- `Supplier`
- `Order`
- `OrderDetails`
- `Employee`
- `Attendance`
- `AdvanceSalary`
- `PaySalary`

## Tabel Inti Saat Ini

- `users`
- `customers`
- `suppliers`
- `employees`
- `attendances`
- `advance_salaries`
- `pay_salaries`
- `categories`
- `products`
- `orders`
- `order_details`
- `roles`, `permissions`, dan tabel pivot dari Spatie Permission

## Kolom Penting Saat Ini

### `products`

- `id`: primary key.
- `name`: nama produk.
- `slug`: slug unik.
- `code`: kode produk/barcode.
- `category_id`: kategori produk.
- `stock`: stok saat ini.
- `buying_price`: harga beli.
- `selling_price`: harga jual.
- `image`: nama file gambar.
- `buying_date`: tanggal beli awal/manual.
- `expire_date`: tanggal expired.

Catatan: `stock` masih angka akhir tanpa histori. Jangan hanya mengandalkan field ini untuk audit.

### `orders`

- `customer_id`: customer transaksi.
- `invoice_no`: nomor invoice.
- `order_date`: tanggal order.
- `order_status`: saat ini `pending` atau `complete`.
- `total_products`: total qty/jumlah item cart.
- `sub_total`: subtotal.
- `vat`: pajak dari cart package.
- `total`: total akhir.
- `payment_type`: metode pembayaran versi lama.
- `pay_amount`: jumlah dibayar.
- `due_amount`: sisa piutang.

Catatan: untuk multi payment, jangan memaksakan semua detail ke `payment_type`. Tambahkan tabel `order_payments`.

### `order_details`

- `order_id`: relasi order.
- `product_id`: produk.
- `quantity`: qty terjual.
- `unit_price`: harga satuan.
- `total`: total baris.

Catatan: jika nanti butuh laporan laba historis akurat, pertimbangkan menyimpan `buying_price_snapshot` dan `selling_price_snapshot`.

## Route/Menu Utama

- `/dashboard`: dashboard.
- `/pos`: POS.
- `/orders/pending`: pending orders.
- `/orders/complete`: complete orders.
- `/pending/due`: pending due.
- `/products`: daftar produk.
- `/products/create`: tambah produk.
- `/products/import`: impor produk.
- `/products/export`: ekspor produk.
- `/categories`: kategori.
- `/customers`: pelanggan.
- `/suppliers`: pemasok.
- `/employees`: karyawan.
- `/attendance`: absensi.
- `/pay-salary`: bayar gaji.
- `/advance-salary`: gaji di muka.
- `/permission`, `/role`, `/role/permission`: role dan permission.
- `/users`: pengguna.
- `/database/backup`: backup database.
- `/help`: bantuan.

## Alur Data POS Saat Ini

1. Pengguna membuka `/pos`.
2. `PosController@index` menampilkan produk yang `expire_date > today`.
3. Pengguna menambah produk ke cart.
4. Cart disimpan oleh package `hardevine/shoppingcart`.
5. Pengguna mengirim order.
6. `OrderController@storeOrder` membuat record `orders` dengan status `pending`.
7. `OrderController@storeOrder` membuat record `order_details`.
8. Cart dikosongkan.
9. Invoice ditampilkan.
10. Pengguna membuka detail order tertunda.
11. Pengguna klik `Complete Order`.
12. `OrderController@updateStatus` mengurangi `products.stock`.
13. Status order berubah menjadi `complete`.

## Aturan Tetap Alur POS

Aturan tetap adalah aturan yang harus selalu benar:

- Order tertunda belum dianggap penjualan akhir.
- Order selesai dianggap penjualan akhir.
- Stok tidak boleh dikurangi dua kali untuk order yang sama.
- Jika order selesai divoid, stok harus dikembalikan satu kali.
- Invoice number harus tetap unik.
- Order detail harus tetap menyimpan item yang dijual walaupun produk berubah nama/harga setelah transaksi.
- Pembayaran piutang tidak boleh membuat `due_amount` negatif.
- Cart pengguna tidak boleh mempengaruhi cart pengguna lain.

Jika implementasi baru melanggar aturan tetap ini, jangan lanjut sebelum diperbaiki.

## Struktur Yang Disarankan Untuk Fitur Baru

Gunakan pola berikut supaya konsisten dengan project:

- Controller: `app/Http/Controllers/Dashboard/NamaFiturController.php`
- Model: `app/Models/NamaModel.php`
- Request validation: `app/Http/Requests/NamaFitur/StoreNamaRequest.php`
- Views: `resources/views/nama-fitur/*.blade.php`
- Migration: `database/migrations/YYYY_MM_DD_HHMMSS_create_nama_table.php`
- Route: tambah group di `routes/web.php` dengan middleware permission.
- Sidebar: tambah menu di `resources/views/dashboard/body/sidebar.blade.php`.
- Permission: tambah permission baru via seeder atau UI Role & Permission.

## Template Modul Baru

Saat membuat modul baru, ikuti urutan ini:

1. Migration tabel utama.
2. Model dan relasi.
3. Form Request untuk validasi input.
4. Controller resource/custom action.
5. Route dengan middleware permission.
6. View index/create/show/edit sesuai kebutuhan.
7. Sidebar menu.
8. Seeder permission jika perlu.
9. Update docs.

Nama file harus deskriptif. Contoh untuk stock movement:

- `app/Models/StockMovement.php`
- `app/Http/Controllers/Dashboard/StockMovementController.php`
- `resources/views/stock-movements/index.blade.php`
- `database/migrations/..._create_stock_movements_table.php`

Jangan membuat helper global besar jika logic masih bisa ditempatkan di service kecil atau method model/controller yang jelas.

## Kandidat Tabel Baru

Untuk phase berikutnya, tabel yang kemungkinan dibutuhkan:

- `audit_logs`
- `stock_movements`
- `purchase_orders`
- `purchase_order_details`
- `purchase_receivings`
- `purchase_returns`
- `cash_shifts`
- `cash_movements`
- `daily_closings`
- `order_payments`
- `stock_opnames`
- `stock_opname_details`
- `sales_returns`
- `sales_return_details`
- `promotions`
- `vouchers`
- `store_settings`

## Sketsa Relasi Fitur Baru

### Audit Log

- `audit_logs.user_id` relasi ke `users.id`.
- Simpan `module`, `action`, `reference_type`, `reference_id`, `old_values`, `new_values`, `ip_address`, `user_agent`.

### Stock Movement

- `stock_movements.product_id` relasi ke `products.id`.
- `stock_movements.user_id` relasi ke `users.id`.
- `type`: `in`, `out`, `adjustment`, `return_in`, `return_out`, `transfer_in`, `transfer_out`.
- `quantity`: jumlah perubahan.
- `stock_before` dan `stock_after`: snapshot.
- `reference_type` dan `reference_id`: sumber perubahan.
- `notes`: alasan.

### Purchase

- `purchase_orders.supplier_id` relasi ke `suppliers.id`.
- `purchase_order_details.purchase_order_id` relasi ke header.
- Receiving harus bisa mencatat barang diterima sebagian atau penuh.

### Shift dan Closing

- `cash_shifts.user_id` relasi kasir.
- Status shift: `open`, `closed`.
- `cash_movements.cash_shift_id` relasi shift.
- `daily_closings` merekap shift per tanggal.

### Stock Opname

- `stock_opnames` sebagai batch/header.
- `stock_opname_details` menyimpan stok sistem, stok fisik, dan selisih.
- Approval opname membuat stock movement.

### Retur Penjualan

- `sales_returns.order_id` relasi order asal.
- `sales_return_details.product_id` relasi produk.
- Retur bisa `refund`, `exchange`, atau `store_credit` jika nanti dibutuhkan.

## Catatan Untuk AI/Developer Berikutnya

- Baca dokumen berurutan: `01-PRD`, `02-PROGRESS`, `03-TODO`, `04-HISTORY`, `05-PROJECT_STRUCTURE`.
- Kerjakan item dari `03-TODO.md` secara berurutan.
- Perbarui checklist dan riwayat setiap selesai fitur.
- Jangan mengubah dependency besar tanpa alasan teknis kuat.
- Jalankan Laravel dengan `C:\php\php.exe artisan serve --host=127.0.0.1 --port=8084` jika server mati.
- Composer lokal: `C:\php\php.exe composer.phar install`.

## Checklist Sebelum Commit/Selesai

- `git status --short` dicek.
- File docs diperbarui jika pekerjaan mengubah status/arah project.
- Migration baru tidak destructive.
- Route baru punya middleware yang tepat.
- View baru memakai layout dashboard yang ada.
- Query laporan memakai status order yang benar.
- Stok/kas/transaksi penting punya riwayat.
- Tidak ada fitur yang ada yang sengaja dihapus.
