# Kịch bản Kiểm thử (Test Cases) cho Hệ thống Quản lý Đơn hàng

Tài liệu này mô tả các kịch bản kiểm thử cho các API của hệ thống, giúp bạn kiểm tra từng chức năng trên Postman.

**Thiết lập môi trường Postman:**
*   **Base URL:** `{{base_url}}` (ví dụ: `http://your-project.test/api`)
*   **Headers chung:**
    *   `Content-Type`: `application/json`
    *   `Accept`: `application/json`

---

## 1. Chuẩn bị Dữ liệu (Seeding)

Trước khi test các luồng chính, hãy tạo một vài dữ liệu nền.

### 1.1. Tạo Khách hàng

*   **Endpoint:** `POST {{base_url}}/customers`
*   **Body:**
    ```json
    {
        "name": "Nguyễn Văn A",
        "email": "nguyenvana@example.com"
    }
    ```
    ```json
    {
        "name": "Trần Thị B",
        "email": "tranthib@example.com"
    }
    ```
*   **Kết quả:** Ghi lại `id` của các khách hàng vừa tạo để dùng cho các bước sau.

### 1.2. Tạo Sản phẩm

*   **Endpoint:** `POST {{base_url}}/products`
*   **Body:**
    ```json
    {
        "name": "Laptop ProMax 2026",
        "price": "35000000.00",
        "stock_quantity": 50
    }
    ```
    ```json
    {
        "name": "Chuột không dây X-Series",
        "price": "750000.00",
        "stock_quantity": 100
    }
    ```
    ```json
    {
        "name": "Bàn phím cơ RGB",
        "price": "2100000.00",
        "stock_quantity": 30
    }
    ```
    ```json
    {
        "name": "Sản phẩm sắp hết hàng",
        "price": "100000.00",
        "stock_quantity": 2
    }
    ```
*   **Kết quả:** Ghi lại `id` của các sản phẩm vừa tạo.

---

## 2. Test Luồng CRUD & Filter (Yêu cầu 1)

### 2.1. API Sản phẩm (`/products`)

*   **Lấy danh sách có filter:** `GET {{base_url}}/products`
    *   **Case 1: Lọc theo tên:** `?search=Laptop`
    *   **Case 2: Lọc theo khoảng giá:** `?min_price=2000000&max_price=4000000`
    *   **Case 3: Lọc sản phẩm sắp hết hàng:** `?low_stock=true` (Giả sử logic là `stock < 10`)
    *   **Case 4: Kết hợp nhiều filter:** `?search=chuột&max_price=1000000`

### 2.2. API Khách hàng (`/customers`)

*   **Lấy danh sách có filter:** `GET {{base_url}}/customers`
    *   **Case 1: Tìm theo tên:** `?search=Nguyễn Văn A`
    *   **Case 2: Tìm theo email:** `?search=tranthib@example.com`

---

## 3. Test Luồng Tạo Đơn hàng (Transaction & Rollback - Yêu cầu 2)

Sử dụng `id` của khách hàng và sản phẩm đã tạo ở bước 1.

### 3.1. Kịch bản THÀNH CÔNG

*   **Mục tiêu:** Tạo đơn hàng thành công, số lượng tồn kho của sản phẩm bị trừ đi.
*   **Endpoint:** `POST {{base_url}}/orders`
*   **Body:**
    ```json
    {
        "customer_id": 1, // Thay bằng ID khách hàng đã tạo
        "items": [
            { "product_id": 1, "quantity": 1 }, // Laptop
            { "product_id": 2, "quantity": 2 }  // Chuột
        ]
    }
    ```
*   **Kiểm tra:**
    1.  API trả về `status 201 Created` và thông tin đơn hàng.
    2.  Kiểm tra DB:
        *   Bảng `orders` có một bản ghi mới.
        *   Bảng `order_items` có 2 bản ghi mới tương ứng.
        *   `total_amount` trong `orders` được tính đúng.
        *   `stock_quantity` của sản phẩm 1 giảm đi 1, sản phẩm 2 giảm đi 2.

### 3.2. Kịch bản THẤT BẠI (Hết hàng)

