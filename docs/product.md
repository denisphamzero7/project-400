# Tài liệu API - Module Products (Sản phẩm)

Module Products cung cấp các API quản lý danh mục sản phẩm, giá bán, số lượng tồn kho và trạng thái bán hàng.

---

## 1. Thông tin chung
- **Prefix URL**: `/api/products`
- **Định dạng dữ liệu**: `JSON` (Riêng API Import sử dụng `multipart/form-data`)
- **Trạng thái sản phẩm (`status`)**:
  - `active` (Đang hoạt động / Đang bán)
  - `draft` (Bản nháp / Chưa hiển thị)
  - `archived` (Ngừng kinh doanh / Đã lưu trữ)

---

## 2. Danh sách API

### 2.1. Lấy danh sách sản phẩm (Phân trang & Lọc)
- **Endpoint**: `GET /api/products/`
- **Mô tả**: Trả về danh sách sản phẩm có hỗ trợ phân trang, tìm kiếm và lọc nâng cao theo giá/kho.
- **Tham số Query (Query Parameters)**:
  - `search` (string, tối đa 100 kí tự): Tìm kiếm theo tên sản phẩm.
  - `status` (string): Lọc theo trạng thái (`active`, `draft` hoặc `archived`).
  - `min_price` (numeric, >= 0): Lọc sản phẩm có giá lớn hơn hoặc bằng mức này.
  - `max_price` (numeric, >= `min_price`): Lọc sản phẩm có giá nhỏ hơn hoặc bằng mức này.
  - `low_stock` (any): Lọc sản phẩm sắp hết hàng (tồn kho `< 10`) khi truyền tham số này lên (ví dụ: `low_stock=true`).
  - `from_date` (date, Y-m-d): Lọc theo ngày tạo từ ngày.
  - `to_date` (date, Y-m-d): Lọc theo ngày tạo đến ngày.
  - `sort_by` (string): Trường sắp xếp (`id`, `name`, `price`, `stock_quantity`, `status`, `created_at` - mặc định: `id`).
  - `sort_order` (string: `asc` hoặc `desc`): Thứ tự sắp xếp (mặc định: `desc`).
  - `limit` (integer, 1 - 100): Số bản ghi trên mỗi trang (mặc định: `10`).

- **Response thành công (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Laptop Dell XPS 15 2024",
        "price": 35000000,
        "stock_quantity": 50,
        "status": "active",
        "created_at": "2026-07-06T15:00:00+07:00",
        "updated_at": "2026-07-06T15:00:00+07:00"
      }
    ],
    "links": {
      "first": "http://localhost/api/products?page=1",
      "last": "http://localhost/api/products?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://localhost/api/products",
      "per_page": 10,
      "to": 1,
      "total": 1
    },
    "success": true
  }
  ```

---

### 2.2. Thống kê sản phẩm
- **Endpoint**: `GET /api/products/stats`
- **Mô tả**: Trả về tổng số lượng sản phẩm phù hợp với bộ lọc truyền vào.
- **Tham số Query (Query Parameters)**: Tương tự như bộ lọc danh sách.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "total": 45
    }
  }
  ```

---

### 2.3. Xem chi tiết 1 sản phẩm
- **Endpoint**: `GET /api/products/{product}`
- **Mô tả**: Trả về thông tin chi tiết của một sản phẩm kèm theo lịch sử chi tiết đơn hàng liên quan (`order_items`).
- **Tham số URL**: `{product}` - ID của sản phẩm.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "name": "Laptop Dell XPS 15 2024",
      "price": 35000000,
      "stock_quantity": 50,
      "status": "active",
      "order_items": [
        {
          "id": 10,
          "order_id": 5,
          "product_id": 1,
          "quantity": 2,
          "price": "35000000.00"
        }
      ],
      "created_at": "2026-07-06T15:00:00+07:00",
      "updated_at": "2026-07-06T15:00:00+07:00"
    }
  }
  ```
- **Response không tìm thấy (404 Not Found)**:
  ```json
  {
    "success": false,
    "message": "Không tìm thấy tài nguyên",
    "code": "NOT_FOUND"
  }
  ```

---

### 2.4. Tạo sản phẩm mới
- **Endpoint**: `POST /api/products/`
- **Mô tả**: Thêm mới một sản phẩm vào hệ thống.
- **Body Parameters (JSON)**:
  - `name` (Bắt buộc, string, tối đa 255): Tên sản phẩm.
  - `price` (Bắt buộc, numeric, >= 0): Giá bán sản phẩm.
  - `stock_quantity` (Bắt buộc, integer, >= 0): Số lượng tồn kho.
  - `status` (Không bắt buộc, string, enum: `active`, `draft`, `archived`): Trạng thái sản phẩm.
- **Response thành công (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Sản phẩm đã được tạo thành công!",
    "data": {
      "id": 2,
      "name": "MacBook Pro M3 2024",
      "price": 45000000,
      "stock_quantity": 30,
      "status": "active",
      "created_at": "2026-07-06T22:10:00+07:00",
      "updated_at": "2026-07-06T22:10:00+07:00"
    }
  }
  ```

