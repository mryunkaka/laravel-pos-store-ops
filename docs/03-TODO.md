# 03 - TODO

Aturan pengerjaan:

- Phase harus dikerjakan berurutan.
- Jangan mulai Phase 2 jika masih ada item Phase 1 yang belum checklist.
- Jangan mulai Phase 3 jika masih ada item Phase 2 yang belum checklist.
- Setelah mengerjakan item, update checklist, catatan di `02-PROGRESS.md`, dan perubahan penting di `04-HISTORY.md`.
- Jangan mengubah struktur project awal untuk mengejar item TODO.
- Jangan mencentang item jika hanya sebagian selesai.
- Jangan menggabungkan banyak item besar dalam satu perubahan jika membuat review sulit.
- Jika menemukan bug terkait item aktif, perbaiki bug itu sebelum lanjut item berikutnya.
- Jika menemukan bug di luar item aktif, catat di `02-PROGRESS.md` sebagai risiko atau TODO tambahan, jangan langsung refactor besar.

## Protokol Kerja Wajib

Sebelum mengerjakan:

1. Baca semua file `docs/*.md`.
2. Cek phase aktif di file ini.
3. Pilih item unchecked paling atas pada phase aktif.
4. Baca controller/model/view/route yang terkait.
5. Tulis asumsi singkat di `02-PROGRESS.md` jika ada hal yang belum pasti.

Saat mengerjakan:

1. Buat perubahan kecil dan terarah.
2. Gunakan pola existing.
3. Tambahkan migration baru jika perlu schema.
4. Tambahkan validation request jika input kompleks.
5. Tambahkan permission jika fitur masuk sidebar/menu.
6. Jangan menghapus data/fitur lama.

Setelah mengerjakan:

1. Jalankan command validasi yang relevan.
2. Test manual flow utama.
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

- Jangan rewrite POS menjadi aplikasi baru.
- Jangan mengganti Laravel Blade menjadi SPA.
- Jangan menghapus module salary/attendance/supplier walaupun tidak prioritas.
- Jangan mengubah auth default tanpa instruksi.
- Jangan memindahkan semua route ke file baru.
- Jangan mengganti nama tabel existing.
- Jangan membuat migration destructive seperti drop/rename table existing tanpa backup dan instruksi eksplisit.
- Jangan mengubah semua tampilan sekaligus.
- Jangan install package besar kecuali benar-benar diperlukan dan dicatat alasannya.

## Phase 1 - Baseline Project Saat Ini

Tujuan: memastikan fitur bawaan project tercatat, bisa dipakai, dan menjadi baseline sebelum enhancement.

- [x] Login admin tersedia.
- [x] Dashboard ringkas tersedia.
- [x] POS cart tersedia.
- [x] Create order dari POS tersedia.
- [x] Print invoice/receipt tersedia.
- [x] Pending orders tersedia.
- [x] Complete orders tersedia.
- [x] Pending due dan pembayaran piutang tersedia.
- [x] CRUD products tersedia.
- [x] CRUD categories tersedia.
- [x] Product import Excel tersedia.
- [x] Product export Excel tersedia.
- [x] Product barcode display tersedia.
- [x] CRUD customers tersedia.
- [x] CRUD suppliers tersedia.
- [x] CRUD employees tersedia.
- [x] Attendance tersedia.
- [x] Salary dan advance salary tersedia.
- [x] Role & permission tersedia.
- [x] Users management tersedia.
- [x] Database backup tersedia.
- [x] Local install, migration, seeder, storage link, dan server port `8084` selesai.

## Phase 2 - Prioritas Data Aman Untuk POS

Tujuan: mencegah transaksi dan stok menjadi salah. Phase ini paling prioritas sebelum fitur besar lain.

Acceptance criteria Phase 2:

