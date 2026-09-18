@php
    $ordersActive = Request::is('orders/pending*') || Request::is('orders/complete*') || Request::is('pending/due*');
    $salaryActive = Request::is('advance-salary*') || Request::is('pay-salary*');
    $attendanceActive = Request::is('attendance*');
    $roleActive = Request::is('permission*') || Request::is('role*');
    $canUpdateWeb = auth()->user()->can('settings.menu') || auth()->user()->can('roles.menu') || auth()->user()->can('database.menu');
@endphp

<div class="iq-sidebar sidebar-default ">
    <div class="iq-sidebar-logo d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="header-logo">
            <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid rounded-normal light-logo" alt="logo"><h5 class="logo-title light-logo ml-3">POSDash</h5>
        </a>
        <div class="iq-menu-bt-sidebar ml-0">
            <x-heroicon-o-bars-3 class="wrapper-menu w-8 h-8" />
        </div>
    </div>
    <style>
        .sidebar-search-wrap {
            position: sticky;
            top: 0;
            z-index: 5;
            padding: 12px 16px 8px;
            background: #ffffff;
        }

        .sidebar-search-input {
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
        }

        .sidebar-search-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.12);
        }
    </style>
    <div class="data-scrollbar" data-scroll="1" id="sidebar-scroll-area">
        <div class="sidebar-search-wrap">
            <input type="search" id="sidebar-menu-search" class="sidebar-search-input" placeholder="Cari menu..." autocomplete="off">
        </div>
        <nav class="iq-sidebar-menu">
            <ul id="iq-sidebar-toggle" class="iq-menu">
                <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="svg-icon">
                        <x-heroicon-o-home class="w-6 h-6" />
                        <span class="ml-4">Dashboard</span>
                    </a>
                </li>

                @if (auth()->user()->can('pos.menu'))
                    <li class="{{ Request::is('pos*') ? 'active' : '' }}">
                        <a href="{{ route('pos.index') }}" class="svg-icon">
                            <x-heroicon-o-shopping-cart class="w-6 h-6" />
                            <span class="ml-3">POS</span>
                            </a>
                            </li>
                @endif

                <hr>

                @if (auth()->user()->can('orders.menu'))
                    <li>
                        <a href="#orders" class="{{ $ordersActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $ordersActive ? 'true' : 'false' }}">
                            <x-heroicon-o-shopping-bag class="w-6 h-6" />
                            <span class="ml-3">Order</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                            </a>
                            <ul id="orders" class="iq-submenu collapse {{ $ordersActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">

                            <li class="{{ Request::is('orders/pending*') ? 'active' : '' }}">
                                <a href="{{ route('order.pendingOrders') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Order Tertunda</span>
                                    </a>
                                    </li>
                                    <li class="{{ Request::is('orders/complete*') ? 'active' : '' }}">
                                        <a href="{{ route('order.completeOrders') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Order Selesai</span>
                                    </a>
                                    </li>
                                    <li class="{{ Request::is('pending/due*') ? 'active' : '' }}">
                                        <a href="{{ route('order.pendingDue') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Piutang Tertunda</span>
                                    </a>
                                    </li>

                                    </ul>
                                    </li>
                @endif

                @if (auth()->user()->can('product.menu'))
                    @php
                        $productActive = Request::is('products*') || Request::is('categories*') || Request::is('vouchers*');
                    @endphp
                    <li>
                        <a href="#products" class="{{ $productActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $productActive ? 'true' : 'false' }}">
                            <x-heroicon-o-archive-box class="w-6 h-6" />
                            <span class="ml-3">Produk</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                            </a>
                            <ul id="products" class="iq-submenu collapse {{ $productActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">
                                <li class="{{ Request::is(['products']) ? 'active' : '' }}">
                                    <a href="{{ route('products.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Produk</span>
                                        </a>
                                        </li>
                                        <li class="{{ Request::is(['products/create']) ? 'active' : '' }}">
                                            <a href="{{ route('products.create') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Tambah Produk</span>
                                        </a>
                                        </li>
                                        <li class="{{ Request::is(['categories*']) ? 'active' : '' }}">
                                            <a href="{{ route('categories.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Kategori</span>
                                        </a>
                                        </li>
                                        <li class="{{ Request::is(['vouchers*']) ? 'active' : '' }}">
                                            <a href="{{ route('vouchers.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Voucher/Promo</span>
                                        </a>
                                        </li>
                                        </ul>
                                        </li>
                @endif

                <hr>

                @php
                    $inventoryActive = Request::is('stock-movements*')
                        || Request::is('stock-adjustments*')
                        || Request::is('stock-transfers*')
                        || Request::is('stock-opnames*')
                        || Request::is('sales-returns*')
                        || Request::is('purchase-orders*')
                        || Request::is('purchase-receivings*')
                        || Request::is('purchase-returns*');
                @endphp

                @if (auth()->user()->can('orders.menu'))
                    <li>
                        <a href="#inventory" class="{{ $inventoryActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $inventoryActive ? 'true' : 'false' }}">
                            <x-heroicon-o-document-text class="w-6 h-6" />
                            <span class="ml-3">Inventaris</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="inventory" class="iq-submenu collapse {{ $inventoryActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">

                            <li class="{{ Request::is('stock-movements*') ? 'active' : '' }}">
                                <a href="{{ route('stock-movements.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Pergerakan Stok</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('stock-adjustments') ? 'active' : '' }}">
                                <a href="{{ route('stock-adjustments.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Penyesuaian Stok</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('stock-transfers*') ? 'active' : '' }}">
                                <a href="{{ route('stock-transfers.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Transfer Stok</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('stock-opnames*') ? 'active' : '' }}">
                                <a href="{{ route('stock-opnames.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Stock Opname</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('sales-returns*') ? 'active' : '' }}">
                                <a href="{{ route('sales-returns.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Retur Penjualan</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('purchase-orders') ? 'active' : '' }}">
                                <a href="{{ route('purchase-orders.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Order Pembelian</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('purchase-receivings') ? 'active' : '' }}">
                                <a href="{{ route('purchase-receivings.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Penerimaan Barang</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('purchase-returns') ? 'active' : '' }}">
                                <a href="{{ route('purchase-returns.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Retur Pembelian</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                @if (auth()->user()->can('orders.menu'))
                    <li>
                        <a href="#cashier" class="{{ Request::is('cash-shifts*') || Request::is('cash-closings*') ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ Request::is('cash-shifts*') || Request::is('cash-closings*') ? 'true' : 'false' }}">
                            <x-heroicon-o-banknotes class="w-6 h-6" />
                            <span class="ml-3">Kasir</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="cashier" class="iq-submenu collapse {{ Request::is('cash-shifts*') || Request::is('cash-closings*') ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">

                            <li class="{{ Request::is('cash-shifts*') ? 'active' : '' }}">
                                <a href="{{ route('cash-shifts.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Shift Kasir</span>
                                </a>
                            </li>

                            <li class="{{ Request::is('cash-closings*') ? 'active' : '' }}">
                                <a href="{{ route('cash-closings.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Tutup Kasir</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif

                @if (auth()->user()->can('employee.menu'))
                    <li class="{{ Request::is('employees*') ? 'active' : '' }}">
                        <a href="{{ route('employees.index') }}" class="svg-icon">
                            <x-heroicon-o-user-group class="w-6 h-6" />
                            <span class="ml-3">Karyawan</span>
                            </a>
                            </li>
                @endif

                @if (auth()->user()->can('customer.menu'))
                    <li class="{{ Request::is('customers*') ? 'active' : '' }}">
                        <a href="{{ route('customers.index') }}" class="svg-icon">
                            <x-heroicon-o-user-group class="w-6 h-6" />
                            <span class="ml-3">Pelangganan</span>
                            </a>
                            </li>
                @endif

                @if (auth()->user()->can('supplier.menu'))
                    <li class="{{ Request::is('suppliers*') ? 'active' : '' }}">
                        <a href="{{ route('suppliers.index') }}" class="svg-icon">
                            <x-heroicon-o-user-group class="w-6 h-6" />
                            <span class="ml-3">Pemasok</span>
                            </a>
                            </li>
                @endif

                @if (auth()->user()->can('salary.menu'))
                    <li>
                        <a href="#advance-salary" class="{{ $salaryActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $salaryActive ? 'true' : 'false' }}">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                        <span class="ml-3">Gaji</span>
                        <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                        </a>
                        <ul id="advance-salary" class="iq-submenu collapse {{ $salaryActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">

                            <li class="{{ Request::is(['advance-salary', 'advance-salary/*/edit']) ? 'active' : '' }}">
                                <a href="{{ route('advance-salary.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Semua Gaji di Muka</span>
                                    </a>
                                    </li>
                                    <li class="{{ Request::is('advance-salary/create*') ? 'active' : '' }}">
                                        <a href="{{ route('advance-salary.create') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Buat Gaji di Muka</span>
                                    </a>
                                    </li>
                                    <li class="{{ Request::is('pay-salary') ? 'active' : '' }}">
                                        <a href="{{ route('pay-salary.index') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Bayar Gaji</span>
                                    </a>
                                    </li>
                                    <li class="{{ Request::is('pay-salary/history*') ? 'active' : '' }}">
                                        <a href="{{ route('pay-salary.payHistory') }}">
                                    <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Riwayat Bayar Gaji</span>
                                    </a>
                                    </li>
                                    </ul>
                                    </li>
                @endif

                @if (auth()->user()->can('attendance.menu'))
                    <li>
                        <a href="#attendance" class="{{ $attendanceActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $attendanceActive ? 'true' : 'false' }}">
                            <x-heroicon-o-calendar-days class="w-6 h-6" />
                            <span class="ml-3">Absensi</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                            </a>
                            <ul id="attendance" class="iq-submenu collapse {{ $attendanceActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">

                                <li class="{{ Request::is(['attendance']) ? 'active' : '' }}">
                                    <a href="{{ route('attendance.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Semua Absensi</span>
                                        </a>
                                        </li>
                                        <li class="{{ Request::is('attendance/create') ? 'active' : '' }}">
                                            <a href="{{ route('attendance.create') }}">
                                                <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Buat Absensi</span>
                                        </a>
                                        </li>
                                        </ul>
                                        </li>
                @endif

                <hr>

                @if (auth()->user()->can('report.menu'))
                    <li class="{{ Request::is('reports*') ? 'active' : '' }}">
                        <a href="{{ route('reports.index') }}" class="svg-icon">
                            <x-heroicon-o-chart-bar class="w-6 h-6" />
                            <span class="ml-3">Laporan</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->can('audit.menu'))
                    <li class="{{ Request::is('audit-logs*') ? 'active' : '' }}">
                        <a href="{{ route('audit-logs.index') }}" class="svg-icon">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                            <span class="ml-3">Audit Log</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->can('settings.menu'))
                    <li class="{{ Request::is('settings/store*') ? 'active' : '' }}">
                        <a href="{{ route('settings.store.edit') }}" class="svg-icon">
                            <x-heroicon-o-cog-6-tooth class="w-6 h-6" />
                            <span class="ml-3">Pengaturan Toko</span>
                        </a>
                    </li>
                @endif

                @if ($canUpdateWeb)
                    <li class="{{ Request::is('settings/update-web*') || Request::is('update-web*') ? 'active' : '' }}">
                        <a href="{{ route('system-update.index') }}" class="svg-icon">
                            <x-heroicon-o-arrow-path class="w-6 h-6" />
                            <span class="ml-3">Update Web</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->can('roles.menu'))
                    <li>
                        <a href="#permission" class="{{ $roleActive ? '' : 'collapsed' }}" data-toggle="collapse" aria-expanded="{{ $roleActive ? 'true' : 'false' }}">
                            <x-heroicon-o-key class="w-6 h-6" />
                            <span class="ml-3">Role & Permission</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4 iq-arrow-right arrow-active" />
                            </a>
                            <ul id="permission" class="iq-submenu collapse {{ $roleActive ? 'show' : '' }}" data-parent="#iq-sidebar-toggle">
                                <li class="{{ Request::is(['permission', 'permission/create', 'permission/edit/*']) ? 'active' : '' }}">
                                    <a href="{{ route('permission.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Permission</span>
                                        </a>
                                        </li>
                                        <li class="{{ Request::is(['role', 'role/create', 'role/edit/*']) ? 'active' : '' }}">
                                            <a href="{{ route('role.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Role</span>
                                        </a>
                                        </li>
                                        <li class="{{ Request::is(['role/permission*']) ? 'active' : '' }}">
                                            <a href="{{ route('rolePermission.index') }}">
                                        <x-heroicon-o-arrow-right class="w-4 h-4" /><span>Role in Permissions</span>
                                        </a>
                                        </li>
                                        </ul>
                                        </li>
                @endif

                @if (auth()->user()->can('user.menu'))
                    <li class="{{ Request::is('users*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="svg-icon">
                            <x-heroicon-o-users class="w-6 h-6" />
                            <span class="ml-3">Pengguna</span>
                            </a>
                            </li>
                @endif

                @if (auth()->user()->can('database.menu'))
                    <li class="{{ Request::is('database/backup*') ? 'active' : '' }}">
                        <a href="{{ route('backup.index') }}" class="svg-icon">
                            <x-heroicon-o-circle-stack class="w-6 h-6" />
                            <span class="ml-3">Backup Database</span>
                            </a>
                            </li>
                @endif

                <li class="{{ Request::is('help*') ? 'active' : '' }}">
                    <a href="{{ route('help.index') }}" class="svg-icon">
                        <x-heroicon-o-question-mark-circle class="w-6 h-6" />
                        <span class="ml-3">Bantuan</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-3"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar-scroll-area');
    var menu = document.getElementById('iq-sidebar-toggle');
    var search = document.getElementById('sidebar-menu-search');

    if (!sidebar || !menu || !search) {
        return;
    }

    var activeItem = menu.querySelector('li.active');

    function scrollToActiveItem() {
        if (!activeItem) {
            return;
        }

        var openMenu = activeItem.closest('.iq-submenu');
        if (openMenu) {
            openMenu.classList.add('show');
            var toggle = menu.querySelector('a[href="#' + openMenu.id + '"]');
            if (toggle) {
                toggle.classList.remove('collapsed');
                toggle.setAttribute('aria-expanded', 'true');
            }
        }

        var targetTop = Math.max(activeItem.offsetTop - search.offsetHeight - 100, 0);
        var scrollbar = window.Scrollbar && window.Scrollbar.get ? window.Scrollbar.get(sidebar) : null;

        if (scrollbar) {
            scrollbar.scrollTo(0, targetTop, 250);
            return;
        }

        sidebar.scrollTop = targetTop;
        var scrollContent = sidebar.querySelector('.scroll-content, .simplebar-content-wrapper');
        if (scrollContent) {
            scrollContent.scrollTop = targetTop;
        }
    }

    scrollToActiveItem();
    window.addEventListener('load', function () {
        scrollToActiveItem();
        window.setTimeout(scrollToActiveItem, 150);
        window.setTimeout(scrollToActiveItem, 500);
    });

    search.addEventListener('input', function () {
        var keyword = search.value.trim().toLowerCase();
        var topItems = Array.prototype.slice.call(menu.children).filter(function (item) {
            return item.tagName && item.tagName.toLowerCase() === 'li';
        });

        topItems.forEach(function (item) {
            var submenu = item.querySelector('.iq-submenu');
            var toggle = submenu ? item.querySelector('a[data-toggle="collapse"]') : null;
            var childItems = submenu ? Array.prototype.slice.call(submenu.querySelectorAll('li')) : [];
            var childMatch = false;

            childItems.forEach(function (child) {
                var match = !keyword || child.textContent.toLowerCase().indexOf(keyword) !== -1;
                child.style.display = match ? '' : 'none';
                childMatch = childMatch || match;
            });

            var ownText = item.firstElementChild ? item.firstElementChild.textContent.toLowerCase() : item.textContent.toLowerCase();
            var match = !keyword || ownText.indexOf(keyword) !== -1 || childMatch;
            item.style.display = match ? '' : 'none';

            if (submenu && keyword) {
                submenu.classList.toggle('show', match);
                if (toggle) {
                    toggle.classList.toggle('collapsed', !match);
                    toggle.setAttribute('aria-expanded', match ? 'true' : 'false');
                }
            } else if (submenu && !keyword) {
                var hasActive = submenu.querySelector('li.active');
                submenu.classList.toggle('show', !!hasActive);
                if (toggle) {
                    toggle.classList.toggle('collapsed', !hasActive);
                    toggle.setAttribute('aria-expanded', hasActive ? 'true' : 'false');
                }
            }
        });
    });
});
</script>
