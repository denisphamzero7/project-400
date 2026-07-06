# Tài liệu API - Module Customers (Khách hàng)

Module Customers cung cấp các API để quản lý thông tin khách hàng, điểm tích lũy và trạng thái hoạt động của họ. Hỗ trợ đầy đủ các tính năng CRUD, xuất nhập Excel, lọc nâng cao, và xử lý hàng loạt.

---

## 1. Thông tin chung
- **Prefix URL**: `/api/customers`
- **Định dạng dữ liệu**: `JSON` (Riêng API Import sử dụng `multipart/form-data`)
- **Trạng thái khách hàng (`status`)**:
  - `active` (Hoạt động)
  - `inactive` (Không hoạt động)

---

## 2. Danh sách API

### 2.1. Lấy danh sách khách hàng (Phân trang & Lọc)
- **Endpoint**: `GET /api/customers/`
- **Mô tả**: Trả về danh sách khách hàng có hỗ trợ phân trang, tìm kiếm và sắp xếp.
- **Tham số Query (Query Parameters)**:
  - `search` (string, tối đa 100 kí tự): Tìm kiếm theo tên hoặc email.
  - `status` (string): Lọc theo trạng thái (`active` hoặc `inactive`).
  - `from_date` (date, Y-m-d): Lọc khách hàng tạo từ ngày này.
  - `to_date` (date, Y-m-d): Lọc khách hàng tạo đến ngày này (phải >= `from_date`).
  - `sort_by` (string): Trường sắp xếp (mặc định: `id`).
  - `sort_order` (string: `asc` hoặc `desc`): Thứ tự sắp xếp.
  - `limit` (integer, 1 - 100): Số bản ghi trên mỗi trang (mặc định: `10`).

- **Response thành công (200 OK)**:
  ```json
  {
    "data": [
      {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "nguyenvana@example.com",
        "loyalty_points": 120,
        "status": "active",
        "created_at": "2026-07-06T15:00:00+07:00",
        "updated_at": "2026-07-06T15:00:00+07:00"
      }
    ],
    "links": {
      "first": "http://localhost/api/customers?page=1",
      "last": "http://localhost/api/customers?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://localhost/api/customers",
      "per_page": 10,
      "to": 1,
      "total": 1
    },
    "success": true
  }
  ```

---

### 2.2. Thống kê khách hàng
- **Endpoint**: `GET /api/customers/stats`
- **Mô tả**: Trả về tổng số lượng khách hàng dựa trên bộ lọc truyền vào.
- **Tham số Query (Query Parameters)**: Tương tự như API danh sách (`search`, `status`, `from_date`, `to_date`).
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "total": 150
    }
  }
  ```

---

### 2.3. Xem chi tiết 1 khách hàng
- **Endpoint**: `GET /api/customers/{customer}`
- **Mô tả**: Trả về thông tin chi tiết của một khách hàng theo ID.
- **Tham số URL**: `{customer}` - ID của khách hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "name": "Nguyễn Văn A",
      "email": "nguyenvana@example.com",
      "loyalty_points": 120,
      "status": "active",
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

### 2.4. Tạo khách hàng mới
- **Endpoint**: `POST /api/customers/`
- **Mô tả**: Thêm mới một khách hàng vào hệ thống.
- **Body Parameters (JSON)**:
  - `name` (Bắt buộc, string, tối đa 255): Tên khách hàng.
  - `email` (Bắt buộc, email, tối đa 255): Địa chỉ email (phải là duy nhất).
  - `loyalty_points` (Không bắt buộc, integer, >= 0): Điểm tích lũy ban đầu (mặc định: 0).
- **Response thành công (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Khách hàng đã được tạo thành công!",
    "data": {
      "id": 2,
      "name": "Nguyễn Văn B",
      "email": "nguyenvanb@example.com",
      "loyalty_points": 0,
      "status": "active",
      "created_at": "2026-07-06T22:00:00+07:00",
      "updated_at": "2026-07-06T22:00:00+07:00"
    }
  }
  ```
