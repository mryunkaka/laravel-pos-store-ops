<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashShift;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Gloudemans\Shoppingcart\Facades\Cart;
use Spatie\QueryBuilder\QueryBuilder;

class PosController extends Controller
{
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

        if ($row < 1 || $row > 100) {
            abort(400, 'The per-page parameter must be an integer between 1 and 100.');
        }

        return view('pos.index', [
            'categories' => Category::orderBy('name')->get(),
            'productItem' => Cart::content(),
            'products' => QueryBuilder::for(Product::class)
                ->where('expire_date', '>', $todayDate)
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