- Transaksi tidak bisa menjual qty melebihi stok tersedia.
- Order pending bisa dibatalkan dengan alasan tanpa menghapus histori.
- Order complete bisa divoid dengan permission supervisor dan alasan.
- Void complete order mengembalikan stok satu kali saja.
- Dashboard tidak lagi menghitung pending order sebagai sales final.
- Audit log minimal menyimpan user, action, module, referensi ID, old value/new value jika relevan, IP/user agent jika mudah tersedia.

- [ ] Validasi stok saat add product ke cart.
- [ ] Validasi stok saat update qty cart.
- [ ] Validasi stok ulang saat order dibuat.
- [ ] Validasi stok ulang saat order di-complete.
- [ ] Cegah stok minus kecuali user punya permission khusus.
- [ ] Tambahkan status order yang lebih jelas: `pending`, `complete`, `cancelled`, `void`.
- [ ] Tambahkan fitur cancel pending order dengan alasan.
- [ ] Tambahkan fitur void complete order dengan alasan dan permission supervisor.
- [ ] Pastikan void complete order mengembalikan stok.
- [ ] Perbaiki dashboard agar sales utama hanya menghitung order `complete`.
- [ ] Perbaiki top selling product agar hanya menghitung order `complete`.
- [ ] Tambahkan audit log dasar untuk login, create order, complete order, cancel/void order, update due, dan update product.

## Phase 3 - Inventory Core

Tujuan: stok tidak hanya angka akhir, tapi punya histori lengkap dan sumber perubahan.

Acceptance criteria Phase 3:

- Setiap perubahan stok dari order, purchase, retur, adjustment, atau opname masuk ke stock movement.
- Stock movement tidak boleh diedit sembarangan. Koreksi dilakukan dengan movement baru.
- Produk punya halaman histori stok.
- Purchase dari supplier tidak otomatis menambah stok sebelum receiving.
- Receiving yang sudah selesai tidak boleh diproses dua kali.

- [ ] Buat modul stock movements/kartu stok.
- [ ] Catat stock out otomatis saat order complete.
- [ ] Catat stock in manual.
- [ ] Catat stock adjustment manual dengan alasan.
- [ ] Tampilkan histori stok per produk.
- [ ] Tambahkan filter histori stok berdasarkan tanggal, tipe, produk, dan user.
- [ ] Buat purchase order dari supplier.
- [ ] Buat purchase receiving/penerimaan barang.
- [ ] Saat purchase diterima, stok produk bertambah dan stock movement tercatat.
- [ ] Tambahkan retur pembelian ke supplier.
- [ ] Saat retur pembelian selesai, stok berkurang dan stock movement tercatat.
- [ ] Tambahkan transfer stok antar lokasi sebagai struktur awal, walau default lokasi masih satu toko.

## Phase 4 - Operasional Kasir dan Closing

Tujuan: kasir bisa buka/tutup shift, uang kas bisa dicocokkan, dan transaksi harian bisa ditutup resmi.

Acceptance criteria Phase 4:

- Kasir tidak bisa transaksi tanpa shift aktif jika aturan shift sudah diaktifkan.
- Satu user kasir tidak boleh punya dua shift aktif bersamaan.
- Closing shift menyimpan snapshot, bukan hanya hitung live.
- Data yang sudah closing tidak berubah diam-diam.
- Multi payment menyimpan detail pembayaran, bukan hanya string `payment_type`.

- [ ] Buat modul shift kasir.
- [ ] Buka shift dengan kas awal.
- [ ] Batasi transaksi POS agar kasir harus punya shift aktif.
- [ ] Catat cash in/cash out selama shift.
- [ ] Tambahkan multi payment per order: cash, QRIS, debit, transfer, e-wallet.
- [ ] Tambahkan split payment dalam satu order.
- [ ] Buat closing shift kasir.
- [ ] Closing menghitung total transaksi, total cash, total non-cash, due, void, dan refund.
- [ ] Closing mencatat uang fisik, selisih kas, catatan kasir, dan user supervisor.
- [ ] Buat closing harian outlet dari kumpulan shift.
- [ ] Lock transaksi yang sudah masuk closing agar tidak bisa diedit tanpa permission khusus.
- [ ] Tambahkan cetak laporan closing shift dan closing harian.

