# Thay đổi giao diện - Cleanup

## Tóm tắt
Đã thực hiện các thay đổi sau để đơn giản hóa giao diện:

### 1. Xóa phần "Cải tiến tiếp theo" (Next Upgrades)
- **File**: `resources/views/machines/index.blade.php`
- **Thay đổi**: Xóa toàn bộ section hiển thị danh sách các upgrades đang chờ
- **Lý do**: Đơn giản hóa giao diện, tập trung vào machines

### 2. Xóa nút "Tạo cải tiến" (Ship an Upgrade)
- **File**: `resources/views/machines/index.blade.php`
- **Thay đổi**: 
  - Xóa nút "Ship an Upgrade" và dropdown menu
  - Xóa JavaScript function `toggleQuickShip()`
  - Xóa các references đến `quickShipDropdown`
- **Lý do**: Đơn giản hóa workflow, người dùng có thể tạo upgrade từ machine detail

### 3. Chỉ giữ tiếng Anh
- **Files**: 
  - `resources/views/components/navbar.blade.php`
  - `app/Http/Middleware/SetLocale.php`
- **Thay đổi**:
  - Navbar: Hiển thị cố định "🇬🇧 EN" (không thể click)
  - Middleware: Force locale = 'en' cho tất cả requests
  - Config: Đã có default locale = 'en'
- **Lý do**: Đơn giản hóa, chỉ support một ngôn ngữ

## Kết quả
Giao diện trang chủ giờ đây:
- ✅ Sạch sẽ hơn, tập trung vào machines
- ✅ Có tính năng zoom in/zoom out
- ✅ Chỉ hiển thị tiếng Anh
- ✅ Không có các nút/section không cần thiết

## Các tính năng còn lại
- Zoom controls (Overview/Detail)
- Machine cards với health status
- Floating action button để tạo machine mới
- Search, notifications, user menu
