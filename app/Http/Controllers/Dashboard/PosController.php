<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CashShift;
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
        foreach (Cart::content() as $item) {
            if ($item->id == $validatedData['id']) {
                $currentQtyInCart = $item->qty;
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

        Cart::add([
            'id' => $validatedData['id'],
            'name' => $validatedData['name'],
            'qty' => 1,
            'price' => $validatedData['price'],
            'options' => ['size' => 'large']
        ]);

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

        Cart::update($rowId, $validatedData['qty']);

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