## Phase 5 - Stock Opname dan Retur Penjualan

Tujuan: kontrol fisik barang dan penanganan pengembalian barang dari customer.

Acceptance criteria Phase 5:

- Stock opname punya batch/header dan detail per produk.
- Opname tidak langsung mengubah stok sebelum approval.
- Hasil approval membuat stock adjustment dan stock movement.
- Retur penjualan harus terhubung ke order asal.
- Refund/tukar barang harus tercatat dan mempengaruhi laporan.

- [ ] Buat modul stock opname batch.
- [ ] Generate daftar produk untuk opname.
- [ ] Input stok fisik manual.
- [ ] Import hasil opname dari Excel.
- [ ] Hitung selisih stok sistem vs stok fisik.
- [ ] Submit hasil opname untuk approval.
- [ ] Approval opname membuat stock adjustment dan stock movement.
- [ ] Simpan histori opname per batch.
- [ ] Buat retur penjualan.
- [ ] Retur penjualan bisa refund uang atau tukar barang.
- [ ] Retur penjualan mengembalikan stok jika barang layak jual.
- [ ] Retur penjualan mencatat barang rusak jika tidak kembali ke stok jual.
- [ ] Hubungkan retur dengan invoice/order asal.

## Phase 6 - Harga, Promo, Pajak, dan Barcode

Tujuan: POS lebih fleksibel untuk skenario toko nyata.

Acceptance criteria Phase 6:

- Diskon tercatat di order/order detail, bukan hanya perubahan tampilan total.
- Promo punya periode aktif dan bisa dinonaktifkan.
- Pajak/service charge punya konfigurasi dan nilai tersimpan di transaksi.
- Barcode scanner flow bisa add item dari input kode.
- Struk thermal punya layout khusus dan tetap bisa dicetak dari browser.

- [ ] Diskon per item.
- [ ] Diskon per invoice.
- [ ] Voucher/promo sederhana berdasarkan periode.
- [ ] Harga grosir atau customer/member price.
- [ ] Pajak fleksibel per produk/kategori.
- [ ] Service charge opsional.
- [ ] Barcode scanner workflow di POS: scan kode langsung add/update qty.
- [ ] Cetak label barcode produk.
- [ ] Optimasi cetak struk thermal 58mm/80mm.
- [ ] Auto print receipt setelah order complete.

## Phase 7 - Laporan, Audit, dan Administrasi Lanjutan

Tujuan: owner/supervisor punya laporan dan kontrol administrasi yang cukup.

Acceptance criteria Phase 7:

- Laporan bisa difilter tanggal.
- Laporan sales final memakai order `complete`, memperhitungkan void/retur sesuai kebutuhan.
- Laporan laba memakai harga beli yang tersimpan pada saat transaksi atau fallback yang jelas.
- Export Excel/PDF menghasilkan data yang sama dengan tampilan.
- Audit log viewer tidak boleh mengizinkan edit log.

- [ ] Laporan penjualan per tanggal.
- [ ] Laporan penjualan per kasir.
- [ ] Laporan penjualan per produk.
- [ ] Laporan metode pembayaran.
- [ ] Laporan piutang.
- [ ] Laporan laba kotor berdasarkan buying price vs selling price.
- [ ] Laporan stok minimum/reorder.
- [ ] Notifikasi produk expired/dekat expired.
- [ ] Export laporan ke Excel.
- [ ] Export laporan ke PDF.
- [ ] Audit log viewer dengan filter user, tanggal, module, dan action.
- [ ] Restore database dari backup lewat UI dengan permission khusus.
- [ ] Pengaturan toko: nama toko, alamat, logo, pajak default, currency.
- [ ] Pengaturan role kasir lebih detail: diskon, void, edit harga, akses laporan.

## Daftar 20 Fitur Tambahan Utama

Daftar ini adalah ringkasan fitur enhancement utama yang tersebar di phase 2 sampai phase 7:

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
