<?php

namespace App\Http\Controllers\Dashboard;

use Exception;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductReference;
use App\Services\AuditService;
use Illuminate\Database\UniqueConstraintViolationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Picqer\Barcode\BarcodeGeneratorHTML;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\BulkDestroyProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $products = QueryBuilder::for(Product::class)
            ->allowedSorts([
                'name',
                'selling_price',
                AllowedSort::callback('category.name', function ($query, $descending) {
                    $query->join('categories', 'products.category_id', '=', 'categories.id')
                        ->orderBy('categories.name', $descending ? 'DESC' : 'ASC')
                        ->select('products.*');
                })
            ])
            ->allowedFilters(['name'])
            ->filter(request(['search']))
            ->with(['category'])
            ->paginate($row)
            ->appends(request()->query());

        $allProductIds = Product::query()
            ->filter(request(['search']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        return view('products.index', [
            'products' => $products,
            'allProductIds' => $allProductIds,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create', [
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['discount'] = $validatedData['discount'] ?? 0;
        $validatedData['discount_type'] = $validatedData['discount_type'] ?? 'fixed';
        $validatedData['tax_rate'] = $validatedData['tax_rate'] ?? 0;
        $validatedData['minimum_stock'] = $validatedData['minimum_stock'] ?? 0;
        $validatedData['wholesale_price'] = $validatedData['wholesale_price'] ?? null;
        $validatedData['wholesale_qty'] = $validatedData['wholesale_qty'] ?? null;

        // Generate code only if not provided
        if (!isset($validatedData['code']) || empty($validatedData['code'])) {
            $validatedData['code'] = IdGenerator::generate([
                'table' => 'products',
                'field' => 'code',
                'length' => 4,
                'prefix' => 'PC'
            ]);
        }

        $validatedData['slug'] = Str::slug($validatedData['name']);

        /**
         * Handle upload image with Storage.
         */
        $storedImage = null;
        if ($file = $request->file('image')) {
            $fileName = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();
            $path = 'public/products/';

            $file->storeAs($path, $fileName);
            $storedImage = $fileName;
            $validatedData['image'] = $fileName;
        }

        try {
            Product::create($validatedData);
        } catch (UniqueConstraintViolationException $exception) {
            if ($storedImage) {
                Storage::delete('public/products/' . $storedImage);
            }

            if (str_contains($exception->getMessage(), 'products_code_unique')) {
                return Redirect::back()
                    ->withInput()
                    ->withErrors(['code' => 'Kode produk sudah digunakan.']);
            }

            throw $exception;
        }

        return Redirect::route('products.index')->with('success', 'Product has been created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Barcode Generator
        $generator = new BarcodeGeneratorHTML();

        $barcode = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);

        return view('products.show', [
            'product' => $product,
            'barcode' => $barcode,
        ]);
    }

    public function barcodeLabel(Product $product)
    {
        $generator = new BarcodeGeneratorHTML();
        $barcode = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);

        return view('products.barcode-label', compact('product', 'barcode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return view('products.edit', [
            'categories' => Category::all(),
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();
        $validatedData['discount'] = $validatedData['discount'] ?? 0;
        $validatedData['discount_type'] = $validatedData['discount_type'] ?? 'fixed';
        $validatedData['tax_rate'] = $validatedData['tax_rate'] ?? 0;
        $validatedData['minimum_stock'] = $validatedData['minimum_stock'] ?? 0;
        $validatedData['wholesale_price'] = $validatedData['wholesale_price'] ?? null;
        $validatedData['wholesale_qty'] = $validatedData['wholesale_qty'] ?? null;
        $validatedData['slug'] = Str::slug($validatedData['name']);

        /**
         * Handle upload image with Storage.
         */
        $oldImage = $product->image;
        $storedImage = null;
        if ($file = $request->file('image')) {
            $fileName = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();
            $path = 'public/products/';

            $file->storeAs($path, $fileName);
            $storedImage = $fileName;
            $validatedData['image'] = $fileName;
        }

        try {
            $product->update($validatedData);
        } catch (UniqueConstraintViolationException $exception) {
            if ($storedImage) {
                Storage::delete('public/products/' . $storedImage);
            }

            if (str_contains($exception->getMessage(), 'products_code_unique')) {
                return Redirect::back()
                    ->withInput()
                    ->withErrors(['code' => 'Kode produk sudah digunakan.']);
            }

            throw $exception;
        }

        if ($storedImage && $oldImage) {
            Storage::delete('public/products/' . $oldImage);
        }

        // Audit log
        AuditService::log('product', 'update', $product, null, $validatedData, "Product {$product->name} updated");

        return Redirect::route('products.index')->with('success', 'Product has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($message = $this->pendingDeletionMessage($product->id)) {
            return Redirect::route('products.index')->with('error', $message);
        }

        DB::transaction(function () use ($product): void {
            ProductReference::archiveFromProduct($product);
            $product->forceDelete();
        });

        return Redirect::route('products.index')->with('success', 'Produk berhasil dihapus permanen. Riwayat tetap tersimpan.');
    }

    public function bulkDestroy(BulkDestroyProductRequest $request)
    {
        $products = Product::whereIn('id', $request->validated('product_ids'))->get();

        foreach ($products as $product) {
            if ($message = $this->pendingDeletionMessage($product->id)) {
                return Redirect::route('products.index')->with('error', $message);
            }
        }

        DB::transaction(function () use ($products): void {
            foreach ($products as $product) {
                ProductReference::archiveFromProduct($product);
                $product->forceDelete();
            }
        });

        return Redirect::route('products.index')->with('success', $products->count() . ' produk berhasil dihapus permanen. Riwayat tetap tersimpan.');
    }

    private function pendingDeletionMessage(int $productId): ?string
    {
        $checks = [
            ['order_details', 'orders', 'order_id', 'order_status', ['pending'], 'order penjualan pending'],
            ['purchase_order_details', 'purchase_orders', 'purchase_order_id', 'status', ['pending'], 'purchase order pending'],
            ['purchase_receiving_details', 'purchase_receivings', 'purchase_receiving_id', 'status', ['pending'], 'penerimaan pending'],
            ['purchase_return_details', 'purchase_returns', 'purchase_return_id', 'status', ['pending'], 'retur pembelian pending'],
            ['sales_return_details', 'sales_returns', 'sales_return_id', 'status', ['pending'], 'retur penjualan pending'],
            ['stock_transfer_details', 'stock_transfers', 'stock_transfer_id', 'status', ['pending'], 'transfer stok pending'],
            ['stock_opname_details', 'stock_opnames', 'stock_opname_id', 'status', ['draft', 'submitted'], 'stock opname aktif'],
        ];

        if (DB::table('stock_adjustments')
            ->where('product_id', $productId)
            ->where('status', 'pending')
            ->exists()) {
            return 'Produk tidak dapat dihapus karena masih dipakai penyesuaian stok pending. Selesaikan atau batalkan dokumen tersebut dulu.';
        }

        foreach ($checks as [$detailTable, $parentTable, $parentKey, $statusColumn, $statuses, $label]) {
            if (DB::table($detailTable)
                ->join($parentTable, $parentTable . '.id', '=', $detailTable . '.' . $parentKey)
                ->where($detailTable . '.product_id', $productId)
                ->whereIn($parentTable . '.' . $statusColumn, $statuses)
                ->exists()) {
                return "Produk tidak dapat dihapus karena masih dipakai {$label}. Selesaikan atau batalkan dokumen tersebut dulu.";
            }
        }

        return null;
    }

    /**
     * Show the form for importing a new resource.
     */
    public function importView()
    {
        return view('products.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'upload_file' => 'required|file|mimes:xls,xlsx',
        ]);

        $the_file = $request->file('upload_file');

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rowLimit = $sheet->getHighestDataRow();
            $data = [];

            for ($row = 2; $row <= $rowLimit; $row++) {
                $name = $sheet->getCell('A' . $row)->getValue();
                $data[] = [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category_id' => $sheet->getCell('B' . $row)->getValue(),
                    'code' => $sheet->getCell('C' . $row)->getValue(),
                    'image' => $sheet->getCell('D' . $row)->getValue(),
                    'stock' => $sheet->getCell('E' . $row)->getValue(),
                    'buying_date' => $sheet->getCell('F' . $row)->getValue(),
                    'expire_date' => $sheet->getCell('G' . $row)->getValue(),
                    'buying_price' => $sheet->getCell('H' . $row)->getValue(),
                    'selling_price' => $sheet->getCell('I' . $row)->getValue(),
                ];
            }

            Product::insert($data);

            Product::whereIn('code', collect($data)->pluck('code')->filter()->all())
                ->get()
                ->each(fn (Product $product) => ProductReference::ensureFromProduct($product));
        } catch (Exception $e) {
            return Redirect::route('products.index')->with('error', 'There was a problem uploading the data!');
        }

        return Redirect::route('products.index')->with('success', 'Data has been successfully imported!');
    }

    public function exportExcel(array $products)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet();
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($products);
            $Excel_writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="Products_ExportedData.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $Excel_writer->save('php://output');
            exit();
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * This function loads the customer data from the database then converts it
     * into an Array that will be exported to Excel
     */
    function exportData()
    {
        $products = Product::all()->sortByDesc('id');

        $product_array[] = array(
            'Product Name',
            'Category Id',
            'Product Code',
            'Product Image',
            'Stock',
            'Buying Date',
            'Expire Date',
            'Buying Price',
            'Selling Price',
        );

        foreach ($products as $product) {
            $product_array[] = array(
                'Product Name' => $product->name,
                'Category Id' => $product->category_id,
                'Product Code' => $product->code,
                'Product Image' => $product->image,
                'Stock' => $product->stock,
                'Buying Date' => $product->buying_date,
                'Expire Date' => $product->expire_date,
                'Buying Price' => $product->buying_price,
                'Selling Price' => $product->selling_price,
            );
        }

        $this->ExportExcel($product_array);
    }
}
