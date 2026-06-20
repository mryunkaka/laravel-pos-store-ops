<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request)
    {
        $query = Voucher::query();

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $vouchers = $query->latest()->paginate(15)->withQueryString();

        return view('vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('vouchers.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);
        $validated['code'] = strtoupper($validated['code']);
        $validated['min_purchase'] = $validated['min_purchase'] ?? 0;
        $validated['max_discount'] = $validated['max_discount'] ?? null;
        $validated['max_use'] = $validated['max_use'] ?? null;
        $validated['is_active'] = $request->boolean('is_active');

        Voucher::create($validated);

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dibuat.');
    }

    public function edit(Voucher $voucher)
    {
        return view('vouchers.edit', compact('voucher'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        $validated = $this->validatedData($request, $voucher);
        $validated['code'] = strtoupper($validated['code']);
        $validated['min_purchase'] = $validated['min_purchase'] ?? 0;
        $validated['max_discount'] = $validated['max_discount'] ?? null;
        $validated['max_use'] = $validated['max_use'] ?? null;
        $validated['is_active'] = $request->boolean('is_active');

        $voucher->update($validated);

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->update(['is_active' => false]);

        return redirect()->route('vouchers.index')->with('success', 'Voucher berhasil dinonaktifkan.');
    }

    private function validatedData(Request $request, ?Voucher $voucher = null): array
    {
        $voucherId = $voucher?->id;

        return $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucherId,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,percentage',
            'discount' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|integer|min:0',
            'max_discount' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_use' => 'nullable|integer|min:1',
        ]);
    }
}
