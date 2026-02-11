# Tính năng Zoom In/Zoom Out

## Mô tả
Đã thêm tính năng zoom in/zoom out cho trang chủ dashboard, cho phép người dùng:
- **Zoom Out (Toàn cảnh)**: Xem tổng quan tất cả các machines với kích thước nhỏ hơn, giúp nhìn thấy toàn bộ hệ thống cùng lúc
- **Zoom In (Chi tiết)**: Xem chi tiết từng machine với kích thước đầy đủ

## Cách sử dụng
1. Truy cập trang chủ (/)
2. Nhấn nút "Toàn cảnh" để zoom out và xem tổng quan
3. Nhấn nút "Chi tiết" để zoom in và xem chi tiết

## Thay đổi kỹ thuật

### Files đã sửa đổi:
1. **resources/views/machines/index.blade.php**
   - Thêm nút zoom toggle
   - Thêm JavaScript để xử lý zoom
   - Thêm CSS cho zoom effects

2. **lang/vi/messages.php** & **lang/en/messages.php**
   - Thêm translations: 'overview' và 'detail'

### Cách hoạt động:
- Khi zoom out, áp dụng CSS transform scale(0.7) cho toàn bộ machine flow
- Cards được thu nhỏ và có thể hover để phóng to
- Smooth transition với cubic-bezier animation
- Responsive trên mobile

## Demo
- Zoom Out: Tất cả machines hiển thị nhỏ hơn, dễ nhìn toàn cảnh
- Zoom In: Machines hiển thị kích thước bình thường với đầy đủ thông tin
