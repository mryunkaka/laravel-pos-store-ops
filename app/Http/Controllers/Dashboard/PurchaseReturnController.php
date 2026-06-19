<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\PurchaseReceiving;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $supplierId = $request->input('supplier_id');

        $query = PurchaseReturn::with(['supplier', 'purchaseReceiving', 'user']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $returns = $query->latest()->paginate(15)->withQueryString();

        return view('purchase-returns.index', compact('returns'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $receivings = PurchaseReceiving::where('status', 'completed')
            ->with(['supplier', 'details.product'])
            ->get();
        $suppliers = Supplier::all();
        $products = Product::all();

        return view('purchase-returns.create', compact('receivings', 'suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_receiving_id' => 'required|exists:purchase_receivings,id',
            'return_date' => 'required|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $receiving = PurchaseReceiving::findOrFail($request->purchase_receiving_id);

            // Generate return number
            $returnNumber = IdGenerator::generate([
                'table' => 'purchase_returns',
                'field' => 'return_number',
                'length' => 10,
                'prefix' => 'RT-' . date('ymd')
            ]);

            // Calculate totals
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['total'];
            }

            $discount = $request->discount ?? 0;
            $grandTotal = $total - $discount;

            // Create purchase return
            $return = PurchaseReturn::create([
                'purchase_receiving_id' => $receiving->id,
                'supplier_id' => $receiving->supplier_id,
                'created_by' => auth()->id(),
                'return_number' => $returnNumber,
                'return_date' => $request->return_date,
                'description' => $request->description,
                'total' => $total,
                'discount' => $discount,
                'grand_total' => $grandTotal,
                'status' => 'pending'
            ]);

            $validItems = collect($request->items)->filter(function ($item) {
                return (int) $item['quantity'] > 0;
            });

            if ($validItems->isEmpty()) {
                abort(422, 'Minimal satu item retur harus diisi.');
            }

            foreach ($validItems as $item) {
                if ((int) $item['quantity'] < 1) {
                    continue;
                }

                PurchaseReturnDetail::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'description' => $item['description'] ?? null
                ]);
            }

            DB::commit();

            return redirect()->route('purchase-returns.index')
                ->with('success', 'Retur pembelian berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat retur pembelian: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseReturn $return)
    {
        $return->load(['supplier', 'purchaseReceiving', 'user', 'details.product']);

        return view('purchase-returns.show', compact('return'));
    }

    public function complete(PurchaseReturn $return)
    {
        if ($return->status !== 'pending') {
            return redirect()->back()->with('error', 'Retur pembelian ini sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($return) {
            $return->load('details.product');

            foreach ($return->details as $detail) {
                $product = Product::findOrFail($detail->product_id);

                if ($product->stock < $detail->quantity && !auth()->user()->can('allow-negative-stock')) {
                    abort(422, "Stok {$product->name} tidak cukup untuk retur.");
                }

                $product->decrement('stock', $detail->quantity);

                StockMovement::recordOut(
                    $product,
                    $detail->quantity,
                    "Retur pembelian {$return->return_number}",
                    auth()->user(),
                    PurchaseReturn::class,
                    $return->id
                );
            }

            $return->update(['status' => 'completed']);
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Retur pembelian berhasil diselesaikan dan stok sudah diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseReturn $return)
    {
        if ($return->status === 'completed') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus retur yang sudah selesai');
        }

        $return->delete();

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Retur pembelian berhasil dihapus');
    }
}