---

### 2.5. Cập nhật thông tin sản phẩm
- **Endpoint**: `PUT /api/products/{product}` hoặc `PATCH /api/products/{product}`
- **Mô tả**: Cập nhật thông tin sản phẩm (hỗ trợ cập nhật một phần qua PATCH).
- **Tham số URL**: `{product}` - ID của sản phẩm.
- **Body Parameters (JSON)**:
  - `name` (Không bắt buộc, string, tối đa 255): Tên sản phẩm.
  - `price` (Không bắt buộc, numeric, >= 0): Giá bán sản phẩm.
  - `stock_quantity` (Không bắt buộc, integer, >= 0): Số lượng tồn kho.
  - `status` (Không bắt buộc, string, enum: `active`, `draft`, `archived`): Trạng thái sản phẩm.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật thông tin thành công!",
    "data": {
      "id": 1,
      "name": "Laptop Dell XPS 15 2024 (Bản nâng cấp)",
      "price": 34000000,
      "stock_quantity": 45,
      "status": "active",
      "created_at": "2026-07-06T15:00:00+07:00",
      "updated_at": "2026-07-06T22:12:00+07:00"
    }
  }
  ```

---

### 2.6. Xóa sản phẩm
- **Endpoint**: `DELETE /api/products/{product}`
- **Mô tả**: Xóa hoàn toàn một sản phẩm khỏi hệ thống.
- **Tham số URL**: `{product}` - ID của sản phẩm.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Sản phẩm đã được xóa!"
  }
  ```

---

### 2.7. Xóa hàng loạt sản phẩm
- **Endpoint**: `POST /api/products/bulk-delete`
- **Mô tả**: Xóa nhiều sản phẩm cùng lúc bằng danh sách ID.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID sản phẩm cần xóa.
  - `ids.*` (Bắt buộc, integer, exists:products,id): ID sản phẩm phải hợp lệ và tồn tại.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã xóa thành công các sản phẩm được chọn!"
  }
  ```

---

### 2.8. Cập nhật trạng thái hàng loạt
- **Endpoint**: `POST /api/products/bulk-status`
- **Mô tả**: Cập nhật trạng thái của nhiều sản phẩm cùng lúc.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID sản phẩm.
  - `ids.*` (Bắt buộc, integer, exists:products,id): ID sản phẩm hợp lệ.
  - `status` (Bắt buộc, string, enum: `active`, `draft`, `archived`): Trạng thái muốn cập nhật.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật trạng thái hàng loạt cho sản phẩm thành công!"
  }
  ```

---

### 2.9. Xuất danh sách sản phẩm (Excel)
- **Endpoint**: `GET /api/products/export`
- **Mô tả**: Xuất danh sách sản phẩm ra file Excel (.xlsx) dựa theo bộ lọc.
- **Tham số Query (Query Parameters)**: Tương tự như bộ lọc danh sách.
- **Response**: Trả về file nhị phân tải xuống trực tiếp (`products.xlsx`).

---

### 2.10. Nhập danh sách sản phẩm từ Excel
- **Endpoint**: `POST /api/products/import`
- **Mô tả**: Nhập dữ liệu sản phẩm từ file bảng tính.
- **Headers**: `Content-Type: multipart/form-data`
- **Body Parameters (Form Data)**:
  - `file` (Bắt buộc, file): Định dạng file `.xlsx`, `.xls` hoặc `.csv`.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Import dữ liệu sản phẩm thành công."
  }
  ```
