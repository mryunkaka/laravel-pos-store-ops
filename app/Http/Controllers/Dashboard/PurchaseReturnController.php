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
        $receivings = PurchaseReceiving::where('status', 'completed')->with('supplier')->get();
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

            // Create return details
            foreach ($request->items as $item) {
                PurchaseReturnDetail::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'description' => $item['description'] ?? null
                ]);

                // Record stock movement (stock decreases)
                StockMovement::recordOut(
                    $item['product_id'],
                    $item['quantity'],
                    'purchase_return',
                    $return->id,
                    "Retur pembelian #{$returnNumber}"
                );
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