- **Response lỗi Validation (422 Unprocessable Entity)**:
  ```json
  {
    "success": false,
    "message": "Email này đã tồn tại trong hệ thống.",
    "errors": {
      "email": [
        "Email này đã tồn tại trong hệ thống."
      ]
    },
    "code": "VALIDATION_ERROR"
  }
  ```

---

### 2.5. Cập nhật thông tin khách hàng
- **Endpoint**: `PUT /api/customers/{customer}` hoặc `PATCH /api/customers/{customer}`
- **Mô tả**: Cập nhật thông tin của khách hàng hiện tại (hỗ trợ cập nhật một phần qua PATCH).
- **Tham số URL**: `{customer}` - ID của khách hàng.
- **Body Parameters (JSON)**:
  - `name` (Không bắt buộc, string, tối đa 255): Tên khách hàng.
  - `email` (Không bắt buộc, email, tối đa 255): Email mới (bỏ qua kiểm tra trùng lặp với chính nó).
  - `loyalty_points` (Không bắt buộc, integer, >= 0): Điểm tích lũy.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật thông tin thành công!",
    "data": {
      "id": 1,
      "name": "Nguyễn Văn A Sửa",
      "email": "nguyenvana_edit@example.com",
      "loyalty_points": 150,
      "status": "active",
      "created_at": "2026-07-06T15:00:00+07:00",
      "updated_at": "2026-07-06T22:05:00+07:00"
    }
  }
  ```

---

### 2.6. Xóa khách hàng
- **Endpoint**: `DELETE /api/customers/{customer}`
- **Mô tả**: Xóa hoàn toàn một khách hàng khỏi cơ sở dữ liệu.
- **Tham số URL**: `{customer}` - ID của khách hàng.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Khách hàng đã được xóa!"
  }
  ```

---

### 2.7. Xóa hàng loạt khách hàng
- **Endpoint**: `POST /api/customers/bulk-delete`
- **Mô tả**: Xóa nhiều khách hàng cùng lúc bằng danh sách ID.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID khách hàng cần xóa.
  - `ids.*` (Bắt buộc, integer, exists:customers,id): ID phải hợp lệ và tồn tại.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Đã xóa thành công các khách hàng được chọn!"
  }
  ```

---

### 2.8. Cập nhật trạng thái hàng loạt
- **Endpoint**: `POST /api/customers/bulk-status`
- **Mô tả**: Cập nhật trạng thái hoạt động của nhiều khách hàng cùng lúc.
- **Body Parameters (JSON)**:
  - `ids` (Bắt buộc, array): Mảng chứa các ID khách hàng cần cập nhật.
  - `ids.*` (Bắt buộc, integer, exists:customers,id): ID khách hàng hợp lệ.
  - `status` (Bắt buộc, string, in:active,inactive): Trạng thái muốn chuyển sang.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Cập nhật trạng thái hàng loạt thành công!"
  }
  ```

---

### 2.9. Xuất danh sách khách hàng (Excel)
- **Endpoint**: `GET /api/customers/export`
- **Mô tả**: Xuất danh sách khách hàng ra file Excel (.xlsx) dựa theo bộ lọc.
- **Tham số Query (Query Parameters)**: Tương tự như bộ lọc danh sách (`search`, `status`, `from_date`, `to_date`).
- **Response**: Trả về file nhị phân tải xuống trực tiếp (`customers.xlsx`).

---

### 2.10. Nhập danh sách khách hàng từ Excel
- **Endpoint**: `POST /api/customers/import`
- **Mô tả**: Nhập dữ liệu khách hàng từ file bảng tính.
- **Headers**: `Content-Type: multipart/form-data`
- **Body Parameters (Form Data)**:
  - `file` (Bắt buộc, file): Định dạng file `.xlsx`, `.xls` hoặc `.csv`.
- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Import dữ liệu khách hàng thành công."
  }
  ```
