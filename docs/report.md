# Tài liệu API - Module Reports (Báo cáo)

Module Reports cung cấp các API phân tích và tổng hợp số liệu kinh doanh như doanh thu, khách hàng thân thiết và tỷ lệ hủy đơn.

---

## 1. Thông tin chung
- **Prefix URL**: `/api/reports`
- **Định dạng dữ liệu**: `JSON`

---

## 2. Danh sách API

### 2.1. Báo cáo doanh thu và phân tích bán hàng
- **Endpoint**: `GET /api/reports/revenue`
- **Mô tả**: Tổng hợp số liệu doanh thu của từng sản phẩm (chỉ tính các đơn hàng có trạng thái `completed`), danh sách top 5 khách hàng chi tiêu nhiều nhất và tỷ lệ hủy đơn hàng trong tháng hiện tại.
- **Tham số Query (Query Parameters)**: Không yêu cầu.

- **Response thành công (200 OK)**:
  ```json
  {
    "success": true,
    "message": "Báo cáo doanh thu đã được tạo thành công.",
    "data": {
      "revenue_by_product": [
        {
          "id": 1,
          "name": "Laptop Dell XPS 15 2024",
          "order_items_sum_quantity_price": "70000000.00"
        },
        {
          "id": 3,
          "name": "Bàn Phím Cơ Keychron K2",
          "order_items_sum_quantity_price": "1200000.00"
        }
      ],
      "top_5_customers": [
        {
          "id": 1,
          "name": "Nguyễn Văn A",
          "email": "nguyenvana@example.com",
          "orders_sum_total_amount": "71200000.00"
        }
      ],
      "cancellation_rate_this_month": 16.67
    }
  }
  ```
  
  - **Ý nghĩa các trường trong `data`**:
    - `revenue_by_product`: Danh sách các sản phẩm và tổng doanh thu tương ứng được sắp xếp giảm dần theo doanh thu. Doanh thu chỉ được tính từ các đơn hàng có trạng thái là `completed`.
    - `top_5_customers`: Top 5 khách hàng chi tiêu nhiều nhất trong hệ thống, sắp xếp giảm dần theo tổng giá trị các đơn hàng `completed`.
    - `cancellation_rate_this_month`: Tỷ lệ đơn hàng bị hủy (`status = cancelled`) so với tổng số đơn hàng được tạo trong tháng hiện tại (đơn vị: `%`, làm tròn 2 chữ số thập phân).
