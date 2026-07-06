# Tài liệu API - Module Order Items (Chi tiết đơn hàng)

Module Order Items cung cấp các API để quản lý trực tiếp các mặt hàng (sản phẩm, số lượng, giá tại thời điểm mua) nằm trong một đơn hàng.

---

## 1. Thông tin chung
- **Prefix URL**: `/api/orderitems`
- **Định dạng dữ liệu**: `JSON` (Riêng API Import sử dụng `multipart/form-data`)

> [!NOTE]
> Khi thêm mới hoặc thay đổi sản phẩm trong chi tiết đơn hàng, hệ thống sẽ tự động lấy giá bán hiện tại của sản phẩm đó trong bảng `products` để áp dụng vào trường `price` của chi tiết đơn hàng.

---

## 2. Danh sách API

### 2.1. Lấy danh sách chi tiết đơn hàng (Phân trang & Lọc)
- **Endpoint**: `GET /api/orderitems/`
- **Mô tả**: Trả về danh sách tất cả các dòng chi tiết đơn hàng (sản phẩm, số lượng, giá bán) kèm thông tin tên sản phẩm đã được eager load.
- **Tham số Query (Query Parameters)**:
  - `search` (string): Bộ lọc tìm kiếm.
  - `status` (string): Lọc trạng thái.
  - `from_date` / `to_date` (date, Y-m-d): Lọc theo thời gian.
  - `sort_by` / `sort_order`: Sắp xếp dữ liệu.
  - `limit` (integer): Giới hạn số lượng hiển thị (mặc định: `10`).

- **Response thành công (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 2,
        "product_name": "Chuột Không Dây Logitech",
        "quantity": 1,
        "price": 150000
      }
    ],
    "links": {
      "first": "http://localhost/api/orderitems?page=1",
      "last": "http://localhost/api/orderitems?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://localhost/api/orderitems",
      "per_page": 10,
      "to": 1,
      "total": 1
    },
    "success": true
  }
  ```

---

### 2.2. Thống kê chi tiết đơn hàng
- **Endpoint**: `GET /api/orderitems/stats`
- **Mô tả**: Đếm số lượng các dòng chi tiết đơn hàng trong hệ thống theo bộ lọc.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "total": 100
    }
  }
  ```

---

### 2.3. Xem chi tiết một dòng vật phẩm đơn hàng
- **Endpoint**: `GET /api/orderitems/{orderItem}`
- **Mô tả**: Xem chi tiết của một dòng sản phẩm trong đơn hàng kèm liên kết đến đơn hàng cha (`order`) và sản phẩm (`product`).
- **Tham số URL**: `{orderItem}` - ID của chi tiết đơn hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "order_id": 1,
      "product_id": 2,
      "product_name": "Chuột Không Dây Logitech",
      "quantity": 1,
      "price": 150000
    }
  }
  ```

---

### 2.4. Thêm sản phẩm vào đơn hàng (Tạo mới chi tiết đơn hàng)
- **Endpoint**: `POST /api/orderitems/`
- **Mô tả**: Thêm một dòng sản phẩm vào đơn hàng hiện có. Hệ thống sẽ tự động truy vấn giá bán hiện tại của sản phẩm để áp vào hóa đơn.
- **Body Parameters (JSON)**:
  - `order_id` (Bắt buộc, integer, exists:orders,id): ID đơn hàng cần thêm sản phẩm.
  - `product_id` (Bắt buộc, integer, exists:products,id): ID sản phẩm muốn thêm.
  - `quantity` (Bắt buộc, integer, >= 1): Số lượng sản phẩm.
- **Response thành công (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Chi tiết đơn hàng đã được tạo thành công!",
    "data": {
      "id": 2,
      "order_id": 1,
      "product_id": 3,
      "product_name": "Bàn Phím Cơ Keychron K2",
      "quantity": 1,
      "price": 400000
    }
  }
  ```

---

### 2.5. Cập nhật chi tiết đơn hàng
- **Endpoint**: `PUT /api/orderitems/{orderItem}` hoặc `PATCH /api/orderitems/{orderItem}`
- **Mô tả**: Cập nhật số lượng hoặc đổi sản phẩm trong chi tiết đơn hàng. Nếu đổi sản phẩm mới, hệ thống sẽ tự động cập nhật lại đơn giá bán mới.
- **Tham số URL**: `{orderItem}` - ID của chi tiết đơn hàng.
- **Body Parameters (JSON)**:
  - `order_id` (Không bắt buộc, integer, exists:orders,id): ID đơn hàng.
  - `product_id` (Không bắt buộc, integer, exists:products,id): ID sản phẩm mới.
  - `quantity` (Không bắt buộc, integer, >= 1): Số lượng sản phẩm mới.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật thông tin thành công!",
    "data": {
      "id": 1,
      "order_id": 1,
      "product_id": 2,
      "quantity": 3,
      "price": 150000
    }
  }
  ```

---

### 2.6. Xóa dòng chi tiết đơn hàng
- **Endpoint**: `DELETE /api/orderitems/{orderItem}`
- **Mô tả**: Xóa hoàn toàn dòng chi tiết đơn hàng này khỏi hóa đơn.
- **Tham số URL**: `{orderItem}` - ID của chi tiết đơn hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đơn hàng đã được xóa!"
  }
  ```

---

### 2.7. Xóa hàng loạt chi tiết đơn hàng
- **Endpoint**: `POST /api/orderitems/bulk-delete`
- **Mô tả**: Xóa hàng loạt chi tiết đơn hàng bằng danh sách ID.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID cần xóa.
  - `ids.*` (Bắt buộc, integer, exists:orders,id): Lưu ý rằng hệ thống hiện tại yêu cầu validate ID của các dòng này dựa trên bảng `orders` (do cấu hình validation gốc).
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã xóa thành công các đơn hàng được chọn!"
  }
  ```

---

### 2.8. Cập nhật trạng thái hàng loạt chi tiết đơn hàng
- **Endpoint**: `POST /api/orderitems/bulk-status`
- **Mô tả**: Cập nhật trạng thái cho nhiều dòng chi tiết đơn hàng.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID chi tiết đơn hàng cần cập nhật.
  - `ids.*` (Bắt buộc, integer, exists:order_items,id): ID chi tiết đơn hàng hợp lệ.
  - `status` (Bắt buộc, string, enum theo trạng thái đơn hàng: `pending`, `processing`, `completed`, `cancelled`, `expired`): Trạng thái cập nhật.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật trạng thái hàng loạt thành công!"
  }
  ```

---

### 2.9. Xuất danh sách chi tiết đơn hàng (Excel)
- **Endpoint**: `GET /api/orderitems/export`
- **Mô tả**: Xuất dữ liệu chi tiết đơn hàng ra file Excel (.xlsx) dựa theo bộ lọc.
- **Response**: Trả về file nhị phân tải xuống trực tiếp (`order_items.xlsx`).

---

### 2.10. Nhập danh sách chi tiết đơn hàng từ Excel
- **Endpoint**: `POST /api/orderitems/import`
- **Mô tả**: Nhập dữ liệu chi tiết đơn hàng từ file bảng tính.
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
