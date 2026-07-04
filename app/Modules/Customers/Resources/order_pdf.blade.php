<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hóa đơn đơn hàng #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif; /* Font hỗ trợ ký tự Unicode */
            line-height: 1.6;
            font-size: 14px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .order-details, .customer-details {
            margin-bottom: 30px;
        }
        .order-details table, .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HÓA ĐƠN BÁN HÀNG</h1>
            <p>Mã đơn hàng: #{{ $order->id }}</p>
            <p>Ngày đặt: {{ $order->created_at->format('d/m/Y') }}</p>
        </div>

        <div class="customer-details">
            <h3>Thông tin khách hàng</h3>
            <p><strong>Tên:</strong> {{ $order->customer->name }}</p>
            <p><strong>Email:</strong> {{ $order->customer->email }}</p>
        </div>

        <div class="order-details">
            <h3>Chi tiết đơn hàng</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 0, ',', '.') }} VNĐ</td>
                        <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} VNĐ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total">
            Tổng cộng: {{ number_format($order->total_amount, 0, ',', '.') }} VNĐ
        </div>

        <div class="footer">
            <p>Cảm ơn bạn đã mua hàng!</p>
        </div>
    </div>
</body>
</html>