*   **Mục tiêu:** Khi một sản phẩm không đủ hàng, toàn bộ giao dịch được `rollback`.
*   **Endpoint:** `POST {{base_url}}/orders`
*   **Body:**
    ```json
    {
        "customer_id": 1,
        "items": [
            { "product_id": 2, "quantity": 5 },  // Chuột (đủ hàng)
            { "product_id": 4, "quantity": 10 } // Sản phẩm sắp hết hàng (chỉ có 2)
        ]
    }
    ```
*   **Kiểm tra:**
    1.  API trả về lỗi (ví dụ: `status 500` hoặc `422`) với thông báo `"Sản phẩm 'Sản phẩm sắp hết hàng' không đủ số lượng tồn kho."`.
    2.  Kiểm tra DB:
        *   **Không** có bản ghi mới nào trong bảng `orders` và `order_items`.
        *   `stock_quantity` của sản phẩm 2 (Chuột) **không** bị thay đổi.

---

## 4. Test Luồng Chi tiết Đơn hàng & Tự động cập nhật Tổng tiền

Mục tiêu của các test case này là kiểm tra các API của `order-items` và xác nhận rằng `total_amount` của đơn hàng cha (`orders`) được tự động cập nhật một cách chính xác nhờ `OrderItemObserver`.

*   **Bước chuẩn bị:** Tạo một đơn hàng thành công (như mục `3.1`) và ghi lại `order_id` và `total_amount` ban đầu của nó. Giả sử đơn hàng có 1 Laptop (35tr) và 2 Chuột (2 * 750k = 1.5tr), tổng ban đầu là `36,500,000`. Ghi lại `id` của `order_item` chứa "Chuột".

### 4.1. Kịch bản: Thêm một sản phẩm vào đơn hàng đã có

*   **Mục tiêu:** Thêm một "Bàn phím cơ" (giá 2,100,000) vào đơn hàng. Tổng tiền mới phải được cộng thêm.
*   **Endpoint:** `POST {{base_url}}/order-items`
*   **Body:**
    ```json
    {
        "order_id": 1, // ID của đơn hàng đã tạo
        "product_id": 3, // ID của "Bàn phím cơ RGB"
        "quantity": 1
    }
    ```
*   **Kiểm tra:**
    1.  API trả về `status 201 Created` với thông tin chi tiết của item vừa thêm.
    2.  Kiểm tra DB: `total_amount` của đơn hàng `id=1` đã được cập nhật thành `38,600,000` (36,500,000 + 2,100,000).

### 4.2. Kịch bản: Cập nhật số lượng của một sản phẩm

*   **Mục tiêu:** Thay đổi số lượng "Chuột" từ 2 thành 3. Tổng tiền phải được tính lại.
*   **Endpoint:** `PUT {{base_url}}/order-items/{item_id}` (sử dụng ID của item "Chuột")
*   **Body:**
    ```json
    {
        "quantity": 3
    }
    ```
*   **Kiểm tra:**
    1.  API trả về `status 200 OK`.
    2.  Kiểm tra DB: `total_amount` của đơn hàng `id=1` đã được cập nhật thành `39,350,000` (35tr laptop + 3 * 750k chuột + 2.1tr bàn phím).

### 4.3. Kịch bản: Xóa một sản phẩm khỏi đơn hàng

*   **Mục tiêu:** Xóa "Bàn phím cơ" vừa thêm ra khỏi đơn hàng. Tổng tiền phải được trừ đi.
*   **Endpoint:** `DELETE {{base_url}}/order-items/{item_id}` (sử dụng ID của item "Bàn phím cơ")
*   **Kiểm tra:**
    1.  API trả về `status 204 No Content`.
    2.  Kiểm tra DB: `total_amount` của đơn hàng `id=1` đã được cập nhật trở lại `37,250,000` (35tr laptop + 3 * 750k chuột).

### 4.4. Kịch bản: Thay đổi sản phẩm trong một chi tiết đơn hàng

*   **Mục tiêu:** Đổi 3 "Chuột" (750k/cái) thành 3 "Sản phẩm sắp hết hàng" (100k/cái). Giá và tổng tiền phải tự cập nhật.
*   **Endpoint:** `PUT {{base_url}}/order-items/{item_id}` (sử dụng ID của item "Chuột")
*   **Body:** `{"product_id": 4, "quantity": 3}`
*   **Kiểm tra:** `price` của order item này đổi thành `100000.00` và `total_amount` của đơn hàng được tính lại.

---

## 5. Test Luồng Observer (Hoàn kho - Yêu cầu 3)

