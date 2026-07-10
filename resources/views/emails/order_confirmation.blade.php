<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #{{ $order->id }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            background-color: #f6f6f6;
            color: #333333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #00B14F;
            padding: 25px 20px;
            text-align: left;
        }
        .header-logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: -1px;
            margin: 0;
        }
        .content {
            padding: 25px 20px;
        }
        .status-title {
            color: #00B14F;
            font-size: 24px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .summary-box {
            margin-bottom: 25px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 20px;
        }
        .summary-title {
            font-size: 14px;
            color: #888888;
            margin: 0;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 22px;
            color: #00B14F;
            font-weight: bold;
            margin: 5px 0 0 0;
        }
        .grid-table td {
            vertical-align: top;
            padding: 10px;
        }
        .section-title {
            color: #00B14F;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .info-label {
            font-size: 11px;
            color: #888888;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 14px;
            color: #333333;
            margin: 0 0 10px 0;
            font-weight: 500;
        }
        .item-row {
            font-size: 13px;
            border-bottom: 1px dashed #f0f0f0;
            padding: 8px 0;
        }
        .invoice-card {
            background-color: #fafafa;
            border: 1px solid #eeeeee;
            border-radius: 4px;
            padding: 15px;
        }
        .invoice-row {
            font-size: 13px;
            margin-bottom: 8px;
        }
        .invoice-total {
            font-size: 15px;
            font-weight: bold;
            border-top: 2px double #dddddd;
            border-bottom: 2px double #dddddd;
            padding: 10px 0;
            margin-top: 12px;
        }
        .btn-action {
            display: inline-block;
            background-color: #00B14F;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 25px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 25px;
            text-align: center;
        }
        .footer {
            background-color: #f6f6f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999999;
        }
    </style>
</head>
<body>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="center" style="padding: 20px 0; background-color: #f6f6f6;">
                <table class="container" cellspacing="0" cellpadding="0" border="0">
                    <!-- Green Header Bar -->
                    <tr>
                        <td class="header">
                            <h1 class="header-logo">Laravel Store</h1>
                        </td>
                    </tr>

                    <!-- Main Body -->
                    <tr>
                        <td class="content">
                            <h2 class="status-title">Đơn hàng đã thanh toán thành công!</h2>

                            <!-- Summary Top Row -->
                            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="summary-box">
                                <tr>
                                    <td width="50%">
                                        <p class="summary-title">Tổng cộng</p>
                                        <p class="summary-value">{{ number_format($order->total_amount, 0, ',', '.') }} đ</p>
                                    </td>
                                    <td width="50%" align="right" style="vertical-align: bottom;">
                                        <p class="summary-title">Ngày đặt hàng</p>
                                        <p style="font-size: 16px; margin: 5px 0 0 0; font-weight: 500;">
                                            {{ $order->created_at?->format('d M Y') ?? now()->format('d M Y') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Details Table (2 columns layout) -->
                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <!-- Left Column: Order details -->
                                    <td width="55%" style="vertical-align: top; padding-right: 15px;">
                                        <div class="section-title">Chi tiết đơn hàng</div>

                                        <div class="info-label">Khách hàng</div>
                                        <div class="info-value">{{ $order->customer->name }}</div>

                                        <div class="info-label">Email</div>
                                        <div class="info-value">{{ $order->customer->email }}</div>

                                        <div class="info-label">Mã đơn hàng</div>
                                        <div class="info-value" style="font-family: monospace; font-size: 14px; font-weight: bold; color: #555555;">
                                            #{{ $order->id }}
                                        </div>

                                        <div class="info-label" style="margin-top: 15px; margin-bottom: 5px;">Sản phẩm đã mua</div>
                                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                            @foreach($order->items as $item)
                                                <tr>
                                                    <td class="item-row" style="padding: 6px 0;">
                                                        <div style="font-weight: bold; font-size: 13px;">{{ $item->product->name }}</div>
                                                        <div style="color: #666666; font-size: 11px;">
                                                            Đơn giá: {{ number_format($item->price, 0, ',', '.') }} đ &times; {{ $item->quantity }}
                                                        </div>
                                                    </td>
                                                    <td class="item-row" align="right" style="vertical-align: middle; font-weight: 500; font-size: 13px;">
                                                        {{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>

                                    <!-- Right Column: Cost box -->
                                    <td width="45%" style="vertical-align: top; padding-left: 5px;">
                                        <div class="section-title">Hóa đơn của bạn</div>

                                        <div class="invoice-card">
                                            <table width="      100%" cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td class="info-label" style="margin-top: 0;">Phương thức thanh toán</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-size: 14px; font-weight: bold; padding-bottom: 12px; border-bottom: 1px dashed #dddddd;">
                                                        Thanh toán trực tuyến
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding-top: 12px;">
                                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="invoice-row">
                                                            <tr>
                                                                <td style="color: #666666;">Tạm tính</td>
                                                                <td align="right">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                                            </tr>
                                                        </table>
                                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="invoice-row">
                                                            <tr>
                                                                <td style="color: #666666;">Khuyến mại</td>
                                                                <td align="right" style="color: #e11d48;">- 0 đ</td>
                                                            </tr>
                                                        </table>

                                                        <table width="100%" cellspacing="0" cellpadding="0" border="0" class="invoice-total">
                                                            <tr>
                                                                <td>TỔNG CỘNG</td>
                                                                <td align="right" style="color: #00B14F;">
                                                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Call to Action -->
                            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ env('FRONTEND_URL', 'http://localhost:8000') }}/orders/{{ $order->id }}" class="btn-action">
                                            Xem đơn hàng của bạn
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <p style="margin: 0 0 10px 0;">Cảm ơn bạn đã mua hàng tại Laravel Store!</p>
                            <p style="margin: 0; font-size: 11px; color: #aaaaaa;">
                                Đây là thư tự động từ hệ thống quản lý đơn hàng. Vui lòng không trả lời thư này.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
