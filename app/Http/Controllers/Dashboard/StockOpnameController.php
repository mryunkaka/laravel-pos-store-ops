<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['creator', 'approver'])
            ->withCount('details');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->whereDate('opname_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('opname_date', '<=', $request->end_date);
        }

        $opnames = $query->latest()->paginate(15)->withQueryString();

        return view('stock-opnames.index', compact('opnames'));
    }

    public function create()
    {
        $productCount = Product::count();

        return view('stock-opnames.create', compact('productCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'opname_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $opname = StockOpname::create([
                'opname_number' => IdGenerator::generate([
                    'table' => 'stock_opnames',
                    'field' => 'opname_number',
                    'length' => 12,
                    'prefix' => 'OPN-',
                ]),
                'opname_date' => $request->opname_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            Product::orderBy('name')->chunk(200, function ($products) use ($opname) {
                foreach ($products as $product) {
                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'product_id' => $product->id,
                        'system_stock' => (int) $product->stock,
                        'physical_stock' => null,
                        'difference' => 0,
                    ]);
                }
            });
        });

        return redirect()->route('stock-opnames.index')
            ->with('success', 'Stock opname berhasil dibuat. Silakan input stok fisik.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load([
            'creator',
            'submitter',
            'approver',
            'details.product' => function ($query) {
                $query->orderBy('name');
            },
        ]);

        return view('stock-opnames.show', compact('stockOpname'));
    }

    public function updateCounts(Request $request, StockOpname $stockOpname)
    {
        if (!$stockOpname->isDraft()) {
            return redirect()->back()->with('error', 'Stock opname yang sudah disubmit tidak bisa diubah.');
        }

        $request->validate([
            'details' => 'required|array',
            'details.*.physical_stock' => 'nullable|integer|min:0',
            'details.*.notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $stockOpname) {
            foreach ($request->details as $detailId => $input) {
                $detail = $stockOpname->details()->findOrFail($detailId);
                $physicalStock = $input['physical_stock'] === null || $input['physical_stock'] === ''
                    ? null
                    : (int) $input['physical_stock'];

                $detail->update([
                    'physical_stock' => $physicalStock,
                    'difference' => $physicalStock === null ? 0 : $physicalStock - $detail->system_stock,
                    'notes' => $input['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('stock-opnames.show', $stockOpname->id)
            ->with('success', 'Input stok fisik berhasil disimpan.');
    }

    public function importView(StockOpname $stockOpname)
    {
        if (!$stockOpname->isDraft()) {
            return redirect()->route('stock-opnames.show', $stockOpname->id)
                ->with('error', 'Import hanya bisa dilakukan pada draft stock opname.');
        }

        return view('stock-opnames.import', compact('stockOpname'));
    }

    public function importStore(Request $request, StockOpname $stockOpname)
    {
        if (!$stockOpname->isDraft()) {
            return redirect()->route('stock-opnames.show', $stockOpname->id)
                ->with('error', 'Import hanya bisa dilakukan pada draft stock opname.');
        }

        $request->validate([
            'upload_file' => 'required|file|mimes:xls,xlsx',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('upload_file')->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rowLimit = $sheet->getHighestDataRow();
            $updated = 0;

            DB::transaction(function () use ($sheet, $rowLimit, $stockOpname, &$updated) {
                for ($row = 2; $row <= $rowLimit; $row++) {
                    $productCode = trim((string) $sheet->getCell('A' . $row)->getValue());
                    $physicalStock = $sheet->getCell('C' . $row)->getValue();
                    $notes = $sheet->getCell('D' . $row)->getValue();

                    if ($productCode === '' || $physicalStock === null || $physicalStock === '') {
                        continue;
                    }

                    $detail = $stockOpname->details()
                        ->whereHas('product', function ($query) use ($productCode) {
                            $query->where('code', $productCode);
                        })
                        ->first();

                    if (!$detail) {
                        continue;
                    }

                    $physicalStock = max(0, (int) $physicalStock);
                    $detail->update([
                        'physical_stock' => $physicalStock,
                        'difference' => $physicalStock - $detail->system_stock,
                        'notes' => $notes,
                    ]);

                    $updated++;
                }
            });

            return redirect()->route('stock-opnames.show', $stockOpname->id)
                ->with('success', "Import selesai. {$updated} produk diperbarui.");
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Gagal import hasil opname: ' . $e->getMessage());
        }
    }

    public function submit(StockOpname $stockOpname)
    {
        if (!$stockOpname->isDraft()) {
            return redirect()->back()->with('error', 'Stock opname ini tidak bisa disubmit.');
        }

        $missingCount = $stockOpname->details()
            ->whereNull('physical_stock')
            ->count();

        if ($missingCount > 0) {
            return redirect()->back()
                ->with('error', "Masih ada {$missingCount} produk yang belum diinput stok fisiknya.");
        }

        $stockOpname->update([
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return redirect()->route('stock-opnames.show', $stockOpname->id)
            ->with('success', 'Stock opname berhasil disubmit untuk persetujuan.');
    }

    public function approve(StockOpname $stockOpname)
    {
        if (!$stockOpname->isSubmitted()) {
            return redirect()->back()->with('error', 'Hanya stock opname submitted yang bisa disetujui.');
        }

        DB::transaction(function () use ($stockOpname) {
            $stockOpname->load('details.product');

            foreach ($stockOpname->details as $detail) {
                if ($detail->difference === 0) {
                    continue;
                }

                $product = Product::lockForUpdate()->findOrFail($detail->product_id);
                $oldStock = (int) $product->stock;
                $newStock = (int) $detail->physical_stock;
                $movementQuantity = $newStock - $oldStock;

                if ($movementQuantity === 0) {
                    continue;
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
                    'adjustment_date' => now()->toDateString(),
                    'type' => $movementQuantity > 0 ? 'in' : 'out',
                    'quantity' => abs($movementQuantity),
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'reason' => "Stock opname {$stockOpname->opname_number}",
                    'status' => 'approved',
                    'created_by' => $stockOpname->created_by,
                    'approved_by' => auth()->id(),
                ]);

                StockMovement::recordAdjustment(
                    $product,
                    $movementQuantity,
                    "Stock opname {$stockOpname->opname_number}",
                    auth()->user(),
                    StockAdjustment::class,
                    $adjustment->id
                );
            }

            $stockOpname->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('stock-opnames.show', $stockOpname->id)
            ->with('success', 'Stock opname berhasil disetujui dan stok sudah disesuaikan.');
    }

    public function cancel(StockOpname $stockOpname)
    {
        if (!$stockOpname->isDraft()) {
            return redirect()->back()->with('error', 'Hanya draft stock opname yang bisa dibatalkan.');
        }

        $stockOpname->update(['status' => 'cancelled']);

        return redirect()->route('stock-opnames.index')
            ->with('success', 'Stock opname berhasil dibatalkan.');
    }
}
