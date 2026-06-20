# 03 - TODO

Aturan pengerjaan:

- Phase harus dikerjakan berurutan.
- Jangan mulai Phase 2 jika masih ada item Phase 1 yang belum checklist.
- Jangan mulai Phase 3 jika masih ada item Phase 2 yang belum checklist.
- Setelah mengerjakan item, perbarui checklist, catatan di `02-PROGRESS.md`, dan perubahan penting di `04-HISTORY.md`.
- Jangan mengubah struktur project awal untuk mengejar item TODO.
- Jangan mencentang item jika hanya sebagian selesai.
- Jangan menggabungkan banyak item besar dalam satu perubahan jika membuat review sulit.
- Jika menemukan bug terkait item aktif, perbaiki bug itu sebelum lanjut item berikutnya.
- Jika menemukan bug di luar item aktif, catat di `02-PROGRESS.md` sebagai risiko atau TODO tambahan, jangan langsung refactor besar.

## Protokol Kerja Wajib

Sebelum mengerjakan:

1. Baca semua file `docs/*.md`.
2. Cek phase aktif di file ini.
3. Pilih item belum dicentang paling atas pada phase aktif.
4. Baca controller/model/view/route yang terkait.
5. Tulis asumsi singkat di `02-PROGRESS.md` jika ada hal yang belum pasti.

Saat mengerjakan:

1. Buat perubahan kecil dan terarah.
2. Gunakan pola yang sudah ada.
3. Tambahkan migration baru jika perlu skema.
4. Tambahkan validasi request jika input kompleks.
5. Tambahkan permission jika fitur masuk sidebar/menu.
6. Jangan menghapus data/fitur lama.

Setelah mengerjakan:

1. Jalankan perintah validasi yang relevan.
2. Uji manual alur utama.
3. Update checklist item.
4. Update `02-PROGRESS.md`.
5. Update `04-HISTORY.md`.
6. Catat file penting yang diubah.

## Makna Checklist

- `[x]` berarti fitur sudah bisa digunakan dari UI atau flow aplikasi, bukan sekadar kode dibuat.
- `[ ]` berarti belum selesai atau belum diverifikasi.
- Jika fitur sebagian selesai, tetap `[ ]` dan tambahkan catatan progress di `02-PROGRESS.md`.
- Jangan membuat simbol status baru selain `[x]` dan `[ ]`.

## Larangan Implementasi

- Jangan menulis ulang POS menjadi aplikasi baru.
- Jangan mengganti Laravel Blade menjadi SPA.
- Jangan menghapus module salary/attendance/supplier walaupun tidak prioritas.
- Jangan mengubah auth default tanpa instruksi.
- Jangan memindahkan semua route ke file baru.
- Jangan mengganti nama tabel yang ada.
- Jangan membuat migration destructive seperti drop/rename table yang ada tanpa backup dan instruksi eksplisit.
- Jangan mengubah semua tampilan sekaligus.
- Jangan install package besar kecuali benar-benar diperlukan dan dicatat alasannya.

## Phase 1 - Dasar Project Saat Ini

Tujuan: memastikan fitur bawaan project tercatat, bisa dipakai, dan menjadi dasar sebelum peningkatan.

- [x] Login admin tersedia.
- [x] Dashboard ringkas tersedia.
- [x] POS cart tersedia.
- [x] Create order dari POS tersedia.
- [x] Cetak invoice/struk tersedia.
- [x] Pending orders tersedia.
- [x] Complete orders tersedia.
- [x] Pending due dan pembayaran piutang tersedia.
- [x] CRUD products tersedia.
- [x] CRUD categories tersedia.
- [x] Product import Excel tersedia.
- [x] Product export Excel tersedia.
- [x] Product barcode display tersedia.
- [x] CRUD customers tersedia.
- [x] Pemasok: CRUD pemasok.
- [x] Karyawan: CRUD karyawan.
- [x] Absensi tersedia.
- [x] Gaji dan gaji di muka tersedia.
- [x] Role & permission tersedia.
- [x] Manajemen pengguna tersedia.
- [x] Database backup tersedia.
- [x] Instalasi lokal, migration, seeder, storage link, dan server port `8084` selesai.

