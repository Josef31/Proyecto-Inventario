<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\CashRegister;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Top Selling Products (by quantity)
        $topSellingProducts = SaleItem::select('sale_items.product_id', 'products.name', DB::raw('SUM(sale_items.quantity) as total_quantity'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // Top Revenue Products
        $topRevenueProducts = SaleItem::select('sale_items.product_id', 'products.name', DB::raw('SUM(sale_items.price * sale_items.quantity) as total_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();

        // Top Customers (by number of purchases)
        $topCustomers = Sale::select('sales.customer_id', 'customers.name as customer_name', DB::raw('COUNT(*) as purchase_count'))
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.status', 'completada')
            ->groupBy('sales.customer_id', 'customers.name')
            ->orderBy('purchase_count', 'desc')
            ->limit(5)
            ->get();
        
        // Calculate total spent for each customer
        foreach ($topCustomers as $customer) {
            $customer->total_spent = Sale::where('customer_id', $customer->customer_id)
                ->where('status', 'completada')
                ->get()
                ->sum('total'); // Using the accessor from Sale model
        }

        // Low Stock Products (stock < 10)
        $lowStockProducts = Product::where('stock_initial', '<', 10)
            ->where('stock_initial', '>', 0)
            ->orderBy('stock_initial', 'asc')
            ->limit(10)
            ->get();

        // Out of Stock Products
        $outOfStockProducts = Product::where('stock_initial', '=', 0)
            ->limit(10)
            ->get();

        // Today's Sales
        $today = Carbon::today();
        $todaySales = Sale::whereDate('created_at', $today)
            ->where('status', 'completada')
            ->get();

        $todayRevenueBs = 0;
        $todayRevenueUsd = 0;
        
        foreach ($todaySales as $sale) {
            $total = $sale->subtotal + $sale->taxes;
            if ($sale->payment_method_id == 1) { // USD
                $todayRevenueUsd += $total;
            } else { // Bs
                $todayRevenueBs += $total;
            }
        }

        // This Week's Sales
        $weekStart = Carbon::now()->startOfWeek();
        $weekSales = Sale::where('created_at', '>=', $weekStart)
            ->where('status', 'completada')
            ->count();

        // This Month's Sales
        $monthStart = Carbon::now()->startOfMonth();
        $monthSales = Sale::where('created_at', '>=', $monthStart)
            ->where('status', 'completada')
            ->count();

        // Total Inventory Value
        $inventoryValue = Product::sum(DB::raw('price_sell * stock_initial'));

        // Cash Register Status
        $openCashRegister = CashRegister::where('status', 'abierta')
            ->orderBy('created_at', 'desc')
            ->first();

        // Recent Sales (last 5)
        $recentSales = Sale::with('customer')
            ->where('status', 'completada')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent Purchases (last 5)
        $recentPurchases = Purchase::with('supplier')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Total Customers
        $totalCustomers = Customer::count();

        // New Customers (this month)
        $newCustomers = Customer::where('created_at', '>=', $monthStart)->count();

        return view('dashboard.index', compact(
            'topSellingProducts',
            'topRevenueProducts',
            'topCustomers',
            'lowStockProducts',
            'outOfStockProducts',
            'todayRevenueBs',
            'todayRevenueUsd',
            'weekSales',
            'monthSales',
            'inventoryValue',
            'openCashRegister',
            'recentSales',
            'recentPurchases',
            'totalCustomers',
            'newCustomers'
        ));
    }
}
