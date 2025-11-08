@extends('dashboard')

@push('styles')
<style>
    .category-statistics-page {
    background: linear-gradient(135deg, #f5f7fb 0%, #eef2f8 100%);
    padding: 32px 0 48px;
    min-height: calc(100vh - 120px);
}

.category-statistics-page .statistics-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.main-content {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

.sidebar {
    width: 260px;
    background: #ffffff;
    border-radius: 20px;
    padding: 28px 24px;
    box-shadow: 0 18px 40px rgba(18, 38, 63, 0.08);
    position: sticky;
    top: 110px;
    height: fit-content;
}

.sidebar h3 {
    font-size: 14px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar .sidebar-heading {
    margin-top: 28px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0 0 28px 0;
    display: grid;
    gap: 10px;
}

.sidebar ul li a {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    color: #1f2937;
    padding: 12px 16px;
    border-radius: 14px;
    background: #f9fafb;
    transition: all 0.25s ease;
    text-decoration: none;
}

.sidebar ul li a i {
    color: #6366f1;
}

.sidebar ul li a:hover,
.sidebar ul li a.active {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff;
    box-shadow: 0 12px 25px rgba(79, 70, 229, 0.25);
}

.sidebar ul li a:hover i,
.sidebar ul li a.active i {
    color: #ffffff;
}

.content {
    flex: 1;
    background: #ffffff;
    border-radius: 24px;
    padding: 32px 36px;
    box-shadow: 0 24px 60px rgba(16, 24, 40, 0.08);
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    margin-bottom: 28px;
}

.content-header h2 {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 12px;
}

.date-filter {
    display: flex;
    gap: 12px;
    align-items: center;
}

.date-filter select {
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 10px 14px;
    font-weight: 500;
    color: #1f2937;
    min-width: 180px;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    background-color: #fff;
    cursor: pointer;
}

.date-filter button {
    border-radius: 12px;
    border: none;
    padding: 10px 18px;
    font-weight: 600;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #ffffff;
    box-shadow: 0 12px 25px rgba(99, 102, 241, 0.25);
    transition: transform 0.2s ease;
    cursor: pointer;
}

.date-filter button:hover {
    transform: translateY(-2px);
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: #ffffff;
    border-radius: 22px;
    padding: 24px;
    display: flex;
    gap: 18px;
    align-items: center;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.08);
}

.stat-icon {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ffffff;
    flex-shrink: 0;
}

.stat-info h3 {
    font-size: 15px;
    text-transform: uppercase;
    color: #6b7280;
    margin-bottom: 6px;
    letter-spacing: 0.06em;
}

.stat-value {
    font-size: 26px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
}

.stat-change {
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.stat-change.positive {
    color: #10b981;
}

.stat-change.negative {
    color: #ef4444;
}

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.chart-container {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(168, 85, 247, 0.08));
    border-radius: 24px;
    padding: 24px 26px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.1);
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-height: 360px;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-header h3 {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.chart-actions {
    display: inline-flex;
    gap: 8px;
    background: rgba(255, 255, 255, 0.8);
    padding: 6px;
    border-radius: 14px;
}

.chart-actions button {
    border: none;
    padding: 8px 14px;
    border-radius: 10px;
    font-weight: 600;
    color: #4b5563;
    background: transparent;
    transition: all 0.2s ease;
    cursor: pointer;
}

.chart-actions button.active,
.chart-actions button:hover {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff;
    box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25);
}

.chart {
    position: relative;
    flex: 1;
    min-height: 260px;
}

.categories-table-container {
    background: #ffffff;
    border-radius: 24px;
    padding: 28px 30px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(229, 231, 235, 0.7);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 16px;
}

.table-header h3 {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
}

.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    border-radius: 12px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #0ea5e9, #6366f1);
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 12px 28px rgba(14, 165, 233, 0.25);
    transition: transform 0.2s ease;
    cursor: pointer;
}

.export-btn:hover {
    transform: translateY(-2px);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px;
}

thead th {
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.08em;
    color: #6b7280;
    padding-bottom: 14px;
    text-align: left;
}

tbody tr {
    background: #f9fafb;
    border-radius: 16px;
}

tbody td {
    padding: 18px 16px;
    vertical-align: middle;
    font-weight: 600;
    color: #1f2937;
}

tbody tr td:first-child {
    border-top-left-radius: 16px;
    border-bottom-left-radius: 16px;
}

tbody tr td:last-child {
    border-top-right-radius: 16px;
    border-bottom-right-radius: 16px;
}

.category-name {
    display: flex;
    align-items: center;
    gap: 16px;
    font-weight: 700;
}

.category-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ffffff;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.15);
}

