<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Http\Requests\Order\StoreOrderRequest;

class OrderController extends Controller
{
    /**
     * Display a listing of pending orders.
     */
    public function pendingOrders()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $orders = QueryBuilder::for(Order::class)
            ->whereIn('order_status', ['pending', 'cancelled'])
            ->allowedSorts([
                'order_date',
                'total',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('orders.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('orders.pending-orders', [
            'orders' => $orders
        ]);
    }

    /**
     * Display a listing of complete orders.
     */
    public function completeOrders()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $orders = QueryBuilder::for(Order::class)
            ->whereIn('order_status', ['complete', 'void'])
            ->allowedSorts([
                'order_date',
                'total',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $descending ? 'DESC' : 'ASC')
                        ->select('orders.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('orders.complete-orders', [
            'orders' => $orders
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function storeOrder(StoreOrderRequest $request)
    {
        // Validation handled by StoreOrderRequest

        return DB::transaction(function () use ($request) {
            // Stock validation before creating order
            $insufficientStock = [];
            $contents = Cart::content();
            $allowNegativeStock = $request->user()->can('allow-negative-stock');

            if (!$allowNegativeStock) {
                foreach ($contents as $item) {
                    $product = Product::find($item->id);
                    if ($product && $product->stock < $item->qty) {
                        $insufficientStock[] = "{$product->name} (stok: {$product->stock}, diminta: {$item->qty})";
                    }
                }

                if (!empty($insufficientStock)) {
                    $message = 'Stok tidak mencukupi: ' . implode(', ', $insufficientStock);
                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }
                    return Redirect::back()->with('error', $message);
                }
            }

            $invoice_no = IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-'
            ]);

            $total = (float) Cart::total(null, null, ''); // Get float value
            $pay_amount = $request->pay_amount;
            $due_amount = $total - $pay_amount;

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'invoice_no' => $invoice_no,
                'order_date' => Carbon::now(),
                'order_status' => 'pending',
                'total_products' => Cart::count(),
                'sub_total' => (float) Cart::subtotal(null, null, ''),
                'vat' => (float) Cart::tax(null, null, ''),
                'total' => $total,
                'payment_type' => $request->payment_type,
                'pay_amount' => $pay_amount,
                'due_amount' => $due_amount,
            ]);

            // Create Order Details
            foreach ($contents as $content) {
                OrderDetails::create([
                    'order_id' => $order->id,
                    'product_id' => $content->id,
                    'quantity' => $content->qty,
                    'unit_price' => $content->price,
                    'total' => $content->total,
                ]);
            }

            // Audit log
            AuditService::log('order', 'create', $order, null, $order->toArray(), "Order {$invoice_no} created");

            // Clear Cart
            Cart::destroy();

