<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\StockAdjustment;
use App\Models\Product;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of adjustments.
     */
    public function index(Request $request)
    {
        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = StockAdjustment::with(['product', 'user']);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($startDate) {
            $query->whereDate('adjustment_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('adjustment_date', '<=', $endDate);
        }

        $adjustments = $query->latest()->paginate(15)->withQueryString();

        return view('stock-adjustments.index', compact('adjustments'));
    }

    /**
     * Show the form for creating a new adjustment.
     */
    public function create()
    {
        $products = Product::all();
        return view('stock-adjustments.create', compact('products'));
    }

    /**
     * Store a newly created adjustment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|min:3'
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = $request->quantity;
            $oldStock = $product->stock;

            if ($request->adjustment_type === 'increase') {
                $newStock = $oldStock + $quantity;
                $product->update(['stock' => $newStock]);

                // Record stock movement (stock increases)
                StockMovement::recordIn(
                    $product->id,
                    $quantity,
                    'stock_adjustment',
                    null,
                    "Penyesuaian stok (increase): {$request->reason}"
                );
            } else {
                $newStock = $oldStock - $quantity;
                $product->update(['stock' => $newStock]);

                // Record stock movement (stock decreases)
                StockMovement::recordOut(
                    $product->id,
                    $quantity,
                    'stock_adjustment',
                    null,
                    "Penyesuaian stok (decrease): {$request->reason}"
                );
            }

            // Create adjustment record
            StockAdjustment::create([
                'product_id' => $product->id,
                'adjustment_date' => $request->adjustment_date,
                'adjustment_type' => $request->adjustment_type,
                'quantity' => $quantity,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'reason' => $request->reason,
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return redirect()->route('stock-adjustments.index')
                ->with('success', 'Penyesuaian stok berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat penyesuaian stok: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified adjustment.
     */
    public function show(StockAdjustment $adjustment)
    {
        $adjustment->load(['product', 'user']);
        return view('stock-adjustments.show', compact('adjustment'));
    }

    /**
     * Remove the specified adjustment.
     */
    public function destroy(StockAdjustment $adjustment)
    {
        if ($adjustment->deleted_at) {
            return redirect()->back()->with('error', 'Penyesuaian sudah dihapus');
        }

        // Restore stock
        $product = $adjustment->product;
        $oldStock = $adjustment->old_stock;
        $product->update(['stock' => $oldStock]);

        // Reverse stock movement
        if ($adjustment->adjustment_type === 'increase') {
            StockMovement::where('reference_type', 'stock_adjustment')
                ->where('reference_id', $adjustment->id)
                ->update(['quantity' => 0]);
        } else {
            StockMovement::where('reference_type', 'stock_adjustment')
                ->where('reference_id', $adjustment->id)
                ->update(['quantity' => 0]);
        }

        $adjustment->delete();

        return redirect()->route('stock-adjustments.index')
            ->with('success', 'Penyesuaian stok berhasil dihapus');
    }
}
