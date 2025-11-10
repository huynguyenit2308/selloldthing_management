<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo thống kê danh mục</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Header section */
        .report-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #2563eb;
        }

        .report-header h1 {
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .report-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .report-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .report-info-item i {
            color: #2563eb;
        }

        /* Overview stats */
        .overview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-radius: 12px;
            padding: 24px;
            border-left: 4px solid #2563eb;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .stat-card h3 {
            font-size: 13px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .stat-card .change {
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .stat-card .change.positive {
            color: #10b981;
        }

        .stat-card .change.negative {
            color: #ef4444;
        }

        /* Table section */
        .table-section {
            margin-top: 40px;
        }

        .table-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-section h2 i {
            color: #2563eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        thead {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
        }

        thead th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th:not(:first-child) {
            text-align: center;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody td {
            padding: 16px;
            font-size: 14px;
            color: #374151;
        }

        tbody td:not(:first-child) {
            text-align: center;
            font-weight: 600;
        }

        .category-name {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            color: #1f2937;
        }

        .category-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 18px;
        }

        .progress-bar {
            width: 100px;
            height: 8px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
            margin: 0 auto;
        }

        .progress {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            border-radius: inherit;
        }

        /* Print buttons */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 1000;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
            text-decoration: none;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        /* Footer */
        .report-footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }

        /* Print styles */
        @media print {
            body {
                background: #ffffff;
            }

            .print-actions {
                display: none !important;
            }

            .container {
                padding: 20px;
            }

            .stat-card {
                break-inside: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tbody tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }

        @page {
            size: A4;
            margin: 15mm;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #374151;
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions">
        <button class="btn btn-primary" onclick="handlePrint()">
            <i class="fas fa-print"></i> In báo cáo
        </button>
        <a href="{{ route('admin.statistics.categories', ['time_period' => $timePeriod]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="report-header">
            <h1>Báo cáo thống kê danh mục</h1>
            <div class="report-info">
                <div class="report-info-item">
                    <i class="fas fa-calendar"></i>
                    <span><strong>Khoảng thời gian:</strong> {{ $dateRange['label'] }}</span>
                </div>
                <div class="report-info-item">
                    <i class="fas fa-clock"></i>
                    <span><strong>Ngày xuất:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- Overview Statistics -->
        <div class="overview-stats">
            <div class="stat-card">
                <h3>Tổng doanh thu</h3>
                <div class="value">{{ number_format($overviewStats['total_revenue'], 0, ',', '.') }} ₫</div>
                <div class="change {{ $overviewStats['growth_rate'] >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-arrow-{{ $overviewStats['growth_rate'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ number_format(abs($overviewStats['growth_rate']), 1) }}% so với kỳ trước
                </div>
            </div>

            <div class="stat-card">
                <h3>Sản phẩm đã bán</h3>
                <div class="value">{{ number_format($overviewStats['total_products_sold']) }}</div>
                <div class="change positive">
                    <i class="fas fa-shopping-cart"></i>
                    Tổng số sản phẩm
                </div>
            </div>

            <div class="stat-card">
                <h3>Danh mục có doanh thu cao nhất</h3>
                <div class="value" style="font-size: 20px;">{{ $overviewStats['top_category']->name ?? 'N/A' }}</div>
                <div class="change">
                    @if($overviewStats['top_category'])
                        {{ number_format($overviewStats['top_category']->revenue ?? 0, 0, ',', '.') }} ₫
                    @else
                        Không có dữ liệu
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <h3>Tăng trưởng doanh thu</h3>
                <div class="value">{{ number_format($overviewStats['growth_rate'], 1) }}%</div>
                <div class="change {{ $overviewStats['growth_rate'] >= 0 ? 'positive' : 'negative' }}">
                    <i class="fas fa-chart-line"></i>
                    So với kỳ trước
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <h2><i class="fas fa-table"></i> Thống kê chi tiết theo danh mục</h2>

            @if($categoryStats->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Không có dữ liệu</h3>
                    <p>Không có dữ liệu thống kê cho khoảng thời gian này</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Danh mục</th>
                            <th>Số sản phẩm</th>
                            <th>Sản phẩm đã bán</th>
                            <th>Doanh thu</th>
                            <th>Tỷ lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalRevenue = $categoryStats->sum('revenue');
                            $colors = ['#3498db', '#e74c3c', '#27ae60', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#d35400'];
                        @endphp

                        @foreach($categoryStats as $index => $category)
                            @php
                                $percentage = $totalRevenue > 0 ? ($category->revenue / $totalRevenue) * 100 : 0;
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <tr>
                                <td>
                                    <div class="category-name">
                                        <div class="category-icon" style="background-color: {{ $color }};">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        {{ $category->name }}
                                    </div>
                                </td>
                                <td>{{ $category->total_products }}</td>
                                <td>{{ $category->products_sold }}</td>
                                <td>{{ number_format($category->revenue, 0, ',', '.') }} ₫</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: {{ $percentage }}%; background-color: {{ $color }};"></div>
                                    </div>
                                    <div style="margin-top: 4px; font-size: 13px;">{{ number_format($percentage, 1) }}%</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Footer -->
        <div class="report-footer">
            <p>Báo cáo được tạo tự động bởi hệ thống quản lý</p>
            <p>© {{ now()->year }} Cửa hàng Đồ cũ</p>
        </div>
    </div>

    <script>
        // Xử lý in báo cáo với error handling
        function handlePrint() {
            try {
                // Kiểm tra trình duyệt có hỗ trợ in không
                if (!window.print) {
                    showError('Trình duyệt không hỗ trợ in. Vui lòng sử dụng trình duyệt khác hoặc lưu dưới dạng PDF.');
                    return;
                }

                // Thử in
                window.print();

                // Lắng nghe sự kiện sau khi in
                window.addEventListener('afterprint', function() {
                    console.log('Print completed or cancelled');
                });

            } catch (error) {
                console.error('Print error:', error);
                showError('Đã có lỗi xảy ra khi in. Vui lòng thử lại hoặc lưu dưới dạng PDF.');
            }
        }

        // Hiển thị thông báo lỗi
        function showError(message) {
            alert(message);
        }

        // Kiểm tra popup blocker
        window.addEventListener('beforeprint', function() {
            console.log('Print dialog opening...');
        });

        // Xử lý lỗi khi popup bị chặn
        window.addEventListener('error', function(e) {
            if (e.message.includes('popup')) {
                showError('Popup bị chặn. Vui lòng cho phép popup trong cài đặt trình duyệt và thử lại.');
            }
        });
    </script>
</body>
</html>
