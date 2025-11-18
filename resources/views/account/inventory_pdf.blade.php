<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo tồn kho</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2933;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 0;
        }
        .meta {
            margin-bottom: 16px;
            font-size: 12px;
        }
        .stats {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .stats th,
        .stats td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        .stats th {
            background: #f3f4f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
        }
        th {
            background: #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Báo cáo tồn kho</h1>
        <div class="meta">
            <div>Người dùng: <strong>{{ $user->name }}</strong> (ID: {{ $user->id }})</div>
            <div>Thời gian tạo: {{ $generatedAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
        </div>
    </header>

    <section>
        <table class="stats">
            <thead>
                <tr>
                    <th>Chỉ số</th>
                    <th class="text-right">Giá trị</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tổng sản phẩm</td>
                    <td class="text-right">{{ number_format($inventoryStats['total_products']) }}</td>
                </tr>
                <tr>
                    <td>Sản phẩm có hàng</td>
                    <td class="text-right">{{ number_format($inventoryStats['in_stock']) }}</td>
                </tr>
                <tr>
                    <td>Sản phẩm sắp hết</td>
                    <td class="text-right">{{ number_format($inventoryStats['low_stock']) }}</td>
                </tr>
                <tr>
                    <td>Tổng giá trị tồn kho</td>
                    <td class="text-right">{{ number_format($inventoryStats['inventory_value'], 0, ',', '.') }} VND</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section>
        <table>
            <thead>
                <tr>
                    <th class="text-center">ID</th>
                    <th>Tên sản phẩm</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-right">Giá</th>
                    <th class="text-center">Tồn kho</th>
                    <th class="text-center">Đã bán</th>
                    <th class="text-right">Giá trị tồn</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td class="text-center">{{ $product['id'] }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td class="text-center">{{ $product['inventory_status']['label'] ?? '' }}</td>
                        <td class="text-right">{{ $product['price_formatted'] }}</td>
                        <td class="text-center">{{ $product['quantity'] }}</td>
                        <td class="text-center">{{ $product['sold'] }}</td>
                        <td class="text-right">{{ $product['total_value_formatted'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</body>
</html>
