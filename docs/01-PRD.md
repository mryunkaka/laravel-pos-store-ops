# 01 - PRD

## Ringkasan Produk

Project ini adalah aplikasi Point of Sale berbasis Laravel 10 untuk operasional toko: master produk, pelanggan, pemasok, transaksi POS, invoice, order, piutang, role/permission, karyawan, gaji, absensi, dashboard, dan backup database.

Target pengembangan berikutnya adalah menjadikan aplikasi ini siap dipakai operasional harian toko dengan kontrol stok, kasir, pembelian, retur, laporan, audit, dan closing yang lebih lengkap.

Dokumen ini adalah sumber konteks utama untuk AI/developer yang melanjutkan project. Tujuannya bukan mengganti arah project, tetapi menjaga agar semua perubahan berikutnya tetap mengikuti struktur awal Laravel POS yang sudah ada. Jangan menafsirkan ulang project ini sebagai penulisan ulang, migrasi ke framework lain, starter kit baru, atau aplikasi POS baru dari nol.

## Instruksi Wajib Untuk AI/Developer

Bagian ini wajib dibaca sebelum membuat perubahan kode:

- Jangan merombak struktur project awal.
- Jangan mengganti Laravel, Blade, Bootstrap/Tailwind yang sudah ada, package POS cart, atau pola controller/view yang sedang dipakai.
- Jangan membuat frontend SPA baru seperti React/Vue/Inertia kecuali ada instruksi eksplisit dari pemilik project.
- Jangan mengganti database engine atau koneksi lokal tanpa instruksi eksplisit dari pemilik.
- Jangan menghapus fitur lama untuk membuat fitur baru.
- Jangan mengubah alur login, role/permission, atau route utama jika tidak dibutuhkan oleh item TODO aktif.
- Jangan melakukan perombakan besar saat mengerjakan fitur kecil.
- Jangan mengganti nama tabel, model, controller, route, atau view yang sudah ada jika masih bisa dikembangkan.
- Jangan menghapus migration lama. Jika butuh perubahan skema, buat migration baru.
- Jangan mengubah data seed default login `admin/password` kecuali ada permintaan eksplisit.
- Jangan lanjut ke phase berikutnya sebelum phase aktif selesai.
- Jika ada kebutuhan yang tidak jelas, baca semua file di `docs/` dulu sebelum membuat asumsi.
- Jika tetap ambigu setelah membaca docs dan kode, tulis asumsi di `02-PROGRESS.md` sebelum implementasi.

## Sumber Kebenaran

Urutan sumber kebenaran saat bekerja:

1. Kode yang sedang ada di repository.
2. Dokumen `docs/01-PRD.md` sampai `docs/05-PROJECT_STRUCTURE.md`.
3. Checklist aktif di `docs/03-TODO.md`.
4. Catatan progres dan riwayat.
5. Baru setelah itu praktik terbaik umum Laravel/POS.

Jika ada konflik antara praktik terbaik umum dan struktur project ini, ikuti struktur project ini selama tidak menimbulkan bug serius.

## Kondisi Lokal

- Project path: `D:\Project\Web\pos3`
- URL lokal: `http://127.0.0.1:8084`
- PHP runtime yang dipakai: `C:\php\php.exe`
- Database: MariaDB `127.0.0.1:3307`
- Nama database: `point_of_sale`
- Login default: username `admin`, password `password`

## Lingkup Produk

Produk ini adalah POS web lokal/hosted untuk toko kecil sampai menengah. Aplikasi tidak sedang diarahkan menjadi marketplace, ERP penuh, aplikasi mobile native, sistem akuntansi lengkap, atau SaaS multi-tenant kompleks.

Yang termasuk lingkup:

- Penjualan kasir.
- Manajemen produk dan stok.
- Pelanggan dan pemasok.
- Pembelian dari pemasok.
- Retur.
- Shift dan tutup kasir.
- Laporan operasional toko.
- Role/permission internal.
- Backup/restore database.
- Audit aktivitas penting.

Yang tidak termasuk lingkup saat ini:

- Pembayaran dari gateway produksi.
- Integrasi e-commerce marketplace.
- Aplikasi mobile Android/iOS native.
- Multi perusahaan SaaS dengan sistem langganan.
- Akuntansi double-entry lengkap.
- Integrasi perangkat keras printer/scanner yang sangat spesifik vendor, kecuali alur kerja browser sederhana.

## Persona Pengguna

- Admin/Pemilik: melihat laporan, mengelola user, role, produk, harga, stok, dan backup.
- Kasir: menjalankan transaksi POS, cetak struk, menerima pembayaran, dan menutup shift.
- Gudang/Inventaris: menerima barang, koreksi stok, stock opname, transfer stok, dan retur pemasok.
- Supervisor: persetujuan void, diskon khusus, penyesuaian stok, tutup kasir, dan laporan.

