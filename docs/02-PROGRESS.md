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

## Kesimpulan Teknis Saat Ini

- POS membuat order dengan status `pending`.
- Stok produk dikurangi saat order diubah menjadi `complete`.
- Piutang dicatat lewat `due_amount`.
- Pembayaran piutang mengubah `pay_amount` dan `due_amount`.
- Produk punya `stock`, `buying_price`, `selling_price`, dan `expire_date`.
- Belum ada tabel mutasi stok, closing, shift, purchase, retur, audit, atau stock opname.

## Risiko Saat Ini

- Stok bisa minus karena belum ada validasi stok saat add/update cart dan complete order.
- Dashboard `today_sales` menghitung semua order berdasarkan `created_at`, bukan hanya complete order.
- Top selling product menghitung semua `order_details`, termasuk order pending.
- Tidak ada audit trail untuk perubahan stok, harga, pembayaran, dan status order.
- Tidak ada mekanisme void/cancel resmi dengan alasan dan approval.
- `composer.phar` masih file lokal untracked, dipakai supaya tidak perlu Composer global.

## Next Step

Kerjakan `03-TODO.md` mulai dari Phase 1. Jangan lanjut Phase 2 sebelum semua checklist Phase 1 selesai dan ditandai.

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
