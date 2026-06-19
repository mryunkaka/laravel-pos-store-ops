<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashShift;
use App\Models\CashShiftDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashShiftController extends Controller
{
    /**
     * Display a listing of shifts.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = CashShift::with(['user'])
            ->where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('start_time', '<=', $endDate);
        }

        $shifts = $query->latest()->paginate(15)->withQueryString();

        return view('cash-shifts.index', compact('shifts'));
    }

    /**
     * Show the form for creating a new shift.
     */
    public function create()
    {
        $activeShift = CashShift::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if ($activeShift) {
            return redirect()->route('cash-shifts.show', $activeShift->id)
                ->with('error', 'Anda sudah memiliki shift aktif');
        }

        return view('cash-shifts.create');
    }

    /**
     * Store a newly created shift.
     */
    public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $shift = CashShift::create([
                'user_id' => auth()->id(),
                'location_id' => 1,
                'start_time' => now(),
                'opening_balance' => $request->opening_balance,
                'status' => 'active'
            ]);

            // Record opening balance as cash_in transaction
            CashShiftDetail::create([
                'cash_shift_id' => $shift->id,
                'transaction_type' => 'cash_in',
                'amount' => $request->opening_balance,
                'payment_type' => 'cash',
                'description' => 'Opening balance',
                'transaction_time' => now()
            ]);

            DB::commit();

            return redirect()->route('cash-shifts.show', $shift->id)
                ->with('success', 'Shift kasir berhasil dibuka');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuka shift: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shift.
     */
    public function show(CashShift $shift)
    {
        if ($shift->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $shift->load(['details' => function ($query) {
            $query->latest('transaction_time');
        }, 'user']);

        return view('cash-shifts.show', compact('shift'));
    }

    /**
     * Record cash in/out during an active shift.
     */
    public function storeMovement(Request $request, CashShift $shift)
    {
        if ($shift->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($shift->status !== 'active') {
            return redirect()->back()->with('error', 'Kas masuk/keluar hanya bisa dicatat pada shift aktif.');
        }

        $request->validate([
            'transaction_type' => 'required|in:cash_in,cash_out',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:500',
        ]);

        CashShiftDetail::create([
            'cash_shift_id' => $shift->id,
            'transaction_type' => $request->transaction_type,
            'amount' => $request->amount,
            'payment_type' => 'cash',
            'description' => $request->description,
            'transaction_time' => now(),
        ]);

        return redirect()->route('cash-shifts.show', $shift->id)
            ->with('success', 'Catatan kas berhasil ditambahkan.');
    }

    /**
     * Print shift report.
     */
    public function print(CashShift $shift)
    {
        if ($shift->user_id !== auth()->id() && !auth()->user()->can('audit.menu')) {
            abort(403, 'Unauthorized');
        }

        $shift->load(['details.order', 'user', 'approvedBy']);

        return view('cash-shifts.print', compact('shift'));
    }

    /**
     * Close the specified shift.
     */
    public function close(Request $request, CashShift $shift)
    {
        if ($shift->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($shift->status !== 'active') {
            return redirect()->back()->with('error', 'Shift ini sudah ditutup');
        }

        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'closing_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $sales = $shift->details()->where('transaction_type', 'sale')->sum('amount');
            $cashSales = $shift->details()->where('transaction_type', 'sale')
                ->where('payment_type', 'cash')
                ->sum('amount');
            $nonCashSales = $shift->details()->where('transaction_type', 'sale')
                ->where('payment_type', '!=', 'cash')
                ->sum('amount');
            $void = $shift->details()->where('transaction_type', 'void')->sum('amount');
            $refund = $shift->details()->where('transaction_type', 'refund')->sum('amount');
            $cashIn = $shift->details()->where('transaction_type', 'cash_in')->sum('amount');
            $cashOut = $shift->details()->where('transaction_type', 'cash_out')->sum('amount');

            $shift->update([
                'status' => 'closed',
                'end_time' => now(),
                'closing_balance' => $request->closing_balance,
                'total_sales' => $sales,
                'total_cash' => $cashSales,
                'total_non_cash' => $nonCashSales,
                'total_void' => $void,
                'total_refund' => $refund,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'closing_notes' => $request->closing_notes ?: sprintf(
                    'Ekspektasi kas: Rp %s. Selisih: Rp %s.',
                    number_format(($cashIn + $cashSales - $cashOut - $refund), 0, ',', '.'),
                    number_format($request->closing_balance - ($cashIn + $cashSales - $cashOut - $refund), 0, ',', '.')
                )
            ]);

            DB::commit();

            return redirect()->route('cash-shifts.index')
                ->with('success', 'Shift kasir berhasil ditutup');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menutup shift: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified shift.
     */
    public function destroy(CashShift $shift)
    {
        if ($shift->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($shift->status !== 'active') {
            return redirect()->back()->with('error', 'Hanya shift aktif yang bisa dihapus');
        }

        $shift->delete();

        return redirect()->route('cash-shifts.index')
            ->with('success', 'Shift kasir berhasil dihapus');
    }
}
