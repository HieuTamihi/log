# Business Machine System - Hướng Dẫn Sử Dụng

## Tổng Quan

Hệ thống Business Machine được thiết kế linh hoạt với 4 cấp độ phân cấp:

1. **Machine** (Máy) - Cấp cao nhất (ví dụ: Demand, Sales, Delivery)
2. **Subsystem** (Hệ thống con) - Thuộc về Machine (ví dụ: Content Engine, Distribution Engine)
3. **Component** (Thành phần) - Thuộc về Subsystem (ví dụ: Hooks, Scripts, Filming)
4. **Upgrade** (Nâng cấp) - Thuộc về Component (các bước cụ thể để cải thiện)

## Tính Năng Chính

### 1. Quản Lý Machine (Máy)

**Tạo Machine mới:**
- Truy cập Dashboard → Click "Create Machine"
- Điền thông tin:
  - Name: Tên máy (ví dụ: "Demand")
  - Description: Mô tả chức năng
  - Icon: Emoji đại diện (ví dụ: "⚡")
  - Color: Màu chủ đạo
  - Order: Thứ tự hiển thị

**Chỉnh sửa Machine:**
- Vào trang Machine → Click "Edit Machine"
- Cập nhật thông tin và lưu

**Xóa Machine:**
- Vào trang Edit Machine → Click "Delete Machine"
- ⚠️ Lưu ý: Xóa Machine sẽ xóa tất cả Subsystems và Components bên trong

### 2. Quản Lý Subsystem (Hệ Thống Con)

**Tạo Subsystem mới:**
- Vào trang Machine → Click "Create Subsystem"
- Điền thông tin tương tự Machine

**Chỉnh sửa Subsystem:**
- Vào trang Subsystem → Click "Edit Subsystem"

**Xóa Subsystem:**
- Vào trang Edit Subsystem → Click "Delete Subsystem"
- ⚠️ Lưu ý: Xóa Subsystem sẽ xóa tất cả Components bên trong

### 3. Quản Lý Component (Thành Phần)

**Tạo Component mới:**
- Vào trang Subsystem → Click "Create Component"
- Điền thông tin:
  - Name: Tên component
  - Description: Mô tả
  - Icon: Emoji
  - Health Status: Trạng thái (Smooth/Needs Love/On Fire)
  - Current Issue: Vấn đề hiện tại (nếu có)
  - Metric Value & Label: Số liệu đo lường (ví dụ: "5 Hooks")

**Chỉnh sửa Component:**
- Vào trang Subsystem → Click "Edit Component" trên card component

**Xóa Component:**
- Vào trang Edit Component → Click "Delete Component"
- ⚠️ Lưu ý: Xóa Component sẽ xóa tất cả Upgrades bên trong

### 4. Quản Lý Upgrade (Nâng Cấp)

**Tạo Upgrade mới:**
- Vào Component → Click "Ship Upgrade"
- Điền thông tin:
  - Name: Tên upgrade
  - Purpose: Mục đích
  - Trigger: Khi nào sử dụng
  - Steps: Các bước thực hiện
  - Definition of Done: Tiêu chí hoàn thành

## Cấu Trúc Database

```
machines
├── id
├── name
├── slug
├── description
├── icon
├── color
└── order

subsystems
├── id
├── machine_id (FK)
├── name
├── slug
├── description
├── icon
├── color
└── order

components
├── id
├── subsystem_id (FK)
├── name
├── slug
├── description
├── icon
├── health_status (smooth/on_fire/needs_love)
├── current_issue
├── metric_value
├── metric_label
└── order

upgrades
├── id
├── component_id (FK)
├── user_id (FK)
├── name
├── purpose
├── trigger
├── steps (JSON)
├── definition_of_done
├── status (draft/active/archived)
└── shipped_at
```

## Routes API

### Machine Management
- `GET /manage/machines` - Danh sách machines
- `GET /manage/machines/create` - Form tạo machine
- `POST /manage/machines` - Lưu machine mới
- `GET /manage/machines/{machine}/edit` - Form chỉnh sửa
- `PUT /manage/machines/{machine}` - Cập nhật machine
- `DELETE /manage/machines/{machine}` - Xóa machine

### Subsystem Management
- `GET /manage/machines/{machine}/subsystems/create` - Form tạo subsystem
- `POST /manage/machines/{machine}/subsystems` - Lưu subsystem mới
- `GET /manage/subsystems/{subsystem}/edit` - Form chỉnh sửa
- `PUT /manage/subsystems/{subsystem}` - Cập nhật subsystem
- `DELETE /manage/subsystems/{subsystem}` - Xóa subsystem

### Component Management
- `GET /manage/subsystems/{subsystem}/components/create` - Form tạo component
- `POST /manage/subsystems/{subsystem}/components` - Lưu component mới
- `GET /manage/components/{component}/edit` - Form chỉnh sửa
- `PUT /manage/components/{component}` - Cập nhật component
- `DELETE /manage/components/{component}` - Xóa component

## Ví Dụ Sử Dụng

### Tạo một Business Machine hoàn chỉnh:

1. **Tạo Machine "Demand"**
   - Name: Demand
   - Description: Creates leads
   - Icon: 🎯
   - Color: #60a5fa

2. **Tạo Subsystem "Content Engine"**
   - Name: Content Engine
   - Description: Creates videos that generate leads
   - Icon: 📝

3. **Tạo Component "Hooks"**
   - Name: Hooks
   - Description: Creates content
   - Health Status: Needs Love
   - Current Issue: Hooks feel stale
   - Metric: 5 Hooks

4. **Tạo Upgrade "Rewrite Hooks"**
   - Name: Hook Writing Upgrade
   - Purpose: Improve hook quality
   - Steps:
     1. Brainstorm openers
     2. Choose the best hook
     3. Write a compelling headline
   - Definition of Done: Success looks like... Less thinking later

## Mở Rộng Trong Tương Lai

Hệ thống được thiết kế để dễ dàng mở rộng:

- ✅ Có thể tạo không giới hạn Machines
- ✅ Mỗi Machine có thể có nhiều Subsystems
- ✅ Mỗi Subsystem có thể có nhiều Components
- ✅ Mỗi Component có thể có nhiều Upgrades
- ✅ Tất cả đều có thể tạo, sửa, xóa động qua giao diện

## Translation Keys

Tất cả các message đã được thêm vào:
- `lang/en/messages.php` (Tiếng Anh)
- `lang/vi/messages.php` (Tiếng Việt)

Bạn có thể dễ dàng thêm ngôn ngữ mới bằng cách tạo folder mới trong `lang/`.
