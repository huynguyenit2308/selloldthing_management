<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Hóa đơn #{{ $payment->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            margin: 20px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header div h2 {
            margin: 0;
        }

        .header div p {
            margin: 2px 0 0 0;
            font-size: 12px;
        }

        /* Section */
        .section {
            margin-bottom: 20px;
        }

        .section strong {
            display: inline-block;
            width: 130px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        table th {
            background-color: #f0f0f0;
            padding: 8px;
            border: 1px solid #000;
        }

        table td {
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
        }

        table td:first-child {
            text-align: left;
        }

        table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        /* Footer tổng */
        .total {
            margin-top: 15px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
    </style>

<body>
    <h1 style="text-align: center">Hóa đơn bán hàng</h1>
    <!-- Thông tin khách hàng -->
    <div class="section">
        <strong>Khách hàng:</strong> {{ optional($payment->user)->name ?? 'Không có' }}<br>
        <strong>Email:</strong> {{ optional($payment->user)->email ?? 'Không có' }}<br>
        <strong>Số điện thoại:</strong> {{ optional($payment->user)->phone ?? 'Không có' }}
    </div>

    <!-- Bảng chi tiết đơn hàng -->
    @if (optional($payment->order))
        <div>
            <strong>Chi tiết đơn hàng:</strong>
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Giá</th>
                        <th>Giảm</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payment->order->items as $item)
                        @php
                            $price = $item->product->price ?? 0;
                            $quantity = $item->quantity ?? 1;
                            $discount = 0;
                            if ($payment->voucher) {
                                if ($payment->voucher->type === 'percent') {
                                    $discount = $price * $quantity * ($payment->voucher->discount / 100);
                                } else {
                                    $totalItems = $payment->order->items->sum('quantity');
                                    $discount = ($payment->voucher->discount / $totalItems) * $quantity;
                                }
                            }
                            $total = $price * $quantity - $discount;
                        @endphp
                        <tr>
                            <td>{{ optional($item->product)->name ?? 'Không có' }}</td>
                            <td>{{ $quantity }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->payment_status === 'completed' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</td>
                            <td>{{ number_format($price, 0, '', ',') }}₫</td>
                            <td>{{ number_format($discount, 0, '', ',') }}₫</td>
                            <td>{{ number_format($total, 0, '', ',') }}₫</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Tổng tiền -->
    <div class="total">
        Tổng thanh toán: {{ number_format($payment->amount, 0, '', ',') }}₫
    </div>
</body>

</html>