### 4.1. Observer khi `updating` (Hủy đơn hàng)

*   **Mục tiêu:** Khi trạng thái đơn hàng đổi thành `cancelled`, số lượng sản phẩm được cộng lại vào kho.
*   **Bước 1:** Lấy `id` của đơn hàng đã tạo thành công ở mục `3.1`.
*   **Bước 2:** Gọi API cập nhật.
    *   **Endpoint:** `PUT {{base_url}}/orders/{order_id}` (Sử dụng ID đơn hàng từ mục 3.1)
    *   **Body:**
        ```json
        {
            "status": "cancelled"
        }
        ```
*   **Kiểm tra:**
    1.  API trả về `status 200 OK`.
    2.  Trạng thái đơn hàng trong DB đổi thành `cancelled`.
    3.  Kiểm tra DB: `stock_quantity` của sản phẩm 1 được cộng lại 1, sản phẩm 2 được cộng lại 2.

---

## 6. Test Luồng Event, Listener & Queue (Yêu cầu 4, 5)

*   **Mục tiêu:** Khi đơn hàng được thanh toán, các tác vụ (gửi mail, cộng điểm) được đẩy vào queue để xử lý ngầm, API phản hồi ngay lập tức.
*   **Yêu cầu môi trường:** Đảm bảo `php artisan horizon` đang chạy và `QUEUE_CONNECTION=redis`.

### 5.1. Kích hoạt Event `OrderPaid`

*   **Bước 1:** Tạo một đơn hàng mới thành công (tương tự mục `3.1`). Ghi lại `id` đơn hàng.
*   **Bước 2:** Cập nhật trạng thái đơn hàng thành `completed`.
    *   **Endpoint:** `PUT {{base_url}}/orders/{new_order_id}`
    *   **Body:**
        ```json
        {
            "status": "completed"
        }
        ```
*   **Kiểm tra:**
    1.  **Tốc độ phản hồi:** API trả về `status 200 OK` gần như ngay lập tức (< 200ms).
    2.  **Horizon Dashboard (`/horizon`):**
        *   Thấy 2 jobs mới được đẩy vào queue (ví dụ: `SendOrderConfirmationEmail`, `UpdateCustomerLoyaltyPoints`).
        *   Các jobs này chuyển từ `Pending` -> `Completed`.
    3.  **Kiểm tra kết quả chạy ngầm:**
        *   Kiểm tra log mail (nếu dùng `MAIL_MAILER=log`) hoặc hộp thư Mailtrap.
        *   Kiểm tra DB: `loyalty_points` của khách hàng đã được cộng thêm.

---

## 7. Test Luồng Broadcast (Real-time - Yêu cầu 6)

*   **Mục tiêu:** Khi có đơn hàng giá trị lớn, server phát một sự kiện WebSocket.
*   **Yêu cầu môi trường:**
    *   `php artisan reverb:start` đang chạy.
    *   Mở một trang frontend có kết nối Laravel Echo và lắng nghe trên channel `BigOrderPlaced`.

### 6.1. Tạo đơn hàng lớn

*   **Endpoint:** `POST {{base_url}}/orders`
*   **Body:** (Đảm bảo tổng giá trị > 10,000,000 VNĐ)
    ```json
    {
        "customer_id": 1,
        "items": [
            { "product_id": 1, "quantity": 1 } // Laptop ProMax giá 35,000,000
        ]
    }
    ```
*   **Kiểm tra:**
    1.  API tạo đơn hàng thành công.
    2.  Trên giao diện frontend, `console.log` hoặc `alert` hiển thị thông báo real-time về đơn hàng lớn vừa được đặt mà không cần tải lại trang.

---

## 8. Test Luồng Scheduler (Cron Job - Yêu cầu 7)

*   **Mục tiêu:** Các đơn hàng `pending` quá 48 giờ sẽ tự động chuyển thành `expired`.
*   **Yêu cầu môi trường:** Cron job đã được thiết lập để chạy `php artisan schedule:run` mỗi phút.

### 7.1. Kịch bản kiểm thử

*   **Bước 1 (Giả lập dữ liệu cũ):** Vào DB, tạo thủ công một đơn hàng với trạng thái `pending` và `created_at` là một ngày cách đây 3 ngày.
    ```sql
    INSERT INTO orders (customer_id, total_amount, status, created_at, updated_at)
    VALUES (1, 100000, 'pending', '2026-06-27 10:00:00', '2026-06-27 10:00:00');
    ```