## Hak Akses Konseptual

Gunakan permission dari Spatie Permission yang sudah ada. Jangan membuat sistem role baru.

- Admin/Pemilik boleh mengakses semua modul.
- Supervisor boleh menyetujui void, penyesuaian stok, stock opname, tutup kasir, dan melihat laporan.
- Kasir hanya boleh transaksi POS, melihat transaksi sendiri, membuka/menutup shift sendiri, dan cetak struk.
- Gudang boleh mengelola pembelian, penerimaan barang, pergerakan stok, stock opname, dan retur pemasok.
- Staf biasa hanya boleh modul yang diberikan lewat role/permission.

Jika menambah modul baru, tambahkan permission baru yang jelas seperti `stock-movement.menu`, `purchase.menu`, `cash-shift.menu`, `closing.menu`, `stock-opname.menu`, `report.menu`, atau nama lain yang konsisten.

## Fitur Saat Ini

- Otentikasi login/register bawaan Laravel Breeze.
- Dashboard ringkas: total bayar, total piutang, order selesai, order tertunda, penjualan hari ini, order terbaru, produk terlaris, grafik penjualan bulanan.
- POS: pilih produk, cart, ubah jumlah, hapus item, pilih/buat pelanggan, input metode bayar dan jumlah bayar, buat invoice.
- Order: order tertunda, order selesai, detail order, cetak invoice/struk, piutang tertunda, bayar piutang.
- Produk: CRUD produk, kategori, unggah gambar, tampilan barcode, impor Excel, ekspor Excel.
- Pelanggan: CRUD pelanggan.
- Pemasok: CRUD pemasok.
- Karyawan: CRUD karyawan.
- Absensi: input dan daftar absensi.
- Gaji: gaji di muka, bayar gaji, bayar semua, riwayat bayar gaji.
- Role & Permission: permission, role, penetapan role permission.
- Pengguna: CRUD pengguna.
- Backup Database: daftar backup, buat, unduh, hapus.

## Detail Alur Saat Ini

### Login

Pengguna membuka `/login`, masuk memakai username dan password. Setelah sukses, pengguna diarahkan ke dashboard.

### Dashboard

Dashboard membaca data order untuk menampilkan ringkasan. Saat ini dashboard belum sepenuhnya memisahkan order tertunda dan selesai dalam seluruh metrik. Saat mengembangkan laporan, pastikan definisi penjualan jelas: penjualan operasional sebaiknya dihitung dari order `complete`, bukan semua order.

### POS

POS menampilkan produk yang tanggal kedaluwarsa-nya lebih besar dari hari ini. Produk bisa ditambahkan ke cart, jumlah diubah, dan item dihapus. Pelanggan bisa dipilih atau dibuat lewat AJAX. Setelah submit, sistem membuat order dan detail order.

### Order

Order baru dari POS berstatus `pending`. Stok belum dikurangi saat order dibuat. Stok dikurangi saat pengguna membuka detail order tertunda dan klik `Complete Order`.

### Due/Piutang

Jika total order lebih besar dari nilai bayar, selisih masuk ke `due_amount`. Menu Piutang Tertunda menampilkan order yang masih punya sisa bayar. Pembayaran piutang mengurangi `due_amount` dan menambah `pay_amount`.

### Produk dan Stok

Produk punya kolom `stock`, tetapi belum punya riwayat mutasi. Saat ini perbarui stok manual dilakukan lewat edit produk/impor produk, dan pengurangan stok dari order selesai langsung mengubah angka di `products.stock`.

## Kesenjangan Utama

- Stok belum divalidasi ketat saat tambah cart/ubah jumlah.
- Stok baru berkurang saat order diselesaikan, bukan saat checkout POS.
- Belum ada pembelian dari pemasok.
- Belum ada kartu stok/riwayat mutasi stok.
- Belum ada stock opname resmi.
- Belum ada shift kasir dan tutup kasir harian.
- Belum ada retur penjualan/pembelian.
- Belum ada laporan operasional lengkap dan laba kotor.
- Belum ada audit log untuk aktivitas penting.

## Masalah Yang Tidak Boleh Diabaikan