.progress-bar {
    width: 100%;
    height: 10px;
    background: rgba(226, 232, 240, 0.9);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 6px;
}

.progress {
    height: 100%;
    border-radius: inherit;
    transition: width 0.35s ease;
}

tbody small {
    font-weight: 600;
    color: #6b7280;
}

.text-center {
    text-align: center;
    color: #6b7280;
    font-weight: 500;
}

@media (max-width: 1100px) {
    .main-content {
        flex-direction: column;
    }

    .sidebar {
        position: relative;
        top: 0;
        width: 100%;
    }
}

@media (max-width: 768px) {
    .category-statistics-page {
        padding: 24px 0;
    }
    
    .statistics-container {
        padding: 0 16px;
    }
    
    .content {
        padding: 20px;
    }

    .content-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .date-filter {
        width: 100%;
        justify-content: space-between;
    }

    .date-filter select {
        flex: 1;
    }

    .stats-cards {
        grid-template-columns: 1fr;
    }

    .charts-section {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 14px;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .export-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .sidebar {
        padding: 20px;
    }
    
    .content {
        padding: 16px;
    }
    
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .chart-container {
        padding: 16px;
    }
    
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .chart-actions {
        width: 100%;
        justify-content: center;
    }
}
        background: linear-gradient(135deg, #f5f7fb 0%, #eef2f8 100%);
        padding: 32px 0 48px;
        min-height: calc(100vh - 120px);
    }

    .category-statistics-page .statistics-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
    }

    .main-content {
        display: flex;
        gap: 24px;
        align-items: flex-start;
    }

    .sidebar {
        width: 260px;
        background: #ffffff;
        border-radius: 20px;
        padding: 28px 24px;
        box-shadow: 0 18px 40px rgba(18, 38, 63, 0.08);
        position: sticky;
        top: 110px;
        height: fit-content;
    }

    .sidebar h3 {
        font-size: 14px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar .sidebar-heading {
        margin-top: 28px;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0 0 28px 0;
        display: grid;
        gap: 10px;
    }

    .sidebar ul li a {
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 500;
        color: #1f2937;
        padding: 12px 16px;
        border-radius: 14px;
        background: #f9fafb;
        transition: all 0.25s ease;
    }

    .sidebar ul li a i {
        color: #6366f1;
    }

    .sidebar ul li a:hover,
    .sidebar ul li a.active {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #ffffff;
        box-shadow: 0 12px 25px rgba(79, 70, 229, 0.25);
    }

    .sidebar ul li a:hover i,
    .sidebar ul li a.active i {
        color: #ffffff;
    }

    .content {
        flex: 1;
        background: #ffffff;
        border-radius: 24px;
        padding: 32px 36px;
        box-shadow: 0 24px 60px rgba(16, 24, 40, 0.08);
    }

    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        margin-bottom: 28px;
    }

    .content-header h2 {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .date-filter {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .date-filter select {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 10px 14px;
        font-weight: 500;
        color: #1f2937;
        min-width: 180px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    }

    .date-filter button {
        border-radius: 12px;
        border: none;
        padding: 10px 18px;
        font-weight: 600;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff;
        box-shadow: 0 12px 25px rgba(99, 102, 241, 0.25);
        transition: transform 0.2s ease;
    }

    .date-filter button:hover {
        transform: translateY(-2px);
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #ffffff;
        border-radius: 22px;
        padding: 24px;
        display: flex;
        gap: 18px;
        align-items: center;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35), 0 18px 35px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.08);
    }

    .stat-icon {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        color: #ffffff;
        flex-shrink: 0;
    }

    .stat-info h3 {
        font-size: 15px;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 6px;
        letter-spacing: 0.06em;
    }

    .stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .stat-change {
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
    }

    .stat-change.positive {
        color: #10b981;
    }

    .stat-change.negative {
        color: #ef4444;
    }

    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .chart-container {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(168, 85, 247, 0.08));
        border-radius: 24px;
        padding: 24px 26px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.1);
        display: flex;
        flex-direction: column;
        gap: 18px;
        min-height: 360px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chart-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .chart-actions {
        display: inline-flex;
        gap: 8px;
        background: rgba(255, 255, 255, 0.8);
        padding: 6px;
        border-radius: 14px;
    }

    .chart-actions button {
        border: none;
        padding: 8px 14px;
        border-radius: 10px;
        font-weight: 600;
        color: #4b5563;
        background: transparent;
        transition: all 0.2s ease;
    }

    .chart-actions button.active,
    .chart-actions button:hover {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #ffffff;
        box-shadow: 0 10px 25px rgba(79, 70, 229, 0.25);
    }

    .chart {
        position: relative;
        flex: 1;
        min-height: 260px;
    }

    .categories-table-container {
        background: #ffffff;
        border-radius: 24px;
        padding: 28px 30px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(229, 231, 235, 0.7);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 16px;
    }

    .table-header h3 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }

    .export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        border-radius: 12px;
        padding: 10px 18px;
        background: linear-gradient(135deg, #0ea5e9, #6366f1);
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 12px 28px rgba(14, 165, 233, 0.25);
        transition: transform 0.2s ease;
    }

    .export-btn:hover {
        transform: translateY(-2px);
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 12px;
    }

    thead th {
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.08em;
        color: #6b7280;
        padding-bottom: 14px;
    }

    tbody tr {
        background: #f9fafb;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        border-radius: 16px;
    }

    tbody td {
        padding: 18px 16px;
        vertical-align: middle;
        font-weight: 600;
        color: #1f2937;
    }

    tbody tr td:first-child {
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
    }

    tbody tr td:last-child {
        border-top-right-radius: 16px;
        border-bottom-right-radius: 16px;
    }

    .category-name {
        display: flex;
        align-items: center;
        gap: 16px;
        font-weight: 700;
    }

    .category-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.15);
    }

    .progress-bar {
        width: 100%;
        height: 10px;
        background: rgba(226, 232, 240, 0.9);
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .progress {
        height: 100%;
        border-radius: inherit;
        transition: width 0.35s ease;
    }

    tbody small {
        font-weight: 600;
        color: #6b7280;
    }

    .text-center {
        text-align: center;
        color: #6b7280;
        font-weight: 500;
    }

    @media (max-width: 1100px) {
        .main-content {
            flex-direction: column;
        }

        .sidebar {
            position: relative;
            top: 0;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 24px;
        }

        .content-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .date-filter {
            width: 100%;
            justify-content: space-between;
        }

        .date-filter select {
            flex: 1;
        }

        .stats-cards {
            grid-template-columns: 1fr;
        }

        .charts-section {
            grid-template-columns: 1fr;
        }

        table {
            font-size: 14px;
        }
    }
>>>>>>> 3514ca6 (up demo category_statistics)
</style>
@endpush

@section('content')
<div class="category-statistics-page">
    <div class="statistics-container">
        <div class="main-content">
            <!-- Sidebar -->
            <div class="sidebar">
                <h3><i class="fas fa-chart-bar"></i> Báo cáo & Thống kê</h3>
                <ul>
                    <li><a href="{{ route('admin.statistics.categories') }}" class="active"><i class="fas fa-chart-pie"></i> Tổng quan</a></li>
                    <li><a href="#"><i class="fas fa-shopping-cart"></i> Doanh thu</a></li>
                    <li><a href="#"><i class="fas fa-box"></i> Sản phẩm</a></li>
                    <li><a href="#"><i class="fas fa-users"></i> Khách hàng</a></li>
                    <li><a href="{{ route('admin.statistics.categories.export') }}" class="export-link"><i class="fas fa-file-export"></i> Xuất báo cáo</a></li>
                </ul>

                <h3 class="sidebar-heading"><i class="fas fa-filter"></i> Bộ lọc thời gian</h3>
                <ul>
                    <li><a href="{{ route('admin.statistics.categories', ['time_period' => 'today']) }}" class="{{ $timePeriod == 'today' ? 'active' : '' }}"><i class="fas fa-calendar-day"></i> Hôm nay</a></li>
                    <li><a href="{{ route('admin.statistics.categories', ['time_period' => 'week']) }}" class="{{ $timePeriod == 'week' ? 'active' : '' }}"><i class="fas fa-calendar-week"></i> Tuần này</a></li>
                    <li><a href="{{ route('admin.statistics.categories', ['time_period' => 'month']) }}" class="{{ $timePeriod == 'month' ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> Tháng này</a></li>
                    <li><a href="{{ route('admin.statistics.categories', ['time_period' => 'quarter']) }}" class="{{ $timePeriod == 'quarter' ? 'active' : '' }}"><i class="fas fa-calendar"></i> Quý này</a></li>
                    <li><a href="{{ route('admin.statistics.categories', ['time_period' => 'year']) }}" class="{{ $timePeriod == 'year' ? 'active' : '' }}"><i class="fas fa-star"></i> Năm nay</a></li>
                </ul>
            </div>

        <!-- Content -->
        <div class="content">
            <div class="content-header">
                <h2><i class="fas fa-chart-pie"></i> Thống kê Danh mục - {{ $dateRange['label'] }}</h2>
                <div class="date-filter">
                    <select id="time-period">
                        <option value="today" {{ $timePeriod == 'today' ? 'selected' : '' }}>Hôm nay</option>
                        <option value="week" {{ $timePeriod == 'week' ? 'selected' : '' }}>Tuần này</option>
                        <option value="month" {{ $timePeriod == 'month' ? 'selected' : '' }}>Tháng này</option>
                        <option value="quarter" {{ $timePeriod == 'quarter' ? 'selected' : '' }}>Quý này</option>
                        <option value="year" {{ $timePeriod == 'year' ? 'selected' : '' }}>Năm nay</option>
                    </select>
                    <button id="apply-filter"><i class="fas fa-filter"></i> Áp dụng</button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2c3e50);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tổng doanh thu</h3>
                        <div class="stat-value">{{ number_format($overviewStats['total_revenue'], 0, ',', '.') }} ₫</div>
                        <div class="stat-change {{ $overviewStats['growth_rate'] >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ $overviewStats['growth_rate'] >= 0 ? 'up' : 'down' }}"></i> 
                            {{ number_format(abs($overviewStats['growth_rate']), 1) }}% so với kỳ trước
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Sản phẩm đã bán</h3>
                        <div class="stat-value">{{ number_format($overviewStats['total_products_sold']) }}</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i> 
                            {{-- Có thể thêm tính toán tăng trưởng sản phẩm ở đây --}}
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #27ae60, #219653);">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Danh mục có doanh thu cao nhất</h3>
                        <div class="stat-value">{{ $overviewStats['top_category']->name ?? 'N/A' }}</div>
                        <div class="stat-change">
                            @if($overviewStats['top_category'])
                                {{ number_format($overviewStats['top_category']->revenue ?? 0, 0, ',', '.') }} ₫
                            @else
                                Không có dữ liệu
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tăng trưởng doanh thu</h3>
                        <div class="stat-value">{{ number_format($overviewStats['growth_rate'], 1) }}%</div>
                        <div class="stat-change {{ $overviewStats['growth_rate'] >= 0 ? 'positive' : 'negative' }}">
                            <i class="fas fa-arrow-{{ $overviewStats['growth_rate'] >= 0 ? 'up' : 'down' }}"></i> 
                            So với kỳ trước
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Doanh thu theo ngày trong tuần</h3>
                        <div class="chart-actions">
                            <button class="active" data-period="week">Tuần</button>
                            <button data-period="month">Tháng</button>
                            <button data-period="year">Năm</button>
                        </div>
                    </div>
                    <div class="chart">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Phân bố danh mục theo doanh thu</h3>
                        <div class="chart-actions" data-chart="distribution">
                            <button class="active" data-type="revenue">Doanh thu</button>
                            <button data-type="products">Sản phẩm</button>
                        </div>
                    </div>
                    <div class="chart">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="categories-table-container">
                <div class="table-header">
                    <h3>Thống kê chi tiết theo danh mục</h3>
                    <button class="export-btn" onclick="exportToExcel()"><i class="fas fa-file-export"></i> Xuất Excel</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Danh mục</th>
                            <th>Số sản phẩm</th>
                            <th>Số sản phẩm đã bán</th>
                            <th>Doanh thu</th>
                            <th>Tỷ lệ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalRevenue = $categoryStats->sum('revenue');
                            $colors = ['#3498db', '#e74c3c', '#27ae60', '#f39c12', '#9b59b6', '#1abc9c', '#34495e', '#d35400'];
                        @endphp
                        
                        @forelse($categoryStats as $index => $category)
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
                                    <small>{{ number_format($percentage, 1) }}%</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có dữ liệu thống kê</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timePeriodSelect = document.getElementById('time-period');
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const distributionCtx = document.getElementById('distributionChart').getContext('2d');

            const gradientColors = [
                'rgba(52, 152, 219, 0.7)',
                'rgba(231, 76, 60, 0.7)',
                'rgba(39, 174, 96, 0.7)',
                'rgba(243, 156, 18, 0.7)',
                'rgba(155, 89, 182, 0.7)',
                'rgba(26, 188, 156, 0.7)',
                'rgba(99, 102, 241, 0.7)',
                'rgba(16, 185, 129, 0.7)'
            ];

            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Doanh thu (VNĐ)',
                        data: [],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'VNĐ'
                            }
                        }
                    }
                }
            });

            const distributionChart = new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: [],
                    datasets: [{
                        data: [],
                        backgroundColor: gradientColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            async function fetchChartData(params = {}) {
                const query = new URLSearchParams({
                    time_period: timePeriodSelect.value,
                    ...params,
                });

                const response = await fetch(`{{ route('admin.statistics.categories.chart-data') }}?${query.toString()}`);
                if (!response.ok) throw new Error('Không thể tải dữ liệu biểu đồ');
                return response.json();
            }

            async function loadRevenueChart() {
                try {
                    const data = await fetchChartData({ chart_type: 'revenue' });
                    revenueChart.data.labels = data.labels;
                    revenueChart.data.datasets[0].data = data.data;
                    revenueChart.update();
                } catch (error) {
                    console.error(error);
                }
            }

            async function loadDistributionChart(metric = 'revenue') {
                try {
                    const data = await fetchChartData({ chart_type: 'distribution', metric });
                    distributionChart.data.labels = data.map(item => item.name);
                    distributionChart.data.datasets[0].data = data.map(item => item.value);
                    distributionChart.update();
                } catch (error) {
                    console.error(error);
                }
            }

            document.getElementById('apply-filter').addEventListener('click', function() {
                const period = timePeriodSelect.value;
                window.history.replaceState({}, '', `{{ route('admin.statistics.categories') }}?time_period=${period}`);
                loadRevenueChart();
                loadDistributionChart(activeMetric);
            });

            let activeMetric = 'revenue';
            const chartButtonsGroups = document.querySelectorAll('.chart-actions');
            chartButtonsGroups.forEach(group => {
                group.querySelectorAll('button').forEach(button => {
                    button.addEventListener('click', function() {
                        group.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');

                        if (group.dataset.chart === 'distribution') {
                            activeMetric = this.dataset.type;
                            loadDistributionChart(activeMetric);
                        } else {
                            timePeriodSelect.value = this.dataset.period;
                            loadRevenueChart();
                        }
                    });
                });
            });

            function exportToExcel() {
                const timePeriod = timePeriodSelect.value;
                window.location.href = '{{ route("admin.statistics.categories.export") }}?time_period=' + timePeriod;
            }

            loadRevenueChart();
            loadDistributionChart();
        });
    </script>
    @endpush