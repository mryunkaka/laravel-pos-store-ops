# 01 - PRD

## Ringkasan Produk

Project ini adalah aplikasi Point of Sale berbasis Laravel 10 untuk operasional toko: master produk, customer, supplier, transaksi POS, invoice, order, piutang, role/permission, karyawan, salary, attendance, dashboard, dan backup database.

Target pengembangan berikutnya adalah menjadikan aplikasi ini siap dipakai operasional harian toko dengan kontrol stok, kasir, pembelian, retur, laporan, audit, dan closing yang lebih lengkap.

Dokumen ini adalah sumber konteks utama untuk AI/developer yang melanjutkan project. Tujuannya bukan mengganti arah project, tetapi menjaga agar semua perubahan berikutnya tetap mengikuti struktur awal Laravel POS yang sudah ada. Jangan menafsirkan ulang project ini sebagai rewrite, migration ke framework lain, starter kit baru, atau aplikasi POS baru dari nol.

## Instruksi Wajib Untuk AI/Developer

Bagian ini wajib dibaca sebelum membuat perubahan kode:

- Jangan merombak struktur project awal.
- Jangan mengganti Laravel, Blade, Bootstrap/Tailwind yang sudah ada, package POS cart, atau pola controller/view yang sedang dipakai.
- Jangan membuat frontend SPA baru seperti React/Vue/Inertia kecuali ada instruksi eksplisit dari owner project.
- Jangan mengganti database engine atau koneksi lokal tanpa instruksi eksplisit.
- Jangan menghapus fitur lama untuk membuat fitur baru.
- Jangan mengubah flow login, role/permission, atau route utama jika tidak dibutuhkan oleh item TODO aktif.
- Jangan melakukan refactor besar saat mengerjakan fitur kecil.
- Jangan mengganti nama tabel, model, controller, route, atau view yang sudah ada jika masih bisa di-extend.
- Jangan menghapus migration lama. Jika butuh perubahan schema, buat migration baru.
- Jangan mengubah data seed default login `admin/password` kecuali ada permintaan eksplisit.
- Jangan lanjut ke phase berikutnya sebelum phase aktif selesai.
- Jika ada kebutuhan yang tidak jelas, baca semua file di `docs/` dulu sebelum membuat asumsi.
- Jika tetap ambigu setelah membaca docs dan kode, tulis asumsi di `02-PROGRESS.md` sebelum implementasi.

## Sumber Kebenaran

Urutan sumber kebenaran saat bekerja:

1. Kode yang sedang ada di repository.
2. Dokumen `docs/01-PRD.md` sampai `docs/05-PROJECT_STRUCTURE.md`.
3. Checklist aktif di `docs/03-TODO.md`.
4. Catatan progress dan history.
5. Baru setelah itu best practice umum Laravel/POS.

Jika ada konflik antara best practice umum dan struktur project ini, ikuti struktur project ini selama tidak menimbulkan bug serius.

## Kondisi Lokal

- Project path: `D:\Project\Web\pos3`
- URL lokal: `http://127.0.0.1:8084`
- PHP runtime yang dipakai: `C:\php\php.exe`
- Database: MariaDB `127.0.0.1:3307`
- Database name: `point_of_sale`
- Login default: username `admin`, password `password`

## Lingkup Produk

Produk ini adalah POS web lokal/hosted untuk toko kecil sampai menengah. Aplikasi tidak sedang diarahkan menjadi marketplace, ERP penuh, aplikasi mobile native, sistem akuntansi lengkap, atau SaaS multi-tenant kompleks.

Yang termasuk lingkup:

- Penjualan kasir.
- Manajemen produk dan stok.
- Customer dan supplier.
- Pembelian dari supplier.
- Retur.
- Shift dan closing kasir.
- Laporan operasional toko.
- Role/permission internal.
- Backup/restore database.
- Audit aktivitas penting.

Yang tidak termasuk lingkup saat ini:

- Integrasi payment gateway production.
- Integrasi e-commerce marketplace.
- Aplikasi mobile Android/iOS native.
- Multi company SaaS dengan billing subscription.
- Akuntansi double-entry lengkap.
- Integrasi hardware printer/scanner yang sangat spesifik vendor, kecuali workflow browser sederhana.

## Persona Pengguna

- Admin/Owner: melihat laporan, mengelola user, role, produk, harga, stok, dan backup.
- Kasir: menjalankan transaksi POS, cetak struk, menerima pembayaran, dan menutup shift.
- Gudang/Inventory: menerima barang, koreksi stok, stock opname, transfer stok, dan retur supplier.
- Supervisor: approval void, diskon khusus, adjustment stok, closing, dan laporan.

## Hak Akses Konseptual