- Jangan menambah fitur pelaporan sebelum memperbaiki definisi status order dan validasi stok, karena laporan akan menghitung data yang belum aman.
- Jangan membuat pembelian/penerimaan tanpa pergerakan stok, karena stok akan berubah tanpa riwayat.
- Jangan membuat stock opname hanya dengan memperbarui `products.stock`, karena harus ada batch, detail, selisih, persetujuan, dan riwayat.
- Jangan membuat tutup kasir hanya dengan tampilan dashboard, karena tutup kasir harus menyimpan cuplikan kas/transaksi.
- Jangan membuat void hanya menghapus order, karena void harus menyimpan alasan dan riwayat.
- Jangan menghapus order selesai untuk koreksi. Gunakan void/retur.

## Target Arsitektur Bisnis

Target akhir setelah semua phase selesai:

- Order selesai menjadi sumber transaksi penjualan valid.
- Setiap perubahan stok tercatat di `stock_movements`.
- Pembelian pemasok menambah stok lewat penerimaan barang, bukan edit produk manual.
- Retur penjualan dan retur pembelian membuat pergerakan stok yang jelas.
- Kasir bekerja dalam shift aktif.
- Tutup shift menyimpan cuplikan uang kas dan transaksi.
- Tutup kasir harian menyimpan rekap shift.
- Audit log menyimpan aktivitas penting.
- Laporan membaca data yang sudah berstatus, bukan data mentah yang ambigu.

## Prinsip Pengembangan

- Phase harus dikerjakan berurutan. Jangan lanjut ke phase berikutnya jika checklist phase sebelumnya belum selesai.
- Prioritaskan fitur yang mencegah data salah: validasi stok, riwayat stok, status transaksi, audit.
- Hindari perubahan besar tanpa migrasi dan pengujian minimal.
- Tambahkan modul mengikuti pola Laravel yang sudah ada: Controller di `app/Http/Controllers/Dashboard`, Model di `app/Models`, view di `resources/views`, route di `routes/web.php`.
- Setiap fitur operasional penting harus punya riwayat, status, pengguna pembuat, dan waktu pencatatan.

## Batas Perubahan Teknis

Perubahan yang diperbolehkan:

- Menambah migration baru.
- Menambah model/controller/request/view baru mengikuti folder yang sudah ada.
- Menambah route baru di `routes/web.php`.
- Menambah menu baru di sidebar yang sudah ada.
- Menambah permission baru lewat seeder atau UI permission.
- Memperbaiki query yang salah pada controller yang ada.
- Menambah kolom baru pada tabel yang ada jika memang dibutuhkan dan dilakukan lewat migration baru.

Perubahan yang harus dihindari:

- Mengubah nama massal folder/file.
- Memindahkan controller yang ada ke namespace lain.
- Mengganti layout Blade utama.
- Menghapus aset Bootstrap/Tailwind yang ada.
- Mengubah stack otentikasi.
- Mengganti package cart tanpa alasan kritis.
- Mengubah semua mata uang secara menyeluruh tanpa pengaturan yang jelas.
- Menghapus fitur gaji/absensi walaupun bukan fitur POS utama.

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

Untuk status, gunakan enum string sederhana terlebih dahulu agar konsisten dengan gaya project saat ini. Contoh: `draft`, `pending`, `approved`, `completed`, `cancelled`, `void`.

## Standar UI

- Ikuti layout `resources/views/dashboard/body/main.blade.php`.
- Tambahkan menu di sidebar hanya jika fitur sudah punya halaman index.
- Gunakan pola tabel, notifikasi, pagination, tombol, dan card yang sudah dipakai di modul yang ada.
- Jangan membuat desain dashboard baru yang tidak konsisten.
- Jangan menyisipkan dokumentasi panjang di halaman aplikasi. Dokumentasi teknis cukup di folder `docs`.
- Untuk fitur baru, sediakan minimal halaman index dan buat/detail jika dibutuhkan.

## Definisi Selesai

Satu fitur dianggap selesai jika:

- Route, controller, model, migration, request validation, dan view sudah tersedia sesuai kebutuhan.
- Permission/menu sudah ditambahkan jika fitur perlu dibatasi.
- Ada alur UI yang bisa dipakai pengguna non-teknis.
- Data tidak hanya memperbarui angka akhir, tapi menyimpan riwayat jika fitur menyangkut stok/kas/transaksi.
- Minimal diuji manual dari browser dan perintah Laravel terkait tidak error.

## Definisi Tidak Selesai

Fitur belum boleh dicentang jika:

- Baru migration dibuat tapi belum ada UI/alur.
- Baru UI dibuat tapi data belum tersimpan benar.
- Baru data tersimpan tapi tidak ada validasi.
- Baru angka stok/kas berubah tapi histori belum tercatat.
- Ada error di browser atau log Laravel.
- Permission/menu belum dipasang untuk user yang tepat.
- Dokumentasi progress dan riwayat belum diperbarui.

## Cara Melanjutkan Jika Konteks AI Terpotong

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
