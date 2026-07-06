# Tài liệu Hướng dẫn Sử dụng & Lập trình dự án

Thư mục `docs/` chứa toàn bộ tài liệu đặc tả nghiệp vụ, kiến trúc hệ thống, quy trình kiểm thử và tài liệu hướng dẫn sử dụng API chi tiết cho các module trong dự án.

---

## 📂 Danh mục Tài liệu

### 1. ⚙️ Hướng dẫn Tích hợp & Vận hành
*   [Kiến trúc Hệ thống & Luồng xử lý nghiệp vụ](file:///d:/xampp/htdocs/new/project/app_test/docs/project_architecture_and_workflows.md): Giải thích mô hình Event-Driven Architecture, các queue worker, Horizon và cơ chế cập nhật tự động bằng Eloquent Observers.
*   [Cấu hình Real-time Queue với Redis](file:///d:/xampp/htdocs/new/project/app_test/docs/laravel-realtime-queues-redis.md): Hướng dẫn thiết lập Docker, Redis, Horizon, chạy queue workers và giám sát hàng đợi xử lý hóa đơn, gửi email.
*   [Kịch bản kiểm thử (Test Cases)](file:///d:/xampp/htdocs/new/project/app_test/docs/TESTCASES.md): Tổng hợp danh sách các kịch bản kiểm thử tự động (Unit / Feature Tests) và hướng dẫn chạy lệnh `artisan test`.

### 2. 🔌 Đặc tả Tài liệu API (Chi tiết từng Module)
Dưới đây là tài liệu chi tiết hướng dẫn gọi API cho từng phân hệ trong dự án:

*   [API Khách hàng (Customers)](file:///d:/xampp/htdocs/new/project/app_test/docs/customer.md): API quản lý thông tin khách hàng, điểm tích lũy thành viên, xuất nhập Excel và cập nhật hàng loạt.
*   [API Sản phẩm (Products)](file:///d:/xampp/htdocs/new/project/app_test/docs/product.md): API quản lý sản phẩm, giá bán, theo dõi tồn kho (low stock) và cập nhật trạng thái hoạt động.
*   [API Đơn hàng (Orders)](file:///d:/xampp/htdocs/new/project/app_test/docs/order.md): API tạo đơn hàng, tự động trừ kho sản phẩm, cập nhật tổng tiền thông qua transaction và kích hoạt xử lý hóa đơn bất đồng bộ.
*   [API Chi tiết đơn hàng (Order Items)](file:///d:/xampp/htdocs/new/project/app_test/docs/order_item.md): API quản lý chi tiết từng mặt hàng trong đơn hàng, tự động đồng bộ giá bán hiện tại từ sản phẩm.
*   [API Báo cáo & Thống kê (Reports)](file:///d:/xampp/htdocs/new/project/app_test/docs/report.md): API phân tích doanh thu theo sản phẩm, top khách hàng chi tiêu nhiều nhất và tỷ lệ hủy đơn hàng trong tháng.

---

## 🛠️ Quy ước chung khi giao tiếp API

1.  **Base URL**: `/api` (Ví dụ: `http://localhost/api/products`)
2.  **Request Headers**:
    *   Với dữ liệu JSON: `Content-Type: application/json` và `Accept: application/json`.
    *   Với dữ liệu Import file: `Content-Type: multipart/form-data`.
3.  **Cấu trúc JSON phản hồi thành công**:
    ```json
    {
      "success": true,
      "message": "Thông điệp thành công (nếu có)",
      "data": { ... } // hoặc mảng [...] hoặc null
    }
    ```
4.  **Cấu trúc JSON phản hồi lỗi (ví dụ: Lỗi validation 422)**:
    ```json
    {
      "success": false,
      "message": "Thông báo lỗi chung",
      "errors": {
        "email": [
          "Email này đã tồn tại trong hệ thống."
        ]
      },
      "code": "VALIDATION_ERROR"
    }
    ```