## Phase 2 - Prioritas Data Aman Untuk POS

Tujuan: mencegah transaksi dan stok menjadi salah. Phase ini paling prioritas sebelum fitur besar lain.

Kriteria penerimaan Phase 2:

- Transaksi tidak bisa menjual jumlah melebihi stok tersedia.
- Order tertunda bisa dibatalkan dengan alasan tanpa menghapus riwayat.
- Order selesai bisa divoid dengan permission supervisor dan alasan.
- Void order selesai mengembalikan stok satu kali saja.
- Dashboard tidak lagi menghitung order tertunda sebagai penjualan akhir.
- Audit log minimal menyimpan pengguna, aksi, modul, referensi ID, nilai lama/nilai baru jika relevan, IP/user agent jika mudah tersedia.

- [x] Validasi stok saat add product ke cart.
- [x] Validasi stok saat update qty cart.
- [x] Validasi stok ulang saat order dibuat.
- [x] Validasi stok ulang saat order di-complete.
- [x] Cegah stok minus kecuali user punya permission khusus.
- [x] Tambahkan status order yang lebih jelas: `pending`, `complete`, `cancelled`, `void`.
- [x] Tambahkan fitur cancel pending order dengan alasan.
- [x] Tambahkan fitur void complete order dengan alasan dan permission supervisor.
- [x] Pastikan void complete order mengembalikan stok.
- [x] Perbaiki dashboard agar sales utama hanya menghitung order `complete`.
- [x] Perbaiki top selling product agar hanya menghitung order `complete`.
- [x] Tambahkan audit log dasar untuk login, create order, complete order, cancel/void order, update due, dan update product.

## Phase 3 - Inti Inventaris

Tujuan: stok tidak hanya angka akhir, tapi punya riwayat lengkap dan sumber perubahan.

Kriteria penerimaan Phase 3:

- Setiap perubahan stok dari order, pembelian, retur, penyesuaian, atau opname masuk ke pergerakan stok.
- Pergerakan stok tidak boleh diedit sembarangan. Koreksi dilakukan dengan pergerakan baru.
- Produk punya halaman riwayat stok.
- Pembelian dari pemasok tidak otomatis menambah stok sebelum penerimaan barang.
- Penerimaan yang sudah selesai tidak boleh diproses dua kali.

- [x] Buat modul pergerakan stok/kartu stok.
- [x] Catat stok keluar otomatis saat order selesai.
- [x] Catat stok masuk manual.
- [x] Catat penyesuaian stok manual dengan alasan.
- [x] Tampilkan riwayat stok per produk.
- [x] Tambahkan filter riwayat stok berdasarkan tanggal, tipe, produk, dan pengguna.
- [x] Buat purchase order dari pemasok.
- [x] Buat penerimaan barang/purchase receiving.
- [x] Saat pembelian diterima, stok produk bertambah dan pergerakan stok tercatat.
- [x] Tambahkan retur pembelian ke pemasok.
- [x] Saat retur pembelian selesai, stok berkurang dan pergerakan stok tercatat.
- [x] Tambahkan transfer stok antar lokasi sebagai struktur awal, walau lokasi default masih satu toko.

## Phase 4 - Operasional Kasir dan Tutup Kasir

Tujuan: kasir bisa buka/tutup shift, uang kas bisa dicocokkan, dan transaksi harian bisa ditutup resmi.

Kriteria penerimaan Phase 4:

- Kasir tidak bisa transaksi tanpa shift aktif jika aturan shift sudah diaktifkan.
- Satu pengguna kasir tidak boleh punya dua shift aktif bersamaan.
- Tutup shift menyimpan cuplikan, bukan hanya hitung langsung.
- Data yang sudah ditutup tidak berubah diam-diam.
- Multi pembayaran menyimpan detail pembayaran, bukan hanya teks `payment_type`.

