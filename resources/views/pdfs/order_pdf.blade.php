<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn #{{ $order->id }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .company-info {
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #111827;
            text-transform: uppercase;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .items-table th {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-section {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        .total-section td {
            padding: 5px 0;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
        }
        .badge-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-default {
            background-color: #e5e7eb;
            color: #374151;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">Laravel Store</div>
                    <div style="margin-top: 5px; font-size: 12px; color: #4b5563;">Hóa Đơn Bán Hàng</div>
                </td>
                <td class="company-info">
                    <strong>Công ty TNHH Laravel Việt Nam</strong><br>
                    Địa chỉ: 123 Đường Láng, Đống Đa, Hà Nội<br>
                    Email: support@laravelstore.vn<br>
                    Hotline: 1900 1234
                </td>
            </tr>
        </table>

        <div class="title">Hóa Đơn Mua Hàng</div>

        <!-- Thông tin đơn hàng & Khách hàng -->
        <table class="details-table">
            <tr>
                <td style="width: 50%;">
                    <strong>Thông tin khách hàng:</strong><br>
                    Họ và tên: {{ $order->customer->name }}<br>
                    Email: {{ $order->customer->email }}
                </td>
                <td style="width: 50%;" class="text-right">
                    <strong>Thông tin hóa đơn:</strong><br>
                    Mã đơn hàng: #{{ $order->id }}<br>
                    Ngày đặt: {{ $order->created_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}<br>
                    Trạng thái: 
                    @if($order->status === \App\Enums\OrdersStatusEnum::COMPLETED)
                        <span class="badge badge-completed">Đã hoàn thành</span>
                    @elseif($order->status === \App\Enums\OrdersStatusEnum::PENDING)
                        <span class="badge badge-pending">Chờ xử lý</span>
                    @elseif($order->status === \App\Enums\OrdersStatusEnum::CANCELLED)
                        <span class="badge badge-cancelled">Đã hủy</span>
                    @else
                        <span class="badge badge-default">{{ $order->status->value ?? 'Chưa xác định' }}</span>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Chi tiết sản phẩm -->
        <table class="items-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">STT</th>
                    <th>Tên sản phẩm</th>
                    <th class="text-right" style="width: 15%;">Đơn giá</th>
                    <th class="text-center" style="width: 10%;">SL</th>
                    <th class="text-right" style="width: 20%;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}đ</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tổng cộng -->
        <table class="total-section">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 20%; font-weight: bold;" class="text-right">Tổng cộng:</td>
                <td style="width: 20%; font-weight: bold; color: #4f46e5; font-size: 15px;" class="text-right">
                    {{ number_format($order->total_amount, 0, ',', '.') }} VNĐ
                </td>
            </tr>
        </table>

        <div style="margin-top: 30px; font-size: 12px; font-style: italic;">
            * Lưu ý: Hóa đơn này được tạo tự động bởi hệ thống và có giá trị xác nhận giao dịch trực tuyến.
        </div>

        <!-- Footer -->
        <div class="footer">
            Cảm ơn quý khách đã mua sắm tại Laravel Store!
        </div>
    </div>
</body>
</html>
