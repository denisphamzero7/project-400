<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hóa đơn #{{ $order->id }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 12px;
            color: #333333;
            background-color: #f6f6f6;
            line-height: 1.4;
        }
        .invoice-box {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            min-height: 100%;
            box-sizing: border-box;
        }
        .header {
            background-color: #00B14F;
            padding: 25px 20px;
            color: #ffffff;
        }
        .header-logo {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .content {
            padding: 25px 20px;
        }
        .status-title {
            color: #00B14F;
            font-size: 22px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .summary-box {
            margin-bottom: 25px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 15px;
            width: 100%;
        }
        .summary-title {
            font-size: 12px;
            color: #888888;
            margin: 0;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 20px;
            color: #00B14F;
            font-weight: bold;
            margin: 5px 0 0 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            vertical-align: top;
            padding: 0;
        }
        .section-title {
            color: #00B14F;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .info-label {
            font-size: 10px;
            color: #888888;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 13px;
            color: #333333;
            margin: 0 0 8px 0;
            font-weight: bold;
        }
        .item-row {
            font-size: 12px;
            border-bottom: 1px dashed #f0f0f0;
            padding: 6px 0;
        }
        .invoice-card {
            background-color: #fafafa;
            border: 1px solid #eeeeee;
            border-radius: 4px;
            padding: 15px;
        }
        .invoice-row {
            font-size: 12px;
            margin-bottom: 6px;
            width: 100%;
        }
        .invoice-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px double #dddddd;
            border-bottom: 2px double #dddddd;
            padding: 8px 0;
            margin-top: 10px;
            width: 100%;
        }
        .footer {
            margin-top: 50px;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #999999;
            border-top: 1px dashed #e5e5e5;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Green Header Bar -->
        <div class="header">
            <div class="header-logo">Laravel Store</div>
        </div>
        
        <!-- Main Body -->
        <div class="content">
            <div class="status-title">Hóa Đơn Thanh Toán</div>
            
            <!-- Summary Top Row -->
            <table class="summary-box">
                <tr>
                    <td style="width: 50%; vertical-align: bottom;">
                        <p class="summary-title">Tổng cộng</p>
                        <p class="summary-value">{{ number_format($order->total_amount, 0, ',', '.') }} đ</p>
                    </td>
                    <td style="width: 50%; text-align: right; vertical-align: bottom;">
                        <p class="summary-title">Ngày hóa đơn</p>
                        <p style="font-size: 14px; margin: 5px 0 0 0; font-weight: bold;">
                            {{ $order->created_at?->format('d M Y') ?? now()->format('d M Y') }}
                        </p>
                    </td>
                </tr>
            </table>
            
            <!-- Details Layout (2 columns using tables) -->
            <table class="details-table">
                <tr>
                    <!-- Left Column: Order details -->
                    <td style="width: 55%; padding-right: 15px;">
                        <div class="section-title">Chi tiết đơn hàng</div>
                        
                        <div class="info-label">Khách hàng</div>
                        <div class="info-value">{{ $order->customer->name }}</div>
                        
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $order->customer->email }}</div>
                        
                        <div class="info-label">Mã đơn hàng</div>
                        <div class="info-value" style="font-family: monospace; font-size: 13px; color: #555555;">
                            #{{ $order->id }}
                        </div>
                        
                        <div class="info-label" style="margin-top: 15px; margin-bottom: 5px;">Danh sách sản phẩm</div>
                        <table style="width: 100%;">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="item-row" style="padding: 6px 0; width: 70%;">
                                        <div style="font-weight: bold; font-size: 12px;">{{ $item->product->name }}</div>
                                        <div style="color: #666666; font-size: 10px;">
                                            {{ number_format($item->price, 0, ',', '.') }} đ &times; {{ $item->quantity }}
                                        </div>
                                    </td>
                                    <td class="item-row" style="text-align: right; vertical-align: middle; font-weight: bold; font-size: 12px; width: 30%;">
                                        {{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                    
                    <!-- Right Column: Cost box -->
                    <td style="width: 45%;">
                        <div class="section-title">Hóa đơn bán hàng</div>
                        
                        <div class="invoice-card">
                            <table style="width: 100%;">
                                <tr>
                                    <td class="info-label" style="margin-top: 0;">Phương thức thanh toán</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 13px; font-weight: bold; padding-bottom: 8px; border-bottom: 1px dashed #dddddd;">
                                        Thanh toán trực tuyến
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td style="padding-top: 8px;">
                                        <table class="invoice-row">
                                            <tr>
                                                <td style="color: #666666;">Tạm tính</td>
                                                <td style="text-align: right;">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                            </tr>
                                        </table>
                                        <table class="invoice-row">
                                            <tr>
                                                <td style="color: #666666;">Khuyến mại</td>
                                                <td style="text-align: right; color: #e11d48;">- 0 đ</td>
                                            </tr>
                                        </table>
                                        
                                        <table class="invoice-total">
                                            <tr>
                                                <td>TỔNG CỘNG</td>
                                                <td style="text-align: right; color: #00B14F;">
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
            
            <!-- Footer -->
            <div class="footer">
                <div>Cảm ơn quý khách đã mua sắm tại Laravel Store!</div>
                <div style="margin-top: 5px; font-size: 9px; color: #aaaaaa;">
                    Hóa đơn điện tử được tạo tự động bởi hệ thống bán hàng trực tuyến.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