            if ($request->wantsJson()) {
                // Return success with Invoice URL and Cleared Cart HTML
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully!',
                    'invoice_url' => route('order.invoiceDownload', $order->id),
                    'cart_html' => view('pos.cart-sidebar', ['productItem' => Cart::content()])->render(),
                    'cart_count' => Cart::count(),
                ]);
            }

            return Redirect::route('order.invoiceDownload', $order->id)->with('success', 'Order has been created!');
        });
    }

    /**
     * Display the specified resource.
     */
    public function orderDetails(int $order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
                        ->where('order_id', $order_id)
                        ->orderBy('id', 'DESC')
                        ->get();

        return view('orders.details-order', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }

    /**
     * Complete an order - reduces stock.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|numeric|exists:orders,id',
        ]);

        $order_id = $request->id;
        $allowNegativeStock = $request->user()->can('allow-negative-stock');

        DB::transaction(function () use ($order_id, $allowNegativeStock) {
            $order = Order::findOrFail($order_id);

            // Prevent completing non-pending orders
            if ($order->order_status !== 'pending') {
                abort(422, 'Only pending orders can be completed.');
            }

            // Stock validation before completing
            $details = OrderDetails::where('order_id', $order_id)->get();
            $insufficientStock = [];

            if (!$allowNegativeStock) {
                foreach ($details as $detail) {
                    $product = Product::find($detail->product_id);
                    if ($product && $product->stock < $detail->quantity) {
                        $insufficientStock[] = "{$product->name} (stok: {$product->stock}, diminta: {$detail->quantity})";
                    }
                }

                if (!empty($insufficientStock)) {
                    abort(422, 'Stok tidak mencukupi: ' . implode(', ', $insufficientStock));
                }
            }

            $oldStatus = $order->order_status;

            foreach ($details as $detail) {
                Product::where('id', $detail->product_id)
                    ->decrement('stock', $detail->quantity);
            }

            $order->update(['order_status' => 'complete']);

            // Audit log
            AuditService::log('order', 'complete', $order, ['order_status' => $oldStatus], ['order_status' => 'complete'], "Order {$order->invoice_no} completed, stock reduced");
        });

        return Redirect::route('order.pendingOrders')->with('success', 'Order has been completed!');
    }

    /**
     * Cancel a pending order with reason.
     */
    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|numeric|exists:orders,id',
            'cancel_reason' => 'required|string|max:500',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (!$order->canBeCancelled()) {
            return Redirect::back()->with('error', 'Only pending orders can be cancelled.');
        }

        $oldStatus = $order->order_status;

        $order->update([
            'order_status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
            'cancelled_by' => auth()->id(),
            'cancelled_at' => Carbon::now(),
        ]);

        // Audit log
        AuditService::log('order', 'cancel', $order, ['order_status' => $oldStatus], ['order_status' => 'cancelled', 'cancel_reason' => $request->cancel_reason], "Order {$order->invoice_no} cancelled");

        return Redirect::route('order.pendingOrders')->with('success', 'Order has been cancelled.');
    }

    /**
     * Void a completed order with reason and restore stock.
     * Requires 'void.order' permission.
     */
    public function voidOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|numeric|exists:orders,id',
            'void_reason' => 'required|string|max:500',
        ]);

        $order = Order::findOrFail($request->order_id);

        if (!$order->canBeVoided()) {
            return Redirect::back()->with('error', 'Only completed orders can be voided.');
        }

        DB::transaction(function () use ($order, $request) {
            $oldStatus = $order->order_status;

            // Restore stock
            $details = OrderDetails::where('order_id', $order->id)->get();
            foreach ($details as $detail) {
                Product::where('id', $detail->product_id)
                    ->increment('stock', $detail->quantity);
            }

            $order->update([
                'order_status' => 'void',
                'void_reason' => $request->void_reason,
                'voided_by' => auth()->id(),
                'voided_at' => Carbon::now(),
            ]);

            // Audit log
            AuditService::log('order', 'void', $order, ['order_status' => $oldStatus], ['order_status' => 'void', 'void_reason' => $request->void_reason], "Order {$order->invoice_no} voided, stock restored");
        });

        return Redirect::route('order.completeOrders')->with('success', 'Order has been voided and stock restored.');
    }

    public function invoiceDownload(int $order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
            ->where('order_id', $order_id)
            ->orderBy('id', 'DESC')
            ->get();

        return view('pos.print-invoice', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }

    public function printReceipt(int $order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        $orderDetails = OrderDetails::with('product')
                        ->where('order_id', $order_id)
                        ->orderBy('id', 'DESC')
                        ->get();

        return view('pos.print-receipt', [
            'order' => $order,
            'orderDetails' => $orderDetails,
        ]);
    }

    public function pendingDue()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $orders = QueryBuilder::for(Order::class)
            ->where('due_amount', '>', 0)
            ->allowedSorts([
                'order_date',
                'due_amount',
                'pay_amount',
                AllowedSort::callback('customer.name', function ($query, $descending) {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')->orderBy('customers.name', $descending ? 'DESC' : 'ASC')->select('orders.*');
                })
            ])
            ->with('customer')
            ->paginate($row);

        return view('orders.pending-due', [
            'orders' => $orders
        ]);
    }

    public function orderDueAjax(int $id)
    {
        $order = Order::findOrFail($id);

        return response()->json($order);
    }

    public function updateDue(Request $request)
    {
        $request->validate([
            'order_id' => 'required|numeric',
            'due_amount' => 'required|numeric',
        ]);

        $order = Order::findOrFail($request->order_id);
        $mainPay = $order->pay_amount;
        $mainDue = $order->due_amount;

        $paid_due = $mainDue - $request->due_amount;
        $paid_pay = $mainPay + $request->due_amount;

        // Prevent negative due
        if ($paid_due < 0) {
            return Redirect::back()->with('error', 'Jumlah bayar melebihi sisa piutang!');
        }

        $oldValues = ['pay_amount' => $mainPay, 'due_amount' => $mainDue];

        $order->update([
            'due_amount' => $paid_due,
            'pay_amount' => $paid_pay,
        ]);

        // Audit log
        AuditService::log('order', 'update_due', $order, $oldValues, ['pay_amount' => $paid_pay, 'due_amount' => $paid_due], "Due payment for {$order->invoice_no}");

        return Redirect::route('order.pendingDue')->with('success', 'Due Amount Updated Successfully!');
    }
}
