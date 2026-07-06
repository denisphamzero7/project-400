# Tài liệu API - Module Orders (Đơn hàng)

Module Orders cung cấp các API để quản lý đơn hàng của khách hàng, xử lý giỏ hàng, tự động kiểm tra tồn kho và tính toán tổng giá trị đơn hàng thông qua cơ chế transaction.

---

## 1. Thông tin chung
- **Prefix URL**: `/api/orders`
- **Định dạng dữ liệu**: `JSON` (Riêng API Import sử dụng `multipart/form-data`)
- **Trạng thái đơn hàng (`status`)**:
  - `pending` (Đang chờ xử lý)
  - `processing` (Đang xử lý)
  - `completed` (Hoàn thành / Đã thanh toán)
  - `cancelled` (Đã hủy)
  - `expired` (Hết hạn)

> [!NOTE]
> Khi đơn hàng được chuyển sang trạng thái `completed`, hệ thống sẽ tự động gửi email thông báo cho khách hàng, tạo file PDF hóa đơn và cập nhật điểm tích lũy của khách hàng bất đồng bộ qua Queue Worker.

---

## 2. Danh sách API

### 2.1. Lấy danh sách đơn hàng (Phân trang & Lọc)
- **Endpoint**: `GET /api/orders/`
- **Mô tả**: Trả về danh sách đơn hàng có phân trang, hỗ trợ lọc nâng cao theo khách hàng, thời gian và giá trị.
- **Tham số Query (Query Parameters)**:
  - `search` (string, tối đa 100 kí tự): Tìm kiếm theo Mã đơn hàng (`id`), Tên hoặc Email của khách hàng.
  - `status` (string): Lọc theo trạng thái đơn hàng (`pending`, `processing`, `completed`, `cancelled`, `expired`).
  - `from_date` (date, Y-m-d): Lọc đơn hàng tạo từ ngày.
  - `to_date` (date, Y-m-d): Lọc đơn hàng tạo đến ngày.
  - `price_from` (numeric, >= 0): Lọc đơn hàng có tổng trị giá từ.
  - `price_to` (numeric, >= `price_from`): Lọc đơn hàng có tổng trị giá đến.
  - `sort_by` (string): Trường sắp xếp (mặc định: `created_at`).
  - `sort_order` (string: `asc` hoặc `desc`): Thứ tự sắp xếp (mặc định: `desc`).
  - `limit` (integer, 1 - 100): Số bản ghi trên mỗi trang (mặc định: `10`).

- **Response thành công (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "customer_id": 1,
        "customer_name": "Nguyễn Văn A",
        "total_amount": "550000.00",
        "status": "pending",
        "items": [
          {
            "product_id": 2,
            "product_name": "Chuột Không Dây Logitech",
            "quantity": 1,
            "price": "150000.00"
          },
          {
            "product_id": 3,
            "product_name": "Bàn Phím Cơ Keychron K2",
            "quantity": 1,
            "price": "400000.00"
          }
        ],
        "created_at": "2026-07-06T15:00:00+07:00",
        "updated_at": "2026-07-06T15:00:00+07:00"
      }
    ],
    "links": {
      "first": "http://localhost/api/orders?page=1",
      "last": "http://localhost/api/orders?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://localhost/api/orders",
      "per_page": 10,
      "to": 1,
      "total": 1
    },
    "success": true
  }
  ```

---

### 2.2. Thống kê đơn hàng
- **Endpoint**: `GET /api/orders/stats`
- **Mô tả**: Thống kê số lượng đơn hàng theo từng trạng thái cụ thể dựa trên bộ lọc truyền lên.
- **Tham số Query (Query Parameters)**: Tương tự như bộ lọc danh sách.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "total": 12,
      "pending": 5,
      "completed": 4,
      "cancelled": 2,
      "expired": 1
    }
  }
  ```

---

