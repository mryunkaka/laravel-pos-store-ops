<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Key Metrics
        $total_paid = Order::where('order_status', 'complete')->sum('pay_amount');
        $total_due = Order::where('order_status', 'complete')->sum('due_amount');
        $complete_orders = Order::where('order_status', 'complete')->count();
        $pending_orders = Order::where('order_status', 'pending')->count();

        // 2. Today's Snapshot (only complete orders)
        $today_sales = Order::where('order_status', 'complete')
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->sum('total');

        // 3. Top 5 Best Selling Products (only from complete orders)
        $top_products = \Illuminate\Support\Facades\DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('product_references', 'order_details.product_id', '=', 'product_references.id')
            ->where('orders.order_status', 'complete')
            ->select(
                'product_references.name as product_name',
                'product_references.image as product_image',
                'product_references.code as product_code',
                \Illuminate\Support\Facades\DB::raw('SUM(order_details.quantity) as total_sold')
            )
            ->groupBy('product_references.id', 'product_references.name', 'product_references.image', 'product_references.code')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // 4. Monthly Sales Data for Chart (only complete orders, Current Year)
        $monthly_sales = Order::select(
            \Illuminate\Support\Facades\DB::raw('SUM(total) as total_amount'),
            \Illuminate\Support\Facades\DB::raw('MONTH(created_at) as month')
        )
            ->where('order_status', 'complete')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total_amount', 'month')
            ->toArray();

        // Fill missing months with 0
        $chart_data = [];
        for ($i = 1; $i <= 12; $i++) {
            $chart_data[] = $monthly_sales[$i] ?? 0;
        }

        // 5. Recent Transactions
        $recent_orders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', [
            'total_paid' => $total_paid,
            'total_due' => $total_due,
            'complete_orders' => $complete_orders,
            'pending_orders' => $pending_orders,
            'today_sales' => $today_sales,
            'top_products' => $top_products,
            'chart_data' => json_encode($chart_data),
            'recent_orders' => $recent_orders,
        ]);
    }
}