- [x] Buat modul shift kasir.
- [x] Buka shift dengan kas awal.
- [x] Batasi transaksi POS agar kasir harus punya shift aktif.
- [x] Catat kas masuk/kas keluar selama shift.
- [x] Tambahkan multi pembayaran per order: tunai, QRIS, debit, transfer, e-wallet.
- [x] Tambahkan pembayaran terpisah dalam satu order.
- [x] Buat tutup shift kasir.
- [x] Tutup kasir menghitung total transaksi, total tunai, total non-tunai, piutang, void, dan refund.
- [x] Tutup kasir mencatat uang fisik, selisih kas, catatan kasir, dan pengguna supervisor.
- [x] Buat tutup kasir harian outlet dari kumpulan shift.
- [x] Kunci transaksi yang sudah masuk tutup kasir agar tidak bisa diedit tanpa permission khusus.
- [x] Tambahkan cetak laporan tutup shift dan tutup kasir harian.

## Phase 5 - Stock Opname dan Retur Penjualan

Tujuan: kontrol fisik barang dan penanganan pengembalian barang dari customer.

Kriteria penerimaan Phase 5:

- Stock opname punya batch/header dan detail per produk.
- Opname tidak langsung mengubah stok sebelum persetujuan.
- Hasil persetujuan membuat penyesuaian stok dan pergerakan stok.
- Retur penjualan harus terhubung ke order asal.
- Refund/tukar barang harus tercatat dan mempengaruhi laporan.

- [x] Buat modul stock opname batch.
- [x] Generate daftar produk untuk opname.
- [x] Input stok fisik manual.
- [x] Import hasil opname dari Excel.
- [x] Hitung selisih stok sistem vs stok fisik.
- [x] Submit hasil opname untuk persetujuan.
- [x] Persetujuan opname membuat penyesuaian stok dan pergerakan stok.
- [x] Simpan riwayat opname per batch.
- [x] Buat retur penjualan.
- [x] Retur penjualan bisa refund uang atau tukar barang.
- [x] Retur penjualan mengembalikan stok jika barang layak jual.
- [x] Retur penjualan mencatat barang rusak jika tidak kembali ke stok jual.
- [x] Hubungkan retur dengan invoice/order asal.

## Phase 6 - Harga, Promo, Pajak, dan Barcode

Tujuan: POS lebih fleksibel untuk skenario toko nyata.

Kriteria penerimaan Phase 6:

- Diskon tercatat di order/detail order, bukan hanya perubahan tampilan total.
- Promo punya periode aktif dan bisa dinonaktifkan.
- Pajak/biaya layanan punya konfigurasi dan nilai tersimpan di transaksi.
- Alur barcode scanner bisa menambah item dari input kode.
- Struk thermal punya layout khusus dan tetap bisa dicetak dari browser.

- [x] Diskon per item.
- [x] Diskon per invoice.
- [x] Voucher/promo sederhana berdasarkan periode.
- [x] Harga grosir atau harga pelanggan/member.
- [x] Pajak fleksibel per produk/kategori.
- [x] Biaya layanan opsional.
- [x] Alur kerja barcode scanner di POS: scan kode langsung tambah/ubah jumlah.
- [x] Cetak label barcode produk.
- [x] Optimasi cetak struk thermal 58mm/80mm.
- [x] Cetak otomatis struk setelah order selesai.

## Phase 7 - Laporan, Audit, dan Administrasi Lanjutan

Tujuan: pemilik/supervisor punya laporan dan kontrol administrasi yang cukup.

Kriteria penerimaan Phase 7:

- Laporan bisa difilter tanggal.
- Laporan penjualan akhir memakai order `complete`, memperhitungkan void/retur sesuai kebutuhan.
- Laporan laba memakai harga beli yang tersimpan pada saat transaksi atau cadangan yang jelas.
- Ekspor Excel/PDF menghasilkan data yang sama dengan tampilan.
- Penampil audit log tidak boleh mengizinkan edit log.

