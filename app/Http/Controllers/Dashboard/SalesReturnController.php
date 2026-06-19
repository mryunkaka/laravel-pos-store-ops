<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashShift;
use App\Models\CashShiftDetail;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\StockMovement;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesReturn::with(['order.customer', 'creator']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->whereDate('return_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('return_date', '<=', $request->end_date);
        }

        $returns = $query->latest()->paginate(15)->withQueryString();

        return view('sales-returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $orders = Order::with('customer')
            ->where('order_status', 'complete')
            ->latest()
            ->limit(100)
            ->get();

        $selectedOrder = null;
        if ($request->order_id) {
            $selectedOrder = Order::with(['customer', 'details.product', 'details.salesReturnDetails.salesReturn'])
                ->where('order_status', 'complete')
                ->findOrFail($request->order_id);
        }

        return view('sales-returns.create', compact('orders', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
            'return_type' => 'required|in:refund,exchange',
            'reason' => 'required|string|min:3',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'nullable|integer|min:0',
            'items.*.condition' => 'nullable|in:sellable,damaged',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        $order = Order::with(['details.salesReturnDetails.salesReturn'])->findOrFail($request->order_id);

        if ($order->order_status !== 'complete') {
            return redirect()->back()->withInput()->with('error', 'Retur hanya bisa dibuat dari order complete.');
        }

        $selectedItems = collect($request->items)
            ->filter(function ($item) {
                return (int) ($item['quantity'] ?? 0) > 0;
            });

        if ($selectedItems->isEmpty()) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu item retur.');
        }

        try {
            DB::transaction(function () use ($request, $order, $selectedItems) {
                $salesReturn = SalesReturn::create([
                    'order_id' => $order->id,
                    'return_number' => IdGenerator::generate([
                        'table' => 'sales_returns',
                        'field' => 'return_number',
                        'length' => 12,
                        'prefix' => 'SR-',
                    ]),
                    'return_date' => $request->return_date,
                    'return_type' => $request->return_type,
                    'status' => 'pending',
                    'refund_amount' => 0,
                    'reason' => $request->reason,
                    'created_by' => auth()->id(),
                ]);

                $refundAmount = 0;

                foreach ($selectedItems as $orderDetailId => $item) {
                    $orderDetail = $order->details->firstWhere('id', (int) $orderDetailId);
                    if (!$orderDetail) {
                        abort(422, 'Item retur tidak sesuai dengan order.');
                    }

                    $quantity = (int) $item['quantity'];
                    $returnedQuantity = $this->completedReturnedQuantity($orderDetail);
                    $availableQuantity = $orderDetail->quantity - $returnedQuantity;

                    if ($quantity > $availableQuantity) {
                        abort(422, "Qty retur {$orderDetail->product->name} melebihi sisa yang bisa diretur.");
                    }

                    $total = $quantity * $orderDetail->unit_price;
                    if ($request->return_type === 'refund') {
                        $refundAmount += $total;
                    }

                    SalesReturnDetail::create([
                        'sales_return_id' => $salesReturn->id,
                        'order_detail_id' => $orderDetail->id,
                        'product_id' => $orderDetail->product_id,
                        'quantity' => $quantity,
                        'condition' => $item['condition'] ?? 'sellable',
                        'unit_price' => $orderDetail->unit_price,
                        'total' => $total,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                $salesReturn->update(['refund_amount' => $refundAmount]);
            });

            return redirect()->route('sales-returns.index')
                ->with('success', 'Retur penjualan berhasil dibuat.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat retur penjualan: ' . $e->getMessage());
        }
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['order.customer', 'details.product', 'creator', 'completer']);

        return view('sales-returns.show', compact('salesReturn'));
    }

    public function complete(SalesReturn $salesReturn)
    {
        if (!$salesReturn->isPending()) {
            return redirect()->back()->with('error', 'Retur ini sudah diproses.');
        }

        DB::transaction(function () use ($salesReturn) {
            $salesReturn->load(['details.product', 'order']);

            foreach ($salesReturn->details as $detail) {
                if ($detail->condition !== 'sellable') {
                    continue;
                }

                $product = Product::lockForUpdate()->findOrFail($detail->product_id);
                $product->increment('stock', $detail->quantity);

                StockMovement::recordIn(
                    $product,
                    $detail->quantity,
                    "Retur penjualan {$salesReturn->return_number}",
                    auth()->user(),
                    SalesReturn::class,
                    $salesReturn->id
                );
            }

            $salesReturn->update([
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            $activeShift = CashShift::where('user_id', auth()->id())
                ->where('status', 'active')
                ->first();

            if ($activeShift && $salesReturn->return_type === 'refund' && $salesReturn->refund_amount > 0) {
                CashShiftDetail::create([
                    'cash_shift_id' => $activeShift->id,
                    'order_id' => $salesReturn->order_id,
                    'transaction_type' => 'refund',
                    'amount' => $salesReturn->refund_amount,
                    'payment_type' => 'cash',
                    'description' => "Refund retur {$salesReturn->return_number}",
                    'transaction_time' => now(),
                ]);
            }
        });

        return redirect()->route('sales-returns.show', $salesReturn->id)
            ->with('success', 'Retur penjualan selesai diproses.');
    }

    public function cancel(SalesReturn $salesReturn)
    {
        if (!$salesReturn->isPending()) {
            return redirect()->back()->with('error', 'Hanya retur pending yang bisa dibatalkan.');
        }

        $salesReturn->update(['status' => 'cancelled']);

        return redirect()->route('sales-returns.index')
            ->with('success', 'Retur penjualan berhasil dibatalkan.');
    }

    private function completedReturnedQuantity(OrderDetails $orderDetail): int
    {
        return (int) $orderDetail->salesReturnDetails
            ->filter(function ($returnDetail) {
                return $returnDetail->salesReturn && $returnDetail->salesReturn->status === 'completed';
            })
            ->sum('quantity');
    }
}