### 2.3. Xem chi tiết 1 đơn hàng
- **Endpoint**: `GET /api/orders/{order}`
- **Mô tả**: Xem thông tin chi tiết một đơn hàng kèm thông tin khách hàng và danh sách các sản phẩm đã mua.
- **Tham số URL**: `{order}` - ID của đơn hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "customer_id": 1,
      "customer_name": "Nguyễn Văn A",
      "total_amount": "550000.00",
      "status": "pending",
      "items": [
        {
          "product_id": 2,
          "product_name": "Chuột Không Dây Logitech",
          "quantity": 1,
          "price": "150000.00"
        }
      ],
      "created_at": "2026-07-06T15:00:00+07:00",
      "updated_at": "2026-07-06T15:00:00+07:00"
    }
  }
  ```

---

### 2.4. Tạo đơn hàng mới
- **Endpoint**: `POST /api/orders/`
- **Mô tả**: Tạo đơn hàng mới. Hệ thống sẽ tự động trừ số lượng tồn kho sản phẩm, tính toán lại tổng giá trị đơn hàng và cập nhật vào CSDL trong một transaction.
- **Body Parameters (JSON)**:
  - `customer_id` (Bắt buộc, integer, exists:customers,id): ID khách hàng mua hàng.
  - `status` (Không bắt buộc, string, enum: `pending`, `processing`, `completed`, `cancelled`, `expired` - mặc định: `pending`).
  - `items` (Bắt buộc, array, tối thiểu 1 phần tử): Danh sách sản phẩm mua.
  - `items.*.product_id` (Bắt buộc, integer, exists:products,id): ID sản phẩm.
  - `items.*.quantity` (Bắt buộc, integer, >= 1): Số lượng sản phẩm muốn mua.
- **Response thành công (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Đơn hàng đã được tạo thành công!",
    "data": {
      "id": 2,
      "customer_id": 1,
      "total_amount": 550000,
      "status": "pending",
      "created_at": "2026-07-06T22:20:00+07:00",
      "updated_at": "2026-07-06T22:20:00+07:00"
    }
  }
  ```
- **Response lỗi nghiệp vụ (ví dụ: hết hàng) (500 Internal Server Error)**:
  ```json
  {
    "success": false,
    "message": "Tạo đơn hàng thất bại!",
    "code": "Sản phẩm 'Bàn Phím Cơ Keychron K2' không đủ số lượng tồn kho."
  }
  ```

---

### 2.5. Cập nhật thông tin đơn hàng
- **Endpoint**: `PUT /api/orders/{order}` hoặc `PATCH /api/orders/{order}`
- **Mô tả**: Cập nhật thông tin cơ bản của đơn hàng (như khách hàng, tổng tiền hoặc trạng thái).
- **Tham số URL**: `{order}` - ID của đơn hàng.
- **Body Parameters (JSON)**:
  - `customer_id` (Không bắt buộc, integer, exists:customers,id): ID khách hàng.
  - `total_amount` (Không bắt buộc, numeric, >= 0): Tổng trị giá cập nhật.
  - `status` (Không bắt buộc, string, enum: `pending`, `processing`, `completed`, `cancelled`, `expired`): Trạng thái đơn hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật thông tin thành công!",
    "data": {
      "id": 1,
      "customer_id": 1,
      "total_amount": "550000.00",
      "status": "completed",
      "created_at": "2026-07-06T15:00:00+07:00",
      "updated_at": "2026-07-06T22:22:00+07:00"
    }
  }
  ```

---

### 2.6. Xóa đơn hàng
- **Endpoint**: `DELETE /api/orders/{order}`
- **Mô tả**: Xóa hoàn toàn một đơn hàng khỏi hệ thống.
- **Tham số URL**: `{order}` - ID của đơn hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đơn hàng đã được xóa!"
  }
  ```

---

### 2.7. Xóa hàng loạt đơn hàng
- **Endpoint**: `POST /api/orders/bulk-delete`
- **Mô tả**: Xóa nhiều đơn hàng cùng lúc bằng danh sách ID.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID đơn hàng cần xóa.
  - `ids.*` (Bắt buộc, integer, exists:orders,id): ID phải hợp lệ và tồn tại.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã xóa thành công các đơn hàng được chọn!"
  }
  ```

---

### 2.8. Cập nhật trạng thái hàng loạt
- **Endpoint**: `POST /api/orders/bulk-status`
- **Mô tả**: Cập nhật trạng thái của nhiều đơn hàng cùng lúc.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID đơn hàng.
  - `ids.*` (Bắt buộc, integer, exists:orders,id): ID đơn hàng hợp lệ.
  - `status` (Bắt buộc, string, enum: `pending`, `processing`, `completed`, `cancelled`, `expired`): Trạng thái muốn cập nhật.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật trạng thái hàng loạt thành công!"
  }
  ```

---

### 2.9. Xuất danh sách đơn hàng (Excel)
- **Endpoint**: `GET /api/orders/export`
- **Mô tả**: Xuất danh sách đơn hàng ra file Excel (.xlsx) dựa theo bộ lọc.
- **Tham số Query (Query Parameters)**: Tương tự như bộ lọc danh sách.
- **Response**: Trả về file nhị phân tải xuống trực tiếp (`orders.xlsx`).

---

### 2.10. Nhập danh sách đơn hàng từ Excel
- **Endpoint**: `POST /api/orders/import`
- **Mô tả**: Nhập dữ liệu đơn hàng từ file bảng tính.
- **Headers**: `Content-Type: multipart/form-data`
- **Body Parameters (Form Data)**:
  - `file` (Bắt buộc, file): Định dạng file `.xlsx`, `.xls` hoặc `.csv`.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Import dữ liệu đơn hàng thành công."
  }
  ```
