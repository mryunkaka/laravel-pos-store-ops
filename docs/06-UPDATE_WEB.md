# Update Web Lokal

Dokumen ini mencatat aturan aman untuk fitur Update Web POS3.

## Tujuan

Update Web dipakai untuk memperbarui aplikasi lokal konsumen dari Git tanpa membuka terminal:

1. `git pull --ff-only`
2. `composer install`
3. `npm.cmd install`
4. `npm.cmd run build`
5. `php artisan optimize:clear`
6. `php artisan migrate --force`
7. `php artisan view:cache`

Halaman GUI:

```text
http://localhost:8082/settings/update-web
```

Link darurat lokal:

```text
http://localhost:8082/update-web.test
http://localhost:8082/update-web.start
```

## Aturan aman data

Update Web tidak boleh menghapus atau mengubah isi data operasional yang sudah ada.

Data yang wajib aman:

1. Riwayat transaksi.
2. Detail transaksi.
3. Piutang dan pembayaran piutang.
4. Produk dan stok.
5. Pelanggan.
6. Supplier.
7. Karyawan.
8. Shift kasir dan tutup kasir.
9. Audit log.
10. Backup database.

Perubahan database yang boleh otomatis:

1. Tambah tabel baru.
2. Tambah kolom baru.
3. Tambah index baru.
4. Tambah permission/role mapping baru.
5. Tambah data konfigurasi default yang tidak menimpa data lama.

Perubahan database yang tidak boleh otomatis tanpa backup dan konfirmasi manual:

1. Drop tabel.
2. Drop kolom.
3. Rename tabel.
4. Rename kolom.
5. Truncate tabel.
6. Delete data.
7. Update massal data transaksi lama.
8. Mengubah nilai pembayaran, piutang, stok, atau riwayat.

## UI wajib

Halaman Update Web wajib menampilkan:

1. Notifikasi jika ada update baru dari Git.
2. Progress bar persentase.
3. Status langkah berjalan.
4. Log realtime.
5. Tombol `Copy Log Update`.
6. Pesan aturan aman data.
7. Reload otomatis setelah update selesai sukses.

## Catatan implementasi

Endpoint status realtime:

```text
GET /settings/update-web/status
```

Endpoint ini mengembalikan status proses, progress, log, dan info update Git.

Halaman melakukan polling tiap 1 detik, bukan refresh penuh, agar log terlihat realtime.
