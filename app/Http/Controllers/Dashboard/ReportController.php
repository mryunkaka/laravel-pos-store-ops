<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\SalesReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    private array $types = [
        'sales-date' => 'Penjualan per Tanggal',
        'sales-cashier' => 'Penjualan per Kasir',
        'sales-product' => 'Penjualan per Produk',
        'payment-method' => 'Metode Pembayaran',
        'receivable' => 'Piutang',
        'gross-profit' => 'Laba Kotor',
        'minimum-stock' => 'Stok Minimum',
        'expiry' => 'Kedaluwarsa',
    ];

    public function index(Request $request)
    {
        $report = $this->buildReport($request);

        return view('reports.index', array_merge($report, [
            'types' => $this->types,
        ]));
    }

    public function exportExcel(Request $request)
    {
        $report = $this->buildReport($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($report['title'], 0, 31));

        foreach ($report['columns'] as $index => $column) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $column);
        }

        foreach ($report['rows'] as $rowIndex => $row) {
            foreach (array_values($row) as $columnIndex => $value) {
                $sheet->setCellValueByColumnAndRow($columnIndex + 1, $rowIndex + 2, $value);
            }
        }

        foreach (range(1, count($report['columns'])) as $columnIndex) {
            $sheet->getColumnDimensionByColumn($columnIndex)->setAutoSize(true);
        }

        $fileName = 'report-' . $report['type'] . '-' . now()->format('YmdHis') . '.xlsx';
        $path = storage_path('app/' . $fileName);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->buildReport($request);

        return view('reports.print', $report);
    }

    private function buildReport(Request $request): array
    {
        $type = $request->input('type', 'sales-date');
        if (!array_key_exists($type, $this->types)) {
            $type = 'sales-date';
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        $data = match ($type) {
            'sales-cashier' => $this->salesByCashier($startDate, $endDate),
            'sales-product' => $this->salesByProduct($startDate, $endDate),
            'payment-method' => $this->paymentMethod($startDate, $endDate),
            'receivable' => $this->receivable($startDate, $endDate),
            'gross-profit' => $this->grossProfit($startDate, $endDate),
            'minimum-stock' => $this->minimumStock(),
            'expiry' => $this->expiryProducts(),
            default => $this->salesByDate($startDate, $endDate),
        };

        return array_merge($data, [
            'type' => $type,
            'title' => $this->types[$type],
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }

    private function completeOrders(Carbon $startDate, Carbon $endDate)
    {
        return Order::query()
            ->where('order_status', 'complete')
            ->whereBetween('order_date', [$startDate, $endDate]);
    }

    private function salesByDate(Carbon $startDate, Carbon $endDate): array
    {
        $orders = $this->completeOrders($startDate, $endDate)
            ->selectRaw('DATE(order_date) as tanggal, COUNT(*) as transaksi, SUM(total) as penjualan, SUM(discount) as diskon, SUM(tax_total) as pajak, SUM(service_charge) as service')
            ->groupByRaw('DATE(order_date)')
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $returns = SalesReturn::query()
            ->where('status', 'completed')
            ->whereBetween('return_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('return_date as tanggal, SUM(refund_amount) as retur')
            ->groupBy('return_date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->tanggal)->toDateString());

        $rows = $orders->map(function ($row, $date) use ($returns) {
            $returnAmount = (float) ($returns[$date]->retur ?? 0);
            return [
                'Tanggal' => $date,
                'Transaksi' => (int) $row->transaksi,
                'Penjualan' => (float) $row->penjualan,
                'Retur' => $returnAmount,
                'Netto' => (float) $row->penjualan - $returnAmount,
                'Diskon' => (float) $row->diskon,
                'Pajak' => (float) $row->pajak,
                'Service' => (float) $row->service,
            ];
        })->values()->all();

        return $this->table(['Tanggal', 'Transaksi', 'Penjualan', 'Retur', 'Netto', 'Diskon', 'Pajak', 'Service'], $rows);
    }

    private function salesByCashier(Carbon $startDate, Carbon $endDate): array
    {
        $rows = $this->completeOrders($startDate, $endDate)
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->selectRaw("COALESCE(users.name, 'Tidak diketahui') as kasir, COUNT(orders.id) as transaksi, SUM(orders.total) as penjualan, SUM(orders.pay_amount) as dibayar, SUM(orders.due_amount) as piutang")
            ->groupBy('users.name')
            ->orderBy('kasir')
            ->get()
            ->map(fn ($row) => [
                'Kasir' => $row->kasir,
                'Transaksi' => (int) $row->transaksi,
                'Penjualan' => (float) $row->penjualan,
                'Dibayar' => (float) $row->dibayar,
                'Piutang' => (float) $row->piutang,
            ])->all();

        return $this->table(['Kasir', 'Transaksi', 'Penjualan', 'Dibayar', 'Piutang'], $rows);
    }

    private function salesByProduct(Carbon $startDate, Carbon $endDate): array
    {
        $rows = OrderDetails::query()
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.order_status', 'complete')
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->selectRaw("COALESCE(products.name, 'Produk dihapus') as produk, SUM(order_details.quantity) as qty, SUM(order_details.total) as penjualan")
            ->groupBy('products.name')
            ->orderByDesc('penjualan')
            ->get()
            ->map(fn ($row) => [
                'Produk' => $row->produk,
                'Qty Terjual' => (int) $row->qty,
                'Penjualan' => (float) $row->penjualan,
            ])->all();

        return $this->table(['Produk', 'Qty Terjual', 'Penjualan'], $rows);
    }

    private function paymentMethod(Carbon $startDate, Carbon $endDate): array
    {
        $rows = DB::table('cash_shift_details')
            ->join('orders', 'cash_shift_details.order_id', '=', 'orders.id')
            ->where('orders.order_status', 'complete')
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->selectRaw('cash_shift_details.payment_type as metode, COUNT(DISTINCT orders.id) as transaksi, SUM(cash_shift_details.amount) as total')
            ->groupBy('cash_shift_details.payment_type')
            ->orderBy('cash_shift_details.payment_type')
            ->get()
            ->map(fn ($row) => [
                'Metode' => strtoupper($row->metode),
                'Transaksi' => (int) $row->transaksi,
                'Total' => (float) $row->total,
            ])->all();

        return $this->table(['Metode', 'Transaksi', 'Total'], $rows);
    }

    private function receivable(Carbon $startDate, Carbon $endDate): array
    {
        $rows = Order::query()
            ->with('customer')
            ->where('due_amount', '>', 0)
            ->whereNotIn('order_status', ['cancelled', 'void'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderByDesc('order_date')
            ->get()
            ->map(fn ($order) => [
                'Tanggal' => $order->order_date->format('Y-m-d'),
                'Invoice' => $order->invoice_no,
                'Pelanggan' => $order->customer->name ?? '-',
                'Total' => (float) $order->total,
                'Dibayar' => (float) $order->pay_amount,
                'Piutang' => (float) $order->due_amount,
                'Status' => $order->order_status,
            ])->all();

        return $this->table(['Tanggal', 'Invoice', 'Pelanggan', 'Total', 'Dibayar', 'Piutang', 'Status'], $rows);
    }

    private function grossProfit(Carbon $startDate, Carbon $endDate): array
    {
        $rows = OrderDetails::query()
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.order_status', 'complete')
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->selectRaw("COALESCE(products.name, 'Produk dihapus') as produk, SUM(order_details.quantity) as qty, SUM(order_details.total) as penjualan, SUM(COALESCE(NULLIF(order_details.buying_price, 0), products.buying_price, 0) * order_details.quantity) as modal")
            ->groupBy('products.name')
            ->orderByDesc('penjualan')
            ->get()
            ->map(fn ($row) => [
                'Produk' => $row->produk,
                'Qty' => (int) $row->qty,
                'Penjualan' => (float) $row->penjualan,
                'Modal' => (float) $row->modal,
                'Laba Kotor' => (float) $row->penjualan - (float) $row->modal,
            ])->all();

        return $this->table(['Produk', 'Qty', 'Penjualan', 'Modal', 'Laba Kotor'], $rows);
    }

    private function minimumStock(): array
    {
        $rows = Product::query()
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->orderBy('stock')
            ->get()
            ->map(fn ($product) => [
                'Produk' => $product->name,
                'Kode' => $product->code,
                'Stok' => (int) $product->stock,
                'Stok Minimum' => (int) $product->minimum_stock,
                'Kategori' => $product->category->name ?? '-',
            ])->all();

        return $this->table(['Produk', 'Kode', 'Stok', 'Stok Minimum', 'Kategori'], $rows);
    }

    private function expiryProducts(): array
    {
        $rows = Product::query()
            ->whereNotNull('expire_date')
            ->whereDate('expire_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('expire_date')
            ->get()
            ->map(fn ($product) => [
                'Produk' => $product->name,
                'Kode' => $product->code,
                'Tanggal Expired' => Carbon::parse($product->expire_date)->format('Y-m-d'),
                'Stok' => (int) $product->stock,
                'Kategori' => $product->category->name ?? '-',
            ])->all();

        return $this->table(['Produk', 'Kode', 'Tanggal Expired', 'Stok', 'Kategori'], $rows);
    }

    private function table(array $columns, array $rows): array
    {
        return [
            'columns' => $columns,
            'rows' => $rows,
            'totals' => $this->totals($rows),
        ];
    }

    private function totals(array $rows): array
    {
        $totals = [];

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $totals[$key] = ($totals[$key] ?? 0) + $value;
                }
            }
        }

        return $totals;
    }
}
