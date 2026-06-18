@extends('dashboard.body.main')

@section('specificpagestyles')
    @vite(['resources/css/app.css'])
    <style>
        /* Protect sidebar from Tailwind CSS reset interference */
        /* Only ensure visibility - don't override display properties to preserve alignment */
        .iq-sidebar-menu .iq-menu li,
        .iq-sidebar-menu .iq-menu li a,
        .iq-sidebar-menu .iq-menu li ul,
        .iq-sidebar-menu .iq-menu li .iq-submenu,
        .iq-sidebar-menu .iq-menu li .iq-submenu li,
        .iq-sidebar-menu .iq-menu li .iq-submenu li a {
            visibility: visible !important;
        }

        /* Preserve collapse/expand behavior for submenus */
        .iq-submenu.collapse:not(.show) {
            display: none !important;
        }
        .iq-submenu.collapse.show {
            display: block !important;
        }

        /* Ensure submenu links are visible but preserve original display property for alignment */
        .iq-sidebar-menu .iq-menu li .iq-submenu li a {
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Preserve original display for submenu spans to maintain alignment */
        .iq-sidebar-menu .iq-menu li .iq-submenu li a span {
            visibility: visible !important;
        }
    </style>
@endsection

@section('container')
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-wrap items-center justify-between mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Bantuan & Dokumentasi</h2>
                    <p class="text-gray-600">Panduan lengkap untuk POSDash - Sistem Manajemen Kasir Anda</p>
                </div>
            </div>

            <!-- Introduction Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8 border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-3">Tentang POSDash</h3>
                <p class="text-gray-700 leading-relaxed">
                    POSDash adalah sistem manajemen Point of Sale (POS) yang handal dan berkelas enterprise, dirancang untuk efisiensi dan kemudahan penggunaan.
                    Aplikasi ini menyediakan alat lengkap untuk mengelola penjualan, inventaris, karyawan, pelanggan, dan operasi keuangan
                    semuanya dalam satu platform terintegrasi.
                </p>
            </div>

            <!-- Feature Categories -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Point of Sale (POS) -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-shopping-cart class="w-6 h-6 text-blue-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Point of Sale (POS)</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Antarmuka pemrosesan transaksi yang cepat dan ramah pengguna</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pencarian produk pintar berdasarkan nama atau kode</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan keranjang real-time dengan perhitungan dinamis</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Perhitungan subtotal dan pajak otomatis</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pemilihan pelanggan dan pembuatan pelanggan cepat</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Penerbitan faktur dan kuitansi profesional</span>
                        </li>
                    </ul>
                </div>

                <!-- Product & Inventory Management -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-cyan-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-archive-box class="w-6 h-6 text-cyan-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Produk & Inventaris</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan katalog produk lengkap</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengorganisasian kategori secara hierarkis</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pelacakan dan pengurangan stok otomatis</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Dukungan barcode untuk identifikasi produk</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Fungsi impor/ekspor produk</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pelacakan tanggal kedaluwarsa produk</span>
                        </li>
                    </ul>
                </div>

                <!-- Order Management -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-shopping-bag class="w-6 h-6 text-green-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Pengelolaan Pesanan</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Melacak pesanan pending dan yang sudah selesai</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Kelola pembayaran tunggakan pending</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Informasi dan riwayat pesanan mendetail</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Perbarui status pesanan (pending menjadi selesai)</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Unduh faktur dan cetak kuitansi</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pembuatan nomor faktur otomatis</span>
                        </li>
                    </ul>
                </div>

                <!-- Customer Management -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-user-group class="w-6 h-6 text-yellow-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Manajemen Pelanggan</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Database pelanggan komprehensif</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Profil pelanggan dengan informasi kontak</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pencarian dan pemilihan pelanggan cepat</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pelacakan riwayat pesanan pelanggan</span>
                        </li>
                    </ul>
                </div>

                <!-- Supplier Management -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-user-group class="w-6 h-6 text-red-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Manajemen Pemasok</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Menjaga database informasi pemasok</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Melacak detail kontak pemasok</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Mengelola hubungan dengan pemasok</span>
                        </li>
                    </ul>
                </div>

                <!-- Employee Management -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-user-group class="w-6 h-6 text-blue-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Manajemen Karyawan</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Catatan dan profil karyawan lengkap</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan foto karyawan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Informasi kontak dan gaji karyawan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pencarian dan penyaringan data karyawan</span>
                        </li>
                    </ul>
                </div>

                <!-- HR & Payroll -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-cyan-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-banknotes class="w-6 h-6 text-cyan-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">SDM & Penggajian</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pelacakan kehadiran karyawan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan uang muka gaji</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pemrosesan pembayaran gaji bulanan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Opsi pembayaran gaji massal</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Riwayat pembayaran gaji lengkap</span>
                        </li>
                    </ul>
                </div>

                <!-- Financial Reporting -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-chart-bar class="w-6 h-6 text-green-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Laporan Keuangan</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Dasbor interaktif dengan metrik utama</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Visualisasi tren penjualan bulanan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Analisis produk terlaris</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pelacakan total jumlah yang dibayar dan tunggakan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Ikhtisar transaksi terbaru</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pencetakan faktur dan kuitansi profesional</span>
                        </li>
                    </ul>
                </div>

                <!-- User & Access Control -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-key class="w-6 h-6 text-yellow-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Pengguna & Kontrol Akses</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan akun pengguna</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Kontrol akses berbasis peran (RBAC)</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan izin yang terperinci</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Penugasan peran dan izin</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Sistem otentikasi yang aman</span>
                        </li>
                    </ul>
                </div>

                <!-- Database Management -->
                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 rounded-lg p-3 mr-4">
                            <x-heroicon-o-circle-stack class="w-6 h-6 text-red-600" />
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Pengelolaan Database</h3>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pembuatan cadangan database sesuai permintaan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Unduh file cadangan</span>
                        </li>
                        <li class="flex items-start text-gray-700">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                            <span>Pengelolaan dan penghapusan file cadangan</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Technical Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8 border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Stack Teknis</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-bold text-gray-700 mb-3">Backend:</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <li>Laravel 10 (PHP 8.1+)</li>
                            <li>MySQL / MariaDB Database</li>
                            <li>Spatie Laravel Permission (RBAC)</li>
                            <li>Shopping Cart Package</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-700 mb-3">Frontend:</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <li>Blade Templates</li>
                            <li>Bootstrap 4/5 (Layout)</li>
                            <li>Tailwind CSS (Utilities)</li>
                            <li>Vanilla JavaScript</li>
                            <li>ApexCharts (Analytics)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Tips Cepat</h3>
                <ul class="space-y-3">
                    <li class="flex items-start text-gray-700">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
                        <span>Gunakan fungsi pencarian untuk dengan cepat menemukan produk, pelanggan, atau karyawan</span>
                    </li>
                    <li class="flex items-start text-gray-700">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
                        <span>Stok akan otomatis dikurangi saat pesanan ditandai sebagai selesai</span>
                    </li>
                    <li class="flex items-start text-gray-700">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
                        <span>Anda dapat melacak pembayaran tunggakan pending dan memperbaruinya saat pelanggan membayar</span>
                    </li>
                    <li class="flex items-start text-gray-700">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
                        <span>Dasbor memberikan wawasan real-time tentang kinerja penjualan Anda</span>
                    </li>
                    <li class="flex items-start text-gray-700">
                        <x-heroicon-o-light-bulb class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" />
                        <span>Cadangan database secara berkala direkomendasikan untuk melindungi data Anda</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
