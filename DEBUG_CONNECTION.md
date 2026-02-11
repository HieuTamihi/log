# Debug Connection Feature

## Các bước kiểm tra:

### 1. Mở Browser Console (F12)
- Nhấn F12 để mở Developer Tools
- Chuyển sang tab "Console"
- Refresh trang

### 2. Kiểm tra logs khi load trang:
Bạn sẽ thấy:
```
Initializing connections...
Canvas resized: [width] x [height]
Connections initialized. Total connections: 0
```

### 3. Click nút "Connect Machines"
Bạn sẽ thấy:
```
Connection mode: true
Enabling connection mode...
Found [X] machine cards
```

Nếu thấy "Found 0 machine cards" → Bạn chưa có machines trong database!

### 4. Click vào một machine card
Bạn sẽ thấy:
```
Card clicked: [machine_id]
Starting connection from: [machine_id]
```

### 5. Click vào machine card thứ hai
Bạn sẽ thấy:
```
Card clicked: [machine_id_2]
Creating connection: [id1] -> [id2]
Sending connection request: [id1] -> [id2]
Connection created: {object}
Drawing 1 connections
```

## Các lỗi thường gặp:

### Lỗi 1: "Canvas not found!"
**Nguyên nhân**: Không có machines trong database
**Giải pháp**: Tạo ít nhất 2 machines trước

### Lỗi 2: "Found 0 machine cards"
**Nguyên nhân**: Không có machines hoặc selector sai
**Giải pháp**: Kiểm tra xem có machines trong view không

### Lỗi 3: "Failed to create connection: 404"
**Nguyên nhân**: Route không tồn tại
**Giải pháp**: Chạy `php artisan route:list | grep connection`

### Lỗi 4: "Failed to create connection: 500"
**Nguyên nhân**: Lỗi server (có thể thiếu CSRF token)
**Giải pháp**: Kiểm tra Laravel logs

## Cách tạo machines để test:

1. Click nút FAB (floating action button) ở góc dưới phải
2. Tạo ít nhất 2 machines
3. Quay lại dashboard
4. Thử lại tính năng connection

## Kiểm tra database:

```sql
-- Kiểm tra có machines không
SELECT * FROM machines;

-- Kiểm tra connections
SELECT * FROM machine_connections;
```

## Test manual qua API:

```bash
# Tạo connection
curl -X POST http://localhost/machine-connections \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: [your-token]" \
  -d '{"from_machine_id": 1, "to_machine_id": 2}'

# Lấy danh sách connections
curl http://localhost/machine-connections
```