Gunakan permission dari Spatie Permission yang sudah ada. Jangan membuat sistem role baru.

- Admin/Owner boleh mengakses semua modul.
- Supervisor boleh approve void, stock adjustment, stock opname, closing, dan melihat laporan.
- Kasir hanya boleh transaksi POS, melihat transaksi sendiri, membuka/menutup shift sendiri, dan cetak struk.
- Gudang boleh mengelola purchase, receiving, stock movement, stock opname, dan retur supplier.
- Staff biasa hanya boleh modul yang diberikan lewat role/permission.

Jika menambah modul baru, tambahkan permission baru yang jelas seperti `stock-movement.menu`, `purchase.menu`, `cash-shift.menu`, `closing.menu`, `stock-opname.menu`, `report.menu`, atau nama lain yang konsisten.

## Fitur Saat Ini

- Authentication login/register bawaan Laravel Breeze.
- Dashboard ringkas: total paid, total due, complete orders, pending orders, today sales, recent orders, top products, chart monthly sales.
- POS: pilih produk, cart, update qty, hapus item, pilih/buat customer, input payment type dan pay amount, buat invoice.
- Orders: pending orders, complete orders, detail order, print invoice/receipt, pending due, update due.
- Products: CRUD produk, kategori, upload gambar, barcode display, import Excel, export Excel.
- Customers: CRUD customer.
- Suppliers: CRUD supplier.
- Employees: CRUD employee.
- Attendance: input dan daftar attendance.
- Salary: advance salary, pay salary, pay all, history pay salary.
- Role & Permission: permission, role, assign role permission.
- Users: CRUD user.
- Backup Database: daftar backup, create, download, delete.

## Detail Alur Saat Ini

### Login

User membuka `/login`, masuk memakai username dan password. Setelah sukses, user diarahkan ke dashboard.

### Dashboard

Dashboard membaca data order untuk menampilkan ringkasan. Saat ini dashboard belum sepenuhnya memisahkan order pending dan complete dalam seluruh metric. Saat mengembangkan report, pastikan definisi sales jelas: sales operasional sebaiknya dihitung dari order `complete`, bukan semua order.

### POS

POS menampilkan produk yang tanggal expired-nya lebih besar dari hari ini. Produk bisa ditambahkan ke cart, qty diubah, dan item dihapus. Customer bisa dipilih atau dibuat lewat AJAX. Setelah submit, sistem membuat order dan order detail.

### Order

Order baru dari POS berstatus `pending`. Stok belum dikurangi saat order dibuat. Stok dikurangi saat user membuka detail pending order dan klik `Complete Order`.

### Due/Piutang

Jika total order lebih besar dari nilai bayar, selisih masuk ke `due_amount`. Menu Pending Due menampilkan order yang masih punya sisa bayar. Pembayaran due mengurangi `due_amount` dan menambah `pay_amount`.

### Produk dan Stok

Produk punya field `stock`, tetapi belum punya histori mutasi. Saat ini update stok manual dilakukan lewat edit product/import product, dan pengurangan stok dari order complete langsung mengubah angka di `products.stock`.

## Gap Utama

- Stok belum divalidasi ketat saat add cart/update qty.
- Stok baru berkurang saat order di-complete, bukan saat checkout POS.
- Belum ada purchase/pembelian dari supplier.
- Belum ada kartu stok/histori mutasi stok.
- Belum ada stock opname resmi.
- Belum ada shift kasir dan closing harian.
- Belum ada retur penjualan/pembelian.
- Belum ada laporan operasional lengkap dan laba kotor.
- Belum ada audit log untuk aktivitas penting.

## Masalah Yang Tidak Boleh Diabaikan

- Jangan menambah fitur reporting sebelum memperbaiki definisi status order dan validasi stok, karena report akan menghitung data yang belum aman.
- Jangan membuat purchase/receiving tanpa stock movement, karena stok akan berubah tanpa histori.
- Jangan membuat stock opname hanya dengan update `products.stock`, karena harus ada batch, detail, selisih, approval, dan histori.
- Jangan membuat closing hanya dengan tampilan dashboard, karena closing harus menyimpan snapshot kas/transaksi.
- Jangan membuat void hanya menghapus order, karena void harus menyimpan alasan dan histori.
- Jangan menghapus order completed untuk koreksi. Gunakan void/return.

## Target Arsitektur Bisnis

Target akhir setelah semua phase:

