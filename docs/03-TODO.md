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
- [ ] Jalankan script repair pada PC konsumen dan verifikasi route `/database/backup` tidak diblokir aturan URI Nginx atau `try_files`; akses tetap dijaga middleware `database.menu`.
- [x] Migration additive memastikan role `SuperAdmin`, `Admin`, dan `Manager` mendapat `database.menu` tanpa membuka permission restore.
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

## QA Full Aplikasi (2026-09-19)

- [x] Audit menu representative dengan Laravel HTTP-kernel harness: 52/52 tanpa HTTP 500.
- [x] Audit format Rupiah, formula total/kembalian/piutang, timezone `Asia/Singapore`, receipt, invoice visual/PDF, dan WhatsApp.
- [x] Regression test barcode kamera POS/form produk, Google Maps, branding toko, detail invoice, dan pesan WhatsApp.
- [x] Tolak quick-add barcode untuk produk expired.
- [x] Lindungi histori order: customer dengan order tidak dapat dihapus.
- [x] Token invoice publik malformed ditangani sebagai 404 tanpa stack trace.
- [x] Audit verb route: aksi WhatsApp invoice/struk memakai POST+CSRF; GET PDF invoice hanya read-only dan tidak membuat atau mengubah metadata invoice.
- [x] Full suite, PHP lint, Blade cache, frontend build, route list, migration status, dan integrity check lulus.
- [ ] Uji browser authenticated end-to-end memakai login vault; saat ini BLOCKED karena vault belum memiliki credential localhost.
- [ ] Pindahkan test suite ke DB testing disposable/SQLite agar test tidak menambah user Faker ke DB MySQL lokal.

## Invoice PDF Online + tmp0.cc + WhatsApp Manual (2026-09-18)

- [x] Audit dokumentasi, order, order details, customer, payment, invoice lama, PDF, konfigurasi, route, dan WhatsApp existing.
- [x] Verifikasi API tmp0.cc: endpoint, multipart `file`, `expires=30d`, response file document, dan URL `/d/{id}`.
- [x] Tambah PDF invoice A4 server-side memakai library PDF.
- [x] Tambah metadata invoice nullable secara additive pada `orders`.
- [x] Setelah pembayaran dikonfirmasi, order tersimpan dan struk thermal dibuka lebih dahulu; order boleh tetap `pending` dan invoice belum diproses.
- [x] Tombol `Kirim WhatsApp + Invoice PDF` pada halaman struk menjalankan generate/upload tmp0.cc lalu membuka WhatsApp manual.
- [x] Upload tmp0.cc tidak rollback transaksi, dengan timeout dan validasi error.
- [x] Tombol Generate Invoice, Upload Invoice, retry, dan Buka Invoice.
- [x] Tombol Salin Link memakai Clipboard API dan fallback browser.
- [x] WhatsApp invoice memakai `api.whatsapp.com/send` manual dengan pesan dinamis dan normalisasi nomor.
- [x] Lepas pemakaian WhatsApp Cloud API untuk invoice tanpa menghapus migration/tabel/data lama.
- [x] Unit test normalisasi nomor, pesan WhatsApp, dan response upload tmp0.cc.
- [x] Verifikasi upload dummy PDF melalui runtime web aktif Nginx/PHP-CGI pada port `8082`.
- [x] Receipt menyediakan form POST+CSRF aktif WhatsApp teks dan WhatsApp + Invoice PDF; submit PDF membuat/reuse PDF, upload tmp0.cc bila perlu, lalu membuka `api.whatsapp.com/send` dengan link invoice. Error tidak kembali ke print receipt. GET PDF hanya menyajikan file lokal yang sudah tersedia.
- [x] Verifikasi route invoice WhatsApp memakai ID order dinamis; pembayaran terkonfirmasi boleh diproses walau order pending, order batal/void ditolak.
- [ ] Uji browser end-to-end pada order nyata dengan internet ON/OFF.
- [ ] Uji handoff PC konsumen setelah `git pull` dan `migrate --force`.

## HTTPS Lokal dan Kamera Barcode (2026-09-20)

