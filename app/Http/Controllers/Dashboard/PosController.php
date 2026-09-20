<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashShift;
use App\Models\StoreSetting;
use App\Models\Voucher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Gloudemans\Shoppingcart\Facades\Cart;
use Spatie\QueryBuilder\QueryBuilder;

class PosController extends Controller
{
    private const SCANNER_TTL_MINUTES = 480;

    public function createScannerChannel()
    {
        $channel = Str::random(48);
        $expiresAt = now()->addMinutes(self::SCANNER_TTL_MINUTES);
        $ttl = $expiresAt->diffInSeconds(now());

        $cache = $this->scannerCache();
        $cache->put($this->scannerChannelKey($channel), [
            'user_id' => auth()->id(),
            'expires_at' => $expiresAt->toIso8601String(),
        ], $ttl);
        $cache->put($this->scannerEventsKey($channel), [], $ttl);
        $cache->put($this->scannerSequenceKey($channel), 0, $ttl);

        $scannerPath = URL::temporarySignedRoute('pos.scanner.remote', $expiresAt, ['channel' => $channel], false);

        return response()->json([
            'success' => true,
            'channel' => $channel,
            'scanner_url' => $this->scannerUrl($scannerPath),
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    public function scannerEvents(Request $request)
    {
        $validated = $request->validate([
            'channel' => 'required|string|size:48',
            'after' => 'nullable|integer|min:0',
        ]);
        $channel = $this->scannerChannel($validated['channel']);

        abort_unless((int) ($channel['user_id'] ?? 0) === (int) auth()->id(), 403);

        $after = (int) ($validated['after'] ?? 0);
        $events = collect($this->scannerCache()->get($this->scannerEventsKey($validated['channel']), []))
            ->filter(fn (array $event) => (int) ($event['id'] ?? 0) > $after)
            ->values();

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }

    public function scannerPage(string $channel)
    {
        $channelData = $this->scannerChannel($channel);
        $expiresAt = Carbon::parse($channelData['expires_at']);

        $lookupPath = URL::temporarySignedRoute('pos.scanner.lookup', $expiresAt, ['channel' => $channel], false);
        $scanPath = URL::temporarySignedRoute('pos.scanner.scan', $expiresAt, ['channel' => $channel], false);

        return view('pos.scanner', [
            'lookupUrl' => $this->scannerUrl($lookupPath),
            'scanUrl' => $this->scannerUrl($scanPath),
        ]);
    }

    public function lookupScannerCode(Request $request, string $channel)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);
        $this->scannerChannel($channel);

        $product = Product::where('code', trim($validated['code']))->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        if ($product->expire_date && Carbon::parse($product->expire_date)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Produk sudah kadaluarsa.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'name' => $product->name,
                'code' => $product->code,
                'price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
            ],
        ]);
    }

    public function receiveScannerCode(Request $request, string $channel)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
        ]);
        $this->scannerChannel($channel);
        $cache = $this->scannerCache();
        $events = $cache->get($this->scannerEventsKey($channel), []);
        $eventId = (int) $cache->increment($this->scannerSequenceKey($channel));

        $events[] = [
            'id' => $eventId,
            'code' => trim($validated['code']),
            'created_at' => now()->toIso8601String(),
        ];
        $cache->put($this->scannerEventsKey($channel), array_slice($events, -50), self::SCANNER_TTL_MINUTES * 60);

        return response()->json([
            'success' => true,
            'message' => 'Barcode diterima kasir.',
        ]);
    }

    private function scannerChannel(string $channel): array
    {
        $data = $this->scannerCache()->get($this->scannerChannelKey($channel));

        abort_unless(is_array($data), 404, 'Sesi scanner sudah berakhir.');
        abort_if(Carbon::parse($data['expires_at'])->isPast(), 404, 'Sesi scanner sudah berakhir.');

        return $data;
    }

    private function scannerCache()
    {
        return Cache::store('file');
    }

    private function scannerChannelKey(string $channel): string
    {
        return 'pos_scanner_channel:'.$channel;
    }

    private function scannerEventsKey(string $channel): string
    {
        return 'pos_scanner_events:'.$channel;
    }

    private function scannerSequenceKey(string $channel): string
    {
        return 'pos_scanner_sequence:'.$channel;
    }

    private function scannerUrl(string $path): string
    {
        $host = request()->getHost();

        if ($host === 'localhost' || $host === '127.0.0.1') {
            $host = gethostbyname(gethostname());
        }

        return request()->getScheme().'://'.$host.':'.request()->getPort().$path;
    }

    /**
     * Display the POS interface.
     */
    public function index()
    {
        // Validasi shift aktif
        $activeShift = CashShift::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$activeShift) {
            return redirect()->route('cash-shifts.create')
                ->with('error', 'Anda harus membuka shift kasir terlebih dahulu sebelum bertransaksi.');
        }

        $todayDate = Carbon::now();
        $row = (int) request('row', 10);
        $categoryId = request()->integer('category_id');

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        $defaultCustomer = Customer::firstOrCreate(
            ['name' => 'Walk-in Customer'],
            [
                'email' => 'walkin@store.com',
                'phone' => '0000000000',
                'address' => 'Store Location',
            ]
        );

        return view('pos.index', [
            'categories' => Category::orderBy('name')->get(),
            'defaultCustomer' => $defaultCustomer,
            'productItem' => Cart::content(),
            'products' => QueryBuilder::for(Product::class)
                ->where('expire_date', '>', $todayDate)
                ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
                ->allowedSorts(['name', 'selling_price'])
                ->allowedFilters(['name', 'category_id'])
                ->filter(request(['search', 'category_id']))
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    /**
     * Calculate item discount (fixed amount or percentage).
     */
    private function calculateItemDiscount(Product $product, $unitPrice): float
    {
        if ($product->discount <= 0) {
            return 0;
        }

        if ($product->discount_type === 'percentage') {
            return ($product->discount / 100) * $unitPrice;
        }

        // Fixed discount
        return min($product->discount, $unitPrice);
    }

    private function effectiveUnitPrice(Product $product, int $qty): float
    {
        if ($product->wholesale_price && $product->wholesale_qty && $qty >= $product->wholesale_qty) {
            return (float) $product->wholesale_price;
        }

        return (float) $product->selling_price;
    }

    /**
     * Add item to the cart.
     */
    public function addCart(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|numeric',
            'name' => 'required|string',
            'price' => 'required|numeric',
        ]);

        // Stock validation
        $product = Product::findOrFail($validatedData['id']);
        if ($product->expire_date && Carbon::parse($product->expire_date)->isPast()) {
            $message = 'Produk sudah kadaluarsa.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return Redirect::back()->with('error', $message);
        }

        $currentQtyInCart = 0;
        $existingRowId = null;
        foreach (Cart::content() as $item) {
            if ($item->id == $validatedData['id']) {
                $currentQtyInCart = $item->qty;
                $existingRowId = $item->rowId;
                break;
            }
        }

        $newQty = $currentQtyInCart + 1;
        if (!$request->user()->can('allow-negative-stock') && $product->stock < $newQty) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi! Stok tersedia: {$product->stock}",
                ], 422);
            }
            return Redirect::back()->with('error', "Stok tidak mencukupi! Stok tersedia: {$product->stock}");
        }

        // Hitung diskon untuk item ini
        $unitPrice = $this->effectiveUnitPrice($product, $newQty);
        $discount = $this->calculateItemDiscount($product, $unitPrice);

        $cartPayload = [
            'price' => $unitPrice,
            'options' => [
                'size' => 'large',
                'discount' => $discount,
                'discount_type' => $product->discount_type ?? 'fixed',
                'tax_rate' => $product->tax_rate > 0 ? $product->tax_rate : (($product->category->tax_rate ?? 0) ?: StoreSetting::current()->default_tax_rate),
            ],
        ];

        if ($existingRowId) {
            Cart::update($existingRowId, array_merge($cartPayload, [
                'qty' => $newQty,
            ]));
        } else {
            Cart::add(array_merge($cartPayload, [
                'id' => $validatedData['id'],
                'name' => $validatedData['name'],
                'qty' => 1,
            ]));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product has been added!',
                'cart_html' => view('pos.cart-sidebar', [
                    'productItem' => Cart::content()
                ])->render(),
                'cart_count' => Cart::count()
            ]);
        }

        return Redirect::back()->with('success', 'Product has been added!');
    }

    /**
     * Update item quantity in the cart.
     */
    public function updateCart(Request $request, string $rowId)
    {
        $validatedData = $request->validate([
            'qty' => 'required|numeric|min:1',
        ]);

        // Stock validation
        $cartItem = Cart::get($rowId);
        if ($cartItem) {
            $product = Product::find($cartItem->id);
            if ($product && !$request->user()->can('allow-negative-stock')) {
                if ($product->stock < $validatedData['qty']) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok tidak mencukupi! Stok tersedia: {$product->stock}",
                        ], 422);
                    }
                    return Redirect::back()->with('error', "Stok tidak mencukupi! Stok tersedia: {$product->stock}");
                }
            }
        }

        // Update cart dengan discount yang baru (untuk handle perubahan qty)
        $cartItem = Cart::get($rowId);
        if ($cartItem) {
            $product = Product::find($cartItem->id);
            $newQty = $validatedData['qty'];
            $unitPrice = $this->effectiveUnitPrice($product, $newQty);
            $discount = $this->calculateItemDiscount($product, $unitPrice);

            Cart::update($rowId, [
                'qty' => $newQty,
                'price' => $unitPrice,
                'options' => [
                    'discount' => $discount,
                    'discount_type' => $product->discount_type ?? 'fixed',
                    'tax_rate' => $product->tax_rate > 0 ? $product->tax_rate : (($product->category->tax_rate ?? 0) ?: StoreSetting::current()->default_tax_rate),
                ]
            ]);
        } else {
            Cart::update($rowId, $validatedData['qty']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart has been updated!',
                'cart_html' => view('pos.cart-sidebar', [
                    'productItem' => Cart::content()
                ])->render(),
                'cart_count' => Cart::count()
            ]);
        }

        return Redirect::back()->with('success', 'Cart has been updated!');
    }

    /**
     * Remove item from the cart.
     */
    public function deleteCart(Request $request, string $rowId)
    {
        Cart::remove($rowId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart has been deleted!',
                'cart_html' => view('pos.cart-sidebar', [
                    'productItem' => Cart::content()
                ])->render(),
                'cart_count' => Cart::count()
            ]);
        }

        return Redirect::back()->with('success', 'Cart has been deleted!');
    }

    /**
     * Preview voucher discount for current cart (AJAX).
     */
    public function voucherPreview(Request $request)
    {
        $voucherCode = strtoupper(trim((string) $request->input('voucher_code', '')));
        $baseTotal = max((float) $request->input('base_total', 0), 0);

        if ($voucherCode === '') {
            return response()->json([
                'success' => true,
                'discount' => 0,
                'message' => '',
            ]);
        }

        $voucher = Voucher::where('code', $voucherCode)->first();

        if (!$voucher || !$voucher->canUse()) {
            return response()->json([
                'success' => false,
                'discount' => 0,
                'message' => 'Voucher tidak valid atau sudah tidak aktif.',
            ], 422);
        }

        if ($baseTotal < $voucher->min_purchase) {
            return response()->json([
                'success' => false,
                'discount' => 0,
                'message' => 'Minimal belanja untuk voucher belum terpenuhi.',
            ], 422);
        }

        if ($voucher->type === 'percentage') {
            $discount = $baseTotal * ($voucher->discount / 100);
            if ($voucher->max_discount) {
                $discount = min($discount, $voucher->max_discount);
            }
        } else {
            $discount = $voucher->discount;
        }

        $discount = min($discount, $baseTotal);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => 'Voucher diterapkan: -' . format_rupiah($discount),
        ]);
    }

    /**
     * Store a newly created Customer (AJAX).
     */
    public function storeCustomer(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'nullable|email|max:50|unique:customers,email',
            'phone' => 'nullable|string|max:15|unique:customers,phone',
            'city' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:100',
        ]);

        $customer = Customer::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully!',
            'customer' => $customer
        ]);
    }

    /**
     * Search Customers for Select2 (AJAX).
     */
    public function searchCustomers(Request $request)
    {
        $term = $request->term;
        $query = Customer::query();

        if ($term) {
            $query->where('name', 'LIKE', "%{$term}%")
                ->orWhere('phone', 'LIKE', "%{$term}%");
        }

        $customers = $query->latest()->limit(20)->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'text' => $customer->name . ' (' . ($customer->phone ?? 'N/A') . ')'
            ];
        });

        return response()->json(['results' => $customers]);
    }
}
