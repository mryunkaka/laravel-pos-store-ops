<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $supplierId = $request->input('supplier_id');

        $query = PurchaseOrder::with(['supplier', 'creator']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $purchaseOrders = $query->latest()->paginate(20);

        return view('purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => [
                'status' => $status,
                'supplier_id' => $supplierId,
            ],
        ]);
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        return view('purchase-orders.create', compact('suppliers'));
    }

    /**
     * Store a newly created purchase order in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $poNumber = IdGenerator::generate([
                'table' => 'purchase_orders',
                'field' => 'po_number',
                'length' => 10,
                'prefix' => 'PO-'
            ]);

            $subTotal = 0;
            $vat = 0;
            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $qty = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $itemTotal = $qty * $unitPrice;

                $subTotal += $itemTotal;
                // Hitung PPN (jika ada)
                $vat += 0;
                $total += $itemTotal;
            }

            $po = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'po_number' => $poNumber,
                'po_date' => $request->po_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'pending',
                'sub_total' => $subTotal,
                'vat' => $vat,
                'total' => $total,
                'created_by' => auth()->id(),
            ]);

            // Create order details
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $qty = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $itemTotal = $qty * $unitPrice;

                PurchaseOrderDetail::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $itemTotal,
                ]);
            }

            return redirect()->route('purchase-orders.index')
                ->with('success', 'Purchase Order berhasil dibuat!');
        });
    }

    /**
     * Display the specified purchase order.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'details.product', 'creator']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Remove the specified purchase order from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya PO dengan status pending yang bisa dihapus.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }

    /**
     * Cancel a pending purchase order.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500',
        ]);

        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya PO dengan status pending yang bisa dibatalkan.');
        }

        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil dibatalkan.');
    }
}
