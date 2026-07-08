<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => 'image|file|max:1024|nullable',
            'name' => 'required|string|unique:products,name',
            'material' => 'nullable|string|max:255',
            'print_size' => 'nullable|string|max:255',
            'print_notes' => 'nullable|string|max:1000',
            'code' => 'nullable|string|unique:products,code|max:50',
            'category_id' => 'required|integer|exists:categories,id',
            'stock' => 'required|integer',
            'minimum_stock' => 'nullable|integer|min:0',
            'buying_price' => 'required|integer',
            'selling_price' => 'required|integer',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'wholesale_price' => 'nullable|numeric|min:0',
            'wholesale_qty' => 'nullable|integer|min:1',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'buying_date' => 'date_format:Y-m-d|nullable',
            'expire_date' => 'date_format:Y-m-d|nullable',
        ];
    }
}