- [x] Aktifkan vhost HTTPS POS3 pada port `8443` dengan sertifikat lokal.
- [x] Pertahankan HTTP `8082` sebagai entry point yang mengarah ke HTTPS.
- [x] Sertifikat memiliki SAN untuk `localhost`, loopback, dan IP LAN PC saat ini.
- [x] Tambahkan tutorial trust certificate lokal dan win-acme untuk domain publik di `README.md`.
- [x] Validasi service Nginx, port HTTPS, response Laravel, Nginx config, test POS, Blade cache, dan frontend build.
- [x] Verifikasi browser membuka `https://localhost:8443/login` dalam secure context dengan `getUserMedia` tersedia.
- [ ] Uji login browser authenticated, tampilan POS, dan izin kamera fisik; credential vault localhost belum tersedia.
- [ ] Uji transaksi POS nyata melalui browser.
- [ ] Uji handoff ke PC konsumen dan trust certificate pada perangkat client.

## Tambahan Lama - WhatsApp Invoice Otomatis (historis, dinonaktifkan)

- [x] Migration dan tabel konfigurasi/log lama dipertahankan untuk kompatibilitas data.
- [x] Invoice mobile lama tetap tersedia pada `/e-invoice-mobile/{token}`.
- [x] Pengiriman Cloud API tidak lagi dipakai untuk invoice; fitur sekarang click-to-chat manual.

## Bugfix Tambahan - Produk

- [x] Hapus produk permanen dengan snapshot `product_references`; histori transaksi, pembelian, stok, dan retur tetap tersimpan. Row legacy soft-deleted tidak disentuh migration.
- [x] Create/update produk menangani collision unique kode setelah validasi dan mengembalikan error field, bukan HTTP 500.
- [x] Bulk pilih produk di halaman index: pilih halaman ini, pilih semua hasil filter lintas pagination, highlight baris terpilih, dan tampilkan jumlah produk terpilih.
- [x] POS memakai pelanggan default `Walk-in Customer` untuk checkout cepat.
- [x] Audit relasi histori inventaris setelah produk dihapus permanen dan arahkan relasi ke snapshot produk.
- [x] Tolak penghapusan produk yang masih dipakai order/purchase/retur/transfer/opname berstatus pending atau aktif.
- [x] Sidebar auto fokus ke menu aktif setelah refresh dan pencarian menu sticky.
- [x] Voucher POS dihitung realtime sebelum checkout dan total pembayaran langsung berubah.
- [x] Kurang bayar POS masuk ke piutang dan pembayaran piutang `/update/due` tidak error method.
- [x] Riwayat pembayaran piutang tampil di daftar order, detail order, faktur, dan struk.
- [x] Halaman Update Web lokal 1 klik dengan log proses update.
- [x] README instalasi lokal Windows lengkap untuk PC konsumen.
- [x] Composer install compatible PHP 8.5 dan instruksi NPM PowerShell memakai `npm.cmd`.
- [x] Deprecated notice `PDO::MYSQL_ATTR_SSL_CA` pada PHP 8.5 dihilangkan.
- [x] README memakai folder baku `C:\Server\Installer`, `C:\Server`, dan `D:\Project\Web\pos3`.
- [x] Runtime PC konsumen memakai folder tetap tanpa versi: `C:\Server\nginx`, `C:\Server\nssm`, dan `C:\Server\php`.
- [x] Permission `settings.menu` ikut dibuat oleh migration/db:seed agar menu Update Web tampil.
- [x] Link darurat update web tanpa sidebar: `/update-web.test` dan `/update-web.start`.
- [x] Menu sidebar Update Web tampil untuk admin meski `settings.menu` belum tersinkron.
- [x] Halaman Update Web menampilkan progress bar/persentase dan status langkah.
- [x] Log Update Web realtime dan halaman reload otomatis setelah sukses.
- [x] Dokumentasi aturan aman data Update Web dicatat di `docs/06-UPDATE_WEB.md`.
- [x] Halaman hapus akun profil bisa dibuka tanpa error missing partial.

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
