<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceiving;
use App\Models\PurchaseReceivingDetail;
use App\Models\StockMovement;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReceivingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $supplierId = $request->input('supplier_id');

        $query = PurchaseReceiving::with(['purchaseOrder', 'supplier', 'receiver']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $receivings = $query->latest()->paginate(20)->withQueryString();

        return view('purchase-receivings.index', [
            'receivings' => $receivings,
            'filters' => [
                'status' => $status,
                'supplier_id' => $supplierId,
            ],
        ]);
    }

    public function create()
    {
        $purchaseOrders = PurchaseOrder::where('status', 'pending')
            ->with(['supplier', 'details.product', 'details.receivingDetails'])
            ->get();

        return view('purchase-receivings.create', compact('purchaseOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receiving_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_detail_id' => 'required|exists:purchase_order_details,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.received_quantity' => 'required|integer|min:0',
            'items.*.rejected_quantity' => 'nullable|integer|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::with('details')->findOrFail($request->purchase_order_id);
            $receivingNumber = IdGenerator::generate([
                'table' => 'purchase_receivings',
                'field' => 'receiving_number',
                'length' => 10,
                'prefix' => 'REC-',
            ]);

            $subTotal = 0;
            $validItems = [];

            foreach ($request->items as $item) {
                $detail = $purchaseOrder->details->firstWhere('id', (int) $item['purchase_order_detail_id']);
                if (!$detail || (int) $detail->product_id !== (int) $item['product_id']) {
                    abort(422, 'Item penerimaan tidak sesuai dengan purchase order.');
                }

                $receivedQty = (int) $item['received_quantity'];
                $rejectedQty = (int) ($item['rejected_quantity'] ?? 0);

                if ($receivedQty < 1 && $rejectedQty < 1) {
                    continue;
                }

                if (($receivedQty + $rejectedQty) > $detail->pending_quantity) {
                    abort(422, "Qty penerimaan {$detail->product->name} melebihi sisa PO.");
                }

                $subTotal += $receivedQty * $detail->unit_price;
                $validItems[] = [$detail, $receivedQty, $rejectedQty, $item['notes'] ?? null];
            }

            if (empty($validItems)) {
                abort(422, 'Minimal satu item harus diterima atau ditolak.');
            }

            $receiving = PurchaseReceiving::create([
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'receiving_number' => $receivingNumber,
                'receiving_date' => $request->receiving_date,
                'status' => 'pending',
                'sub_total' => $subTotal,
                'vat' => 0,
                'total' => $subTotal,
                'received_by' => auth()->id(),
            ]);

            foreach ($validItems as [$detail, $receivedQty, $rejectedQty, $notes]) {
                PurchaseReceivingDetail::create([
                    'purchase_receiving_id' => $receiving->id,
                    'purchase_order_detail_id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'received_quantity' => $receivedQty,
                    'rejected_quantity' => $rejectedQty,
                    'notes' => $notes,
                ]);
            }

            return redirect()->route('purchase-receivings.index')
                ->with('success', 'Penerimaan barang berhasil disimpan sebagai pending.');
        });
    }

    public function show(PurchaseReceiving $receiving)
    {
        $receiving->load(['purchaseOrder', 'details.product', 'supplier', 'receiver']);

        return view('purchase-receivings.show', compact('receiving'));
    }

    public function complete(PurchaseReceiving $receiving)
    {
        if ($receiving->status !== 'pending') {
            return redirect()->back()->with('error', 'Penerimaan ini sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($receiving) {
            $receiving->load(['details.product', 'purchaseOrder.details.receivingDetails']);

            foreach ($receiving->details as $detail) {
                if ($detail->received_quantity < 1) {
                    continue;
                }

                $product = Product::findOrFail($detail->product_id);
                $product->increment('stock', $detail->received_quantity);

                StockMovement::recordIn(
                    $product,
                    $detail->received_quantity,
                    "Penerimaan barang {$receiving->receiving_number}",
                    auth()->user(),
                    PurchaseReceiving::class,
                    $receiving->id
                );
            }

            $receiving->update(['status' => 'completed']);

            $purchaseOrder = $receiving->purchaseOrder;
            $purchaseOrder->refresh();
            if ($purchaseOrder->canBeCompleted()) {
                $purchaseOrder->update(['status' => 'completed']);
            }
        });

        return redirect()->route('purchase-receivings.index')
            ->with('success', 'Penerimaan barang berhasil diselesaikan dan stok sudah diperbarui.');
    }

    public function destroy(PurchaseReceiving $receiving)
    {
        if ($receiving->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya penerimaan pending yang bisa dihapus.');
        }

        $receiving->delete();

        return redirect()->route('purchase-receivings.index')
            ->with('success', 'Penerimaan barang berhasil dihapus.');
    }
}
