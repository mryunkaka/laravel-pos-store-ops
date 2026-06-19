<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class StockMovementController extends Controller
{
    /**
     * Display stock movement history per product.
     */
    public function history(Product $product)
    {
        $summary = StockMovement::where('product_id', $product->id)
            ->select('type', DB::raw('SUM(ABS(quantity)) as total_quantity'))
            ->groupBy('type')
            ->pluck('total_quantity', 'type')
            ->toArray();

        $movements = QueryBuilder::for(StockMovement::class)
            ->where('product_id', $product->id)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::scope('date_range'),
            ])
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('stock-movements.history', [
            'product' => $product,
            'movements' => $movements,
            'summary' => $summary,
        ]);
    }

    /**
     * Display list of all stock movements.
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = StockMovement::with(['product', 'user']);

        if ($type) {
            $query->where('type', $type);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $movements = $query->latest()->paginate(20);
        $products = Product::orderBy('name')->get();

        return view('stock-movements.index', [
            'movements' => $movements,
            'products' => $products,
            'filters' => [
                'type' => $type,
                'product_id' => $productId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Record manual stock adjustment.
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'reason' => 'required|string|max:500',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int) $request->quantity;

        DB::transaction(function () use ($product, $quantity, $request) {
            if ($quantity > 0) {
                $product->increment('stock', $quantity);
            } else {
                $product->decrement('stock', abs($quantity));
            }

            $type = $quantity > 0 ? 'adjustment_in' : 'adjustment_out';
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $type,
                'reference_type' => Product::class,
                'reference_id' => $product->id,
                'quantity' => abs($quantity),
                'unit_price' => $product->buying_price,
                'description' => $request->reason,
                'reference_user_id' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Stok berhasil disesuaikan!');
    }

    /**
     * Show adjustment form.
     */
    public function showAdjustForm(Product $product)
    {
        return view('stock-movements.adjust', [
            'product' => $product,
        ]);
    }
}
