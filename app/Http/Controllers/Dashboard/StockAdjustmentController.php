<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = StockAdjustment::with(['product', 'user']);
        $products = Product::orderBy('name')->get();

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

        return view('stock-adjustments.index', compact('adjustments', 'products'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('stock-adjustments.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'adjustment_date' => 'required|date',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|min:3',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($request->product_id);
            $quantity = (int) $request->quantity;
            $oldStock = (int) $product->stock;

            if ($request->adjustment_type === 'increase') {
                $newStock = $oldStock + $quantity;
                $adjustmentType = 'in';
                $movementQuantity = $quantity;
            } else {
                $newStock = $oldStock - $quantity;

                if ($newStock < 0 && !auth()->user()->can('allow-negative-stock')) {
                    DB::rollBack();

                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Stok tidak boleh minus.');
                }

                $adjustmentType = 'out';
                $movementQuantity = -$quantity;
            }

            $product->update(['stock' => $newStock]);

            $adjustment = StockAdjustment::create([
                'product_id' => $product->id,
                'adjustment_number' => IdGenerator::generate([
                    'table' => 'stock_adjustments',
                    'field' => 'adjustment_number',
                    'length' => 12,
                    'prefix' => 'ADJ-',
                ]),
                'adjustment_date' => $request->adjustment_date,
                'type' => $adjustmentType,
                'quantity' => $quantity,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'reason' => $request->reason,
                'status' => 'approved',
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            StockMovement::recordAdjustment(
                $product,
                $movementQuantity,
                "Penyesuaian stok: {$request->reason}",
                auth()->user(),
                StockAdjustment::class,
                $adjustment->id
            );

            DB::commit();

            return redirect()->route('stock-adjustments.index')
                ->with('success', 'Penyesuaian stok berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat penyesuaian stok: ' . $e->getMessage());
        }
    }

    public function show(StockAdjustment $adjustment)
    {
        $adjustment->load(['product', 'user', 'approver']);

        return view('stock-adjustments.show', compact('adjustment'));
    }

    public function destroy(StockAdjustment $adjustment)
    {
        return redirect()->route('stock-adjustments.index')
            ->with('error', 'Penyesuaian stok tidak boleh dihapus. Buat penyesuaian baru untuk koreksi.');
    }
}
