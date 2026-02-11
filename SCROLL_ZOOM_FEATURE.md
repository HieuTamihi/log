# Tính năng Scroll Zoom với Subsystems

## Mô tả
Đã nâng cấp tính năng zoom với các cải tiến:

### 1. Zoom bằng Scroll Chuột
- **Cách dùng**: Giữ `Ctrl` (Windows) hoặc `Cmd` (Mac) + Scroll chuột
- **Phạm vi**: 50% - 150% (có thể zoom in và zoom out)
- **Mượt mà**: Zoom từng bước 10%, có animation mượt
- **Indicator**: Hiển thị % zoom khi đang zoom (tự động ẩn sau 1 giây)

### 2. Hiển thị Subsystems khi Zoom Out
Khi zoom < 80%:
- ✅ Hiển thị danh sách subsystems bên dưới mỗi machine
- ✅ Mỗi subsystem có:
  - Icon
  - Tên
  - Health status (dot màu)
  - Link đến subsystem detail
- ✅ Có đường line kết nối từ machine đến subsystems
- ✅ Hover effect để dễ nhận biết

### 3. Tự động ẩn/hiện nội dung
- **Zoom In (>80%)**: Hiển thị metrics, ẩn subsystems
- **Zoom Out (<80%)**: Hiển thị subsystems, ẩn metrics

## Cách sử dụng

### Phương pháp 1: Nút Toggle
- Click nút "Overview" để zoom out nhanh (60%)
- Click nút "Detail" để zoom in về 100%

### Phương pháp 2: Scroll Zoom (Khuyến nghị)
1. Di chuột vào khu vực machines
2. Giữ `Ctrl` (hoặc `Cmd` trên Mac)
3. Scroll lên để zoom in
4. Scroll xuống để zoom out
5. Thả `Ctrl`/`Cmd` để scroll bình thường

## Thay đổi kỹ thuật

### HTML
- Thêm subsystems container vào mỗi machine card
- Thêm zoom level indicator
- Thêm zoom hint (hướng dẫn)

### CSS
- Subsystems styling với connecting lines
- Dynamic transform scale
- Smooth transitions
- Health status dots với animation

### JavaScript
- Wheel event listener với Ctrl/Cmd detection
- Dynamic zoom level (0.5 - 1.5)
- Auto show/hide subsystems based on zoom level
- Zoom indicator với auto-hide

## UI/UX Improvements
- 🎯 Zoom hint để người dùng biết cách dùng
- 📊 Zoom indicator hiển thị % zoom hiện tại
- 🔗 Subsystems có visual connection đến machine
- ⚡ Smooth animations cho tất cả transitions
- 🎨 Color-coded health status