*   **Bước 2:** Chạy lệnh scheduler thủ công để test ngay.
    ```bash
    php artisan orders:expire
    ```
    (Giả sử bạn đã tạo command `orders:expire` và đăng ký trong `Kernel.php`)
*   **Kiểm tra:**
    1.  Command báo thực thi thành công.
    2.  Kiểm tra DB: Trạng thái của đơn hàng trên đã được chuyển từ `pending` sang `expired`.

---

## 9. Test API Báo cáo (Yêu cầu 8)

*   **Mục tiêu:** Lấy báo cáo tổng hợp từ nhiều bảng.
*   **Endpoint:** `GET {{base_url}}/reports/revenue` (Giả sử bạn tạo route này)
*   **Kiểm tra:**
    1.  API trả về `status 200 OK`.
    2.  Dữ liệu JSON trả về có cấu trúc đúng như yêu cầu:
        *   Doanh thu theo từng danh mục.
        *   Top 5 khách hàng chi tiêu nhiều nhất.
        *   Tỷ lệ đơn hàng bị hủy trong tháng.

Chúc bạn test thành công!

---

## 10. Test Luồng Ghi Log của Listener (Kiểm tra các cập nhật mới)

*   **Mục tiêu:** Xác minh rằng sau khi một đơn hàng được thanh toán, các listener (`SendOrderConfirmationEmail`, `UpdateCustomerLoyaltyPoints`) hoạt động chính xác và ghi lại log thành công. Đây là bước kiểm tra quan trọng cho các thay đổi gần đây.
*   **Yêu cầu môi trường:**
    *   `php artisan horizon` hoặc `php artisan queue:work` đang chạy.
    *   File log của Laravel có thể truy cập được tại `storage/logs/laravel.log`.

### 10.1. Kịch bản: Tạo và Thanh toán Đơn hàng

Đây là API đầu tiên và quan trọng nhất cần kiểm tra sau các bản sửa lỗi gần đây.

#### Bước 1: Tạo một Đơn hàng Mới

*   **Endpoint:** `POST {{base_url}}/orders`
*   **Mục đích:** Tạo một đơn hàng cơ bản.
*   **Body:** (Sử dụng ID khách hàng và sản phẩm đã có từ bước chuẩn bị)
    ```json
    {
        "customer_id": 1,
        "items": [
            { "product_id": 1, "quantity": 1 }
        ]
    }
    ```
*   **Kết quả:** API trả về `201 Created` và thông tin đơn hàng. Ghi lại `id` của đơn hàng này (ví dụ: `new_order_id`).

#### Bước 2: Thanh toán Đơn hàng (Kích hoạt Event)

*   **Endpoint:** `PUT {{base_url}}/orders/{new_order_id}`
*   **Mục đích:** Cập nhật trạng thái đơn hàng thành `completed`, kích hoạt event `OrderPaid`.
*   **Body:**
    ```json
    {
        "status": "completed"
    }
    ```
*   **Kết quả:** API trả về `200 OK` gần như ngay lập tức.

#### Bước 3: Kiểm tra Chi tiết trong Log

*   **Hành động:** Mở file `storage/logs/laravel.log`.
*   **Mục đích:** Tìm các dòng log được ghi bởi các listener để xác nhận chúng đã thực thi thành công.
*   **Kiểm tra:** Bạn sẽ thấy các dòng log tương tự như sau xuất hiện ở cuối file:
    ```log
    // Log từ SendOrderConfirmationEmail
    [2026-07-03 10:00:00] local.INFO: Đã gửi thông báo xác nhận đơn hàng thành công cho đơn hàng ID: {new_order_id}

    // Log từ UpdateCustomerLoyaltyPoints
    [2026-07-03 10:00:00] local.INFO: Đã cộng {X} điểm thưởng cho khách hàng: Nguyễn Văn A. Tổng điểm mới: {Y}
    ```
    *   **Giải thích:**
        *   Dòng log đầu tiên xác nhận `SendOrderConfirmationEmail` đã chạy và gửi thông báo thành công.
        *   Dòng log thứ hai xác nhận `UpdateCustomerLoyaltyPoints` đã chạy, cộng điểm cho khách hàng, và hiển thị chính xác tổng điểm mới (nhờ vào việc đã sửa lỗi `refresh()` model).
