<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferDetail;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = StockTransfer::with(['fromLocation', 'toLocation', 'creator']);

        if ($status) {
            $query->where('status', $status);
        }

        $transfers = $query->latest()->paginate(20)->withQueryString();

        return view('stock-transfers.index', compact('transfers', 'status'));
    }

    public function create()
    {
        $locations = StockLocation::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('stock-transfers.create', compact('locations', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_location_id' => 'required|exists:stock_locations,id|different:to_location_id',
            'to_location_id' => 'required|exists:stock_locations,id',
            'transfer_date' => 'required|date',
            'reason' => 'required|string|min:3|max:500',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request) {
            $transferNumber = IdGenerator::generate([
                'table' => 'stock_transfers',
                'field' => 'transfer_number',
                'length' => 12,
                'prefix' => 'TRF-',
            ]);

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_location_id' => $request->from_location_id,
                'to_location_id' => $request->to_location_id,
                'transfer_date' => $request->transfer_date,
                'status' => 'pending',
                'reason' => $request->reason,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                StockTransferDetail::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            return redirect()->route('stock-transfers.index')
                ->with('success', 'Transfer stok berhasil dibuat sebagai pending.');
        });
    }

    public function show(StockTransfer $stockTransfer)
    {
        $stockTransfer->load(['fromLocation', 'toLocation', 'creator', 'completer', 'details.product']);

        return view('stock-transfers.show', compact('stockTransfer'));
    }

    public function complete(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Transfer stok ini sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($stockTransfer) {
            $stockTransfer->load(['fromLocation', 'toLocation', 'details.product']);

            foreach ($stockTransfer->details as $detail) {
                $product = $detail->product;

                if ($product->stock < $detail->quantity && !auth()->user()->can('allow-negative-stock')) {
                    abort(422, "Stok {$product->name} tidak cukup untuk transfer.");
                }

                StockMovement::recordOut(
                    $product,
                    $detail->quantity,
                    "Transfer stok {$stockTransfer->transfer_number} dari {$stockTransfer->fromLocation->name}",
                    auth()->user(),
                    StockTransfer::class,
                    $stockTransfer->id
                );

                StockMovement::recordIn(
                    $product,
                    $detail->quantity,
                    "Transfer stok {$stockTransfer->transfer_number} ke {$stockTransfer->toLocation->name}",
                    auth()->user(),
                    StockTransfer::class,
                    $stockTransfer->id
                );
            }

            $stockTransfer->update([
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Transfer stok berhasil diselesaikan.');
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        if ($stockTransfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya transfer pending yang bisa dihapus.');
        }

        $stockTransfer->delete();

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Transfer stok berhasil dihapus.');
    }
}