- [x] Laporan penjualan per tanggal.
- [x] Laporan penjualan per kasir.
- [x] Laporan penjualan per produk.
- [x] Laporan metode pembayaran.
- [x] Laporan piutang.
- [x] Laporan laba kotor berdasarkan harga beli vs harga jual.
- [x] Laporan stok minimum/pesan ulang.
- [x] Notifikasi produk kedaluwarsa/dekat kedaluwarsa.
- [x] Ekspor laporan ke Excel.
- [x] Ekspor laporan ke PDF.
- [x] Penampil audit log dengan filter pengguna, tanggal, modul, dan aksi.
- [x] Pulihkan database dari backup lewat UI dengan permission khusus.
- [x] Pengaturan toko: nama toko, alamat, logo, pajak default, mata uang.
- [x] Pengaturan role kasir lebih detail: diskon, void, edit harga, akses laporan.

## Phase 8 - Migrasi Bahasa Tampilan ke Indonesia

Tujuan: semua teks tampilan aplikasi (UI) menggunakan bahasa Indonesia, kecuali tombol/teks singkat yang sudah umum dimengerti secara global (Submit, Edit, Delete, Total, Subtotal, dll).

Kriteria penerimaan Phase 8:

- Semua label, judul halaman, deskripsi, pesan sukses/error, dan navigasi di sidebar/navbar menggunakan bahasa Indonesia.
- Tombol singkat seperti Submit, Edit, Delete, Save, Update, Cancel, Total, Subtotal, Qty, dll boleh tetap bahasa Inggris.
- Status badge boleh tetap bahasa Inggris (Pending, Complete, Cancelled, Void).
- Teks placeholder input diterjemahkan.
- Pesan alert dan notifikasi diterjemahkan.
- Nama bulan di chart dan format tanggal disesuaikan.

- [x] Sidebar: semua menu dan submenu diterjemahkan.
- [x] Navbar: placeholder search, label profil, dan teks tombol diterjemahkan.
- [x] Dashboard: judul, label kartu metrik, judul tabel, header kolom, dan teks kosong diterjemahkan.
- [x] POS: label, placeholder, pesan, dan teks cart sidebar diterjemahkan.
- [x] Order: semua halaman (pending, complete, detail, invoice, struk, piutang) diterjemahkan.
- [x] Produk: semua halaman (index, create, edit, show, import) diterjemahkan.
- [x] Kategori: semua halaman (index, create, edit) diterjemahkan.
- [x] Pelanggan: semua halaman (index, create, edit, show) diterjemahkan.
- [x] Pemasok: semua halaman (index, create, edit, show) diterjemahkan.
- [x] Karyawan: semua halaman (index, create, edit, show) diterjemahkan.
- [x] Absensi: semua halaman (index, create, edit) diterjemahkan.
- [x] Gaji: semua halaman gaji di muka dan bayar gaji diterjemahkan.
- [x] Role & Permission: semua halaman diterjemahkan.
- [x] Pengguna: semua halaman (index, create, edit) diterjemahkan.
- [x] Backup database, bantuan, error, dan halaman selamat datang diterjemahkan.
- [x] Otentikasi: halaman login, register, lupa password, dll diterjemahkan.
- [x] Profil: semua halaman profil diterjemahkan.
- [x] Footer dan layout utama diterjemahkan.

## Daftar 20 Fitur Tambahan Utama

Daftar ini adalah ringkasan fitur peningkatan utama yang tersebar dari phase 2 sampai phase 7:

1. Validasi stok POS.
2. Cancel/void transaksi dengan alasan.
3. Audit log.
4. Kartu stok/stock movements.
5. Stock in manual.
6. Stock adjustment.
7. Purchase order.
8. Purchase receiving.
9. Retur pembelian.
10. Transfer stok.
11. Shift kasir.
12. Cash in/cash out.
13. Closing shift.
14. Closing harian.
15. Multi payment.
16. Split payment.
17. Stock opname.
18. Retur penjualan.
19. Diskon/promo.
20. Laporan penjualan dan laba.
