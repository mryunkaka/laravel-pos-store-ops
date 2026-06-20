<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    /**
     * Search product by barcode.
     */
    public function search(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $product = Product::where('code', $request->code)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'price' => $product->selling_price,
                'discount' => $product->discount,
                'discount_type' => $product->discount_type,
                'stock' => $product->stock,
            ],
        ]);
    }

    /**
     * Quick add product to cart by barcode (for AJAX/POS scanner).
     */
    public function quickAdd(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $product = Product::where('code', $request->code)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        // Stock check
        $allowNegativeStock = $request->user()->can('allow-negative-stock');
        if (!$allowNegativeStock && $product->stock < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'price' => $product->selling_price,
                'discount' => $product->discount,
                'discount_type' => $product->discount_type,
                'stock' => $product->stock,
            ],
        ]);
    }
}
