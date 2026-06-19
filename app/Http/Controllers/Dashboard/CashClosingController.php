<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashClosing;
use App\Models\CashClosingDetail;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashClosingController extends Controller
{
    /**
     * Display a listing of closings.
     */
    public function index(Request $request)
    {
        $locationId = 1; // Default single location
        $date = $request->input('date');
        $status = $request->input('status');

        $query = CashClosing::with(['details.cashShift.user'])
            ->where('location_id', $locationId);

        if ($date) {
            $query->where('closing_date', $date);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $closings = $query->latest()->paginate(15)->withQueryString();

        return view('cash-closings.index', compact('closings'));
    }

    /**
     * Show the form for creating a new closing.
     */
    public function create()
    {
        $date = $request->input('date', now()->toDateString());
        $shifts = CashShift::where('location_id', 1)
            ->whereDate('start_time', $date)
            ->where('status', 'closed')
            ->with(['user', 'details'])
            ->get();

        // Calculate summary from shifts
        $totalSales = $shifts->sum('total_sales');
        $totalCash = $shifts->sum('total_cash');
        $totalNonCash = $shifts->sum('total_non_cash');
        $totalVoid = $shifts->sum('total_void');
        $totalRefund = $shifts->sum('total_refund');

        return view('cash-closings.create', compact('shifts', 'date', 'totalSales', 'totalCash', 'totalNonCash', 'totalVoid', 'totalRefund'));
    }

    /**
     * Store a newly created closing.
     */
    public function store(Request $request)
    {
        $request->validate([
            'closing_date' => 'required|date',
            'shifts' => 'required|array|min:1',
            'shifts.*.id' => 'required|exists:cash_shifts,id',
            'shifts.*.cash_actual' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $closing = CashClosing::create([
                'location_id' => 1,
                'closing_date' => $request->closing_date,
                'closing_time' => now(),
                'status' => 'closed',
                'notes' => $request->notes ?? null
            ]);

            $totalSales = 0;
            $totalCash = 0;
            $totalNonCash = 0;
            $totalVoid = 0;
            $totalRefund = 0;
            $cashExpected = 0;
            $cashActual = 0;

            foreach ($request->shifts as $shiftData) {
                $shift = CashShift::findOrFail($shiftData['id']);

                // Get summary from shift details
                $sales = $shift->details()->where('transaction_type', 'sale')->sum('amount');
                $cash = $shift->details()->where('transaction_type', 'sale')
                    ->where('payment_type', 'cash')->sum('amount');
                $nonCash = $shift->details()->where('transaction_type', 'sale')
                    ->where('payment_type', '!=', 'cash')->sum('amount');
                $refund = $shift->details()->where('transaction_type', 'refund')->sum('amount');
                $void = $shift->details()->where('transaction_type', 'void')->sum('amount');

                // Create closing detail
                $cashExpected += $shift->closing_balance;
                $cashActual += $shiftData['cash_actual'];

                CashClosingDetail::create([
                    'cash_closing_id' => $closing->id,
                    'cash_shift_id' => $shift->id,
                    'shift_sales' => $sales,
                    'shift_cash' => $cash,
                    'shift_non_cash' => $nonCash,
                    'shift_void' => $void,
                    'shift_refund' => $refund,
                    'shift_cash_expected' => $shift->closing_balance,
                    'shift_cash_actual' => $shiftData['cash_actual'],
                    'shift_cash_difference' => $shift->closing_balance - $shiftData['cash_actual']
                ]);

                $totalSales += $sales;
                $totalCash += $cash;
                $totalNonCash += $nonCash;
                $totalVoid += $void;
                $totalRefund += $refund;
            }

            // Update closing summary
            $closing->update([
                'total_sales' => $totalSales,
                'total_cash' => $totalCash,
                'total_non_cash' => $totalNonCash,
                'total_void' => $totalVoid,
                'total_refund' => $totalRefund,
                'total_due' => $totalNonCash,
                'cash_expected' => $cashExpected,
                'cash_actual' => $cashActual,
                'cash_difference' => $cashExpected - $cashActual
            ]);

            DB::commit();

            return redirect()->route('cash-closings.index')
                ->with('success', 'Tutup kasir berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat tutup kasir: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified closing.
     */
    public function show(CashClosing $closing)
    {
        if ($closing->location_id !== 1) {
            abort(403, 'Unauthorized');
        }

        $closing->load(['details.cashShift.user', 'details.cashShift.details']);

        return view('cash-closings.show', compact('closing'));
    }

    /**
     * Verify the specified closing.
     */
    public function verify(Request $request, CashClosing $closing)
    {
        if ($closing->location_id !== 1) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'approved_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $closing->update([
                'status' => 'verified',
                'closed_at' => now(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'notes' => $request->approved_notes ?? $closing->notes
            ]);

            DB::commit();

            return redirect()->route('cash-closings.show', $closing->id)
                ->with('success', 'Tutup kasir berhasil diverifikasi');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memverifikasi tutup kasir: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified closing.
     */
    public function destroy(CashClosing $closing)
    {
        if ($closing->location_id !== 1) {
            abort(403, 'Unauthorized');
        }

        if ($closing->status === 'verified') {
            return redirect()->back()->with('error', 'Tutup kasir yang sudah diverifikasi tidak bisa dihapus');
        }

        $closing->delete();

        return redirect()->route('cash-closings.index')
            ->with('success', 'Tutup kasir berhasil dihapus');
    }
}
