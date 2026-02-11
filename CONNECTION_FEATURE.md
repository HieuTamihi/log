# Tính năng Kéo Mũi Tên Nối Machines

## Mô tả
Đã thêm tính năng interactive để tạo connections (mũi tên) giữa các machines theo ý người dùng.

## Cách sử dụng

### 1. Bật Connection Mode
- Click nút **"Connect Machines"** 
- Nút sẽ chuyển sang màu gradient (active state)
- Các machine cards sẽ có cursor crosshair

### 2. Tạo Connection
1. Click vào machine đầu tiên (source)
   - Card sẽ có border xanh highlight
2. Click vào machine thứ hai (target)
   - Mũi tên sẽ được vẽ từ machine 1 → machine 2
   - Connection được lưu vào database

### 3. Hủy Connection đang tạo
- Click lại vào machine đang được chọn (có border xanh)
- Hoặc click nút "Connect Machines" để tắt mode

### 4. Tắt Connection Mode
- Click lại nút "Connect Machines"
- Các connections đã tạo vẫn được giữ nguyên

## Tính năng

### Visual
- ✅ Mũi tên nét đứt (dashed line) với arrow head
- ✅ Màu gradient mặc định (#6366f1)
- ✅ Tự động vẽ từ cạnh phải machine source → cạnh trái machine target
- ✅ Responsive với zoom - connections tự động redraw khi zoom

### Database
- ✅ Lưu connections vào table `machine_connections`
- ✅ Mỗi connection có:
  - `from_machine_id`: Machine nguồn
  - `to_machine_id`: Machine đích
  - `user_id`: Người tạo
  - `label`: Nhãn (optional)
  - `color`: Màu mũi tên (default: #6366f1)
- ✅ Unique constraint: Không cho phép duplicate connections
- ✅ Cascade delete: Xóa machine → xóa connections liên quan

### API Endpoints
```
GET  /machine-connections       - Lấy danh sách connections
POST /machine-connections       - Tạo connection mới
DELETE /machine-connections/{id} - Xóa connection
```

## Thay đổi kỹ thuật

### Database
- Migration: `create_machine_connections_table`
- Model: `MachineConnection`
- Relationships: belongsTo Machine (from/to), belongsTo User

### Backend
- Controller: `MachineConnectionController`
- Routes: RESTful API cho connections
- MachineController: Load connections khi render view

### Frontend
- Canvas API để vẽ mũi tên
- Event listeners cho click events
- AJAX calls để lưu connections
- Auto-redraw khi zoom/resize

## Cải tiến trong tương lai
- [ ] Click vào mũi tên để xóa connection
- [ ] Drag & drop để tạo connection (thay vì click-click)
- [ ] Thêm label text trên mũi tên
- [ ] Chọn màu cho mỗi connection
- [ ] Connection types (solid, dashed, dotted)
- [ ] Curved arrows thay vì straight lines
- [ ] Context menu để edit connection properties

## Demo Flow
1. Vào trang dashboard
2. Click "Connect Machines"
3. Click machine "Sales" → Click machine "Marketing"
4. Mũi tên xuất hiện: Sales → Marketing
5. Click "Connect Machines" để tắt mode
6. Zoom in/out → Mũi tên tự động scale
7. Refresh trang → Connections vẫn còn (đã lưu DB)