- Order complete menjadi sumber transaksi penjualan valid.
- Setiap perubahan stok tercatat di `stock_movements`.
- Pembelian supplier menambah stok lewat receiving, bukan edit produk manual.
- Retur penjualan dan retur pembelian membuat pergerakan stok yang jelas.
- Kasir bekerja dalam shift aktif.
- Closing shift menyimpan snapshot uang kas dan transaksi.
- Closing harian menyimpan rekap shift.
- Audit log menyimpan aktivitas penting.
- Laporan membaca data yang sudah distatuskan, bukan data mentah yang ambigu.

## Prinsip Pengembangan

- Phase harus dikerjakan berurutan. Jangan lanjut phase berikutnya jika checklist phase sebelumnya belum selesai.
- Prioritaskan fitur yang mencegah data salah: validasi stok, histori stok, status transaksi, audit.
- Hindari perubahan besar tanpa migrasi dan test minimal.
- Tambahkan modul mengikuti pola Laravel yang sudah ada: Controller di `app/Http/Controllers/Dashboard`, Model di `app/Models`, view di `resources/views`, route di `routes/web.php`.
- Setiap fitur operasional penting harus punya histori, status, user pembuat, dan timestamp.

## Batas Perubahan Teknis

Perubahan yang diperbolehkan:

- Menambah migration baru.
- Menambah model/controller/request/view baru mengikuti folder existing.
- Menambah route baru di `routes/web.php`.
- Menambah menu baru di sidebar existing.
- Menambah permission baru lewat seeder atau UI permission.
- Memperbaiki query yang salah pada controller existing.
- Menambah kolom baru pada tabel existing jika memang dibutuhkan dan dilakukan lewat migration baru.

Perubahan yang harus dihindari:

- Rename massal folder/file.
- Memindahkan controller existing ke namespace lain.
- Mengganti Blade layout utama.
- Menghapus Bootstrap/Tailwind asset existing.
- Mengubah auth stack.
- Mengganti package cart tanpa alasan kritis.
- Mengubah semua currency secara sweeping tanpa pengaturan yang jelas.
- Menghapus fitur salary/attendance walaupun bukan fitur POS utama.

## Standar Data Penting

Setiap tabel transaksi baru sebaiknya punya:

- `id`
- nomor dokumen jika relevan
- status
- relasi `user_id` pembuat
- relasi referensi seperti `order_id`, `product_id`, `supplier_id`, atau `customer_id`
- nominal/qty dengan tipe decimal/integer sesuai kebutuhan
- catatan/alasan untuk koreksi
- `created_at` dan `updated_at`

Untuk status, gunakan enum string sederhana terlebih dahulu agar konsisten dengan style project saat ini. Contoh: `draft`, `pending`, `approved`, `completed`, `cancelled`, `void`.

## Standar UI

- Ikuti layout `resources/views/dashboard/body/main.blade.php`.
- Tambahkan menu di sidebar hanya jika fitur sudah punya halaman index.
- Gunakan pola tabel, alert, pagination, tombol, dan card yang sudah dipakai di modul existing.
- Jangan membuat desain dashboard baru yang tidak konsisten.
- Jangan menyisipkan dokumentasi panjang di halaman aplikasi. Dokumentasi teknis cukup di folder `docs`.
- Untuk fitur baru, sediakan minimal halaman index dan create/detail jika dibutuhkan.

## Definisi Selesai

Satu fitur dianggap selesai jika:

- Route, controller, model, migration, request validation, dan view sudah tersedia sesuai kebutuhan.
- Permission/menu sudah ditambahkan jika fitur perlu dibatasi.
- Ada flow UI yang bisa dipakai user non-teknis.
- Data tidak hanya update angka akhir, tapi menyimpan histori jika fitur menyangkut stok/kas/transaksi.
- Minimal diuji manual dari browser dan command Laravel terkait tidak error.

## Definisi Tidak Selesai

Fitur belum boleh dicentang jika:

- Baru migration dibuat tapi belum ada UI/flow.
- Baru UI dibuat tapi data belum tersimpan benar.
- Baru data tersimpan tapi tidak ada validasi.
- Baru angka stok/kas berubah tapi histori belum tercatat.
- Ada error di browser atau log Laravel.
- Permission/menu belum dipasang untuk user yang tepat.
- Dokumentasi progress dan history belum diperbarui.

## Cara Melanjutkan Jika Context AI Terpotong

Jika model AI lain kehilangan konteks:

1. Baca `docs/01-PRD.md`.
2. Baca `docs/02-PROGRESS.md`.
3. Baca `docs/03-TODO.md`.
4. Baca `docs/04-HISTORY.md`.
5. Baca `docs/05-PROJECT_STRUCTURE.md`.
6. Cek `git status --short`.
7. Cek route dan controller terkait item TODO aktif.
8. Kerjakan item checklist paling atas yang belum selesai pada phase aktif.

Jangan langsung coding dari ingatan atau asumsi.
