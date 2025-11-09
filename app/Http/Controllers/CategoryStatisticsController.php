<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoryStatisticsController extends Controller
{
    public function index(Request $request)
    {
        $timePeriod = $request->query('time_period', 'week');
        
        // Xác định khoảng thời gian dựa trên bộ lọc
        $dateRange = $this->getDateRange($timePeriod);
        
        // Lấy dữ liệu tổng quan
        $overviewStats = $this->getOverviewStatistics($dateRange);
        
        // Lấy dữ liệu doanh thu theo danh mục
        $categoryStats = $this->getCategoryStatistics($dateRange);

        return view('admin.categories.category_statistics', [
            'overviewStats' => $overviewStats,
            'categoryStats' => $categoryStats,
            'timePeriod' => $timePeriod,
            'dateRange' => $dateRange,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $timePeriod = $request->query('time_period', 'week');
        $dateRange = $this->getDateRange($timePeriod);
        $categoryStats = $this->getCategoryStatistics($dateRange);

        // TODO: Triển khai xuất Excel
        // Sử dụng Maatwebsite/Laravel-Excel hoặc thư viện xuất Excel khác
        
        return response()->json([
            'message' => 'Chức năng xuất Excel sẽ được triển khai',
            'data' => $categoryStats
        ]);
    }

    private function getDateRange($timePeriod)
    {
        $now = Carbon::now();
        
        switch ($timePeriod) {
            case 'today':
                return [
                    'start' => $now->startOfDay(),
                    'end' => $now->endOfDay(),
                    'label' => 'Hôm nay'
                ];
                
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'label' => 'Tuần này'
                ];
                
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'label' => 'Tháng này'
                ];
                
            case 'quarter':
                return [
                    'start' => $now->copy()->startOfQuarter(),
                    'end' => $now->copy()->endOfQuarter(),
                    'label' => 'Quý này'
                ];
                
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                    'label' => 'Năm nay'
                ];
                
            default:
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'label' => 'Tuần này'
                ];
        }
    }

    private function getOverviewStatistics($dateRange)
    {
        // Tổng doanh thu: Tính từ các đơn hàng đã thanh toán thành công (status = 'completed')
        // Khi thanh toán thành công, PaymentController tự động cập nhật Order status thành 'completed'
        $totalRevenue = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'completed') // Chỉ tính đơn hàng đã hoàn thành
            ->sum('total_price');

        // Tổng sản phẩm đã bán: Đếm số lượng sản phẩm trong các đơn hàng đã hoàn thành
        $totalProductsSold = OrderItem::whereHas('order', function($query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                      ->where('status', 'completed'); // Chỉ tính đơn hàng đã thanh toán
            })
            ->sum('quantity');

        // Danh mục có doanh thu cao nhất
        $topCategory = Category::select('categories.id', 'categories.name')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.status', 'completed')
            ->where('categories.status', 1) // Chỉ danh mục active
            ->selectRaw('SUM(order_items.quantity * products.price) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->first();

        // Tính tăng trưởng so với kỳ trước
        $previousPeriod = $this->getPreviousPeriod($dateRange);
        $currentRevenue = $totalRevenue;
        $previousRevenue = Order::whereBetween('created_at', [$previousPeriod['start'], $previousPeriod['end']])
            ->where('status', 'completed')
            ->sum('total_price');

        $growthRate = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_products_sold' => $totalProductsSold,
            'top_category' => $topCategory,
            'growth_rate' => $growthRate,
            'current_revenue' => $currentRevenue,
            'previous_revenue' => $previousRevenue,
        ];
    }

    private function getCategoryStatistics($dateRange)
    {
        /**
         * Tính toán thống kê doanh thu theo danh mục
         * 
         * Cách hoạt động:
         * 1. Khi khách hàng thanh toán thành công (tiền mặt hoặc MoMo)
         *    -> PaymentController tự động cập nhật Order.status = 'completed'
         * 
         * 2. Query này sẽ tính:
         *    - Số sản phẩm đã bán (products_sold): Tổng quantity từ OrderItem
         *    - Doanh thu (revenue): Tổng (quantity * giá sản phẩm)
         *    Đều dựa trên các Order có status = 'completed'
         * 
         * 3. Kết quả được sử dụng để:
         *    - Hiển thị biểu đồ phân bố doanh thu theo danh mục
         *    - Hiển thị bảng thống kê chi tiết
         */
        return Category::select('categories.id', 'categories.name', 'categories.description')
            ->withCount(['products as total_products'])
            ->addSelect([
                // Tính số sản phẩm đã bán của danh mục
                'products_sold' => OrderItem::selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->whereColumn('products.category_id', 'categories.id')
                    ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('orders.status', 'completed'), // Chỉ tính đơn hàng đã thanh toán
                    
                // Tính doanh thu của danh mục (số lượng * giá)
                // Doanh thu này sẽ tự động cộng dồn khi có thanh toán thành công
                'revenue' => OrderItem::selectRaw('COALESCE(SUM(order_items.quantity * products.price), 0)')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->whereColumn('products.category_id', 'categories.id')
                    ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('orders.status', 'completed') // Chỉ tính đơn hàng đã thanh toán
            ])
            ->where('categories.status', 1) // Chỉ danh mục active
            ->orderByDesc('revenue')
            ->get()
            ->map(function($category) {
                $category->revenue = (float) $category->revenue;
                $category->products_sold = (int) $category->products_sold;
                return $category;
            });
    }

    private function getRevenueTrendData($dateRange, $timePeriod)
    {
        switch ($timePeriod) {
            case 'month':
                $rows = Order::select(
                        DB::raw('DATE(created_at) as day'),
                        DB::raw('SUM(total_price) as revenue')
                    )
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'completed')
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->orderBy('day')
                    ->get()
                    ->mapWithKeys(fn($row) => [$row->day => (float) $row->revenue]);

                $cursor = $dateRange['start']->copy();
                $labels = [];
                $data = [];

                while ($cursor->lessThanOrEqualTo($dateRange['end'])) {
                    $key = $cursor->toDateString();
                    $labels[] = $cursor->format('d/m');
                    $data[] = $rows[$key] ?? 0;
                    $cursor->addDay();
                }

                return [
                    'labels' => $labels,
                    'data' => $data,
                ];

            case 'year':
                $rows = Order::select(
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                        DB::raw('SUM(total_price) as revenue')
                    )
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'completed')
                    ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                    ->orderBy('month')
                    ->get()
                    ->mapWithKeys(fn($row) => [$row->month => (float) $row->revenue]);

                $cursor = $dateRange['start']->copy()->startOfMonth();
                $end = $dateRange['end']->copy()->startOfMonth();
                $labels = [];
                $data = [];

                while ($cursor->lessThanOrEqualTo($end)) {
                    $key = $cursor->format('Y-m');
                    $labels[] = $cursor->format('m/Y');
                    $data[] = $rows[$key] ?? 0;
                    $cursor->addMonth();
                }

                return [
                    'labels' => $labels,
                    'data' => $data,
                ];

            default:
                $revenueByDay = Order::select(
                        DB::raw('DAYNAME(created_at) as day_name'),
                        DB::raw('DAYOFWEEK(created_at) as day_of_week'),
                        DB::raw('SUM(total_price) as revenue')
                    )
                    ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', 'completed')
                    ->groupBy('day_name', 'day_of_week')
                    ->orderBy('day_of_week')
                    ->get();

                $daysOfWeek = [
                    'Monday' => 0,
                    'Tuesday' => 0,
                    'Wednesday' => 0,
                    'Thursday' => 0,
                    'Friday' => 0,
                    'Saturday' => 0,
                    'Sunday' => 0,
                ];

                foreach ($revenueByDay as $revenue) {
                    $daysOfWeek[$revenue->day_name] = (float) $revenue->revenue;
                }

                return [
                    'labels' => array_keys($daysOfWeek),
                    'data' => array_values($daysOfWeek),
                ];
        }
    }

    private function getCategoryDistribution($dateRange, $metric = 'revenue')
    {
        $stats = $this->getCategoryStatistics($dateRange);

        if ($metric === 'products') {
            $totalProducts = $stats->sum('products_sold');

            return $stats->map(function($category) use ($totalProducts) {
                $value = (float) $category->products_sold;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'value' => $value,
                    'percentage' => $totalProducts > 0 ? ($value / $totalProducts) * 100 : 0,
                ];
            });
        }

        $totalRevenue = $stats->sum('revenue');

        return $stats->map(function($category) use ($totalRevenue) {
            $value = (float) $category->revenue;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'value' => $value,
                'percentage' => $totalRevenue > 0 ? ($value / $totalRevenue) * 100 : 0,
            ];
        });
    }

    private function getPreviousPeriod($currentPeriod)
    {
        $start = $currentPeriod['start']->copy();
        $end = $currentPeriod['end']->copy();
        
        $diff = $start->diffInDays($end);
        
        return [
            'start' => $start->subDays($diff + 1),
            'end' => $end->subDays($diff + 1),
        ];
    }

    public function getChartData(Request $request)
    {
        $chartType = $request->query('chart_type', 'revenue');
        $timePeriod = $request->query('time_period', 'week');

        $dateRange = $this->getDateRange($timePeriod);

        if ($chartType === 'distribution') {
            $metric = $request->query('metric', 'revenue');
            $data = $this->getCategoryDistribution($dateRange, $metric);
        } else {
            $data = $this->getRevenueTrendData($dateRange, $timePeriod);
        }

        return response()->json($data);
    }
}