# 📚 Resource Library - Hướng dẫn sử dụng

## Tổng quan
Resource Library là nơi lưu trữ tập trung cho tất cả ảnh, files, và templates trong hệ thống. Bạn có thể upload một lần và sử dụng lại nhiều lần trong các notes khác nhau.

## ✅ Đã hoàn thành

### Backend (100%)
1. ✅ Database table `resources` với đầy đủ fields
2. ✅ Model `Resource` với relationships và helpers
3. ✅ Controller `ResourceController` với CRUD operations
4. ✅ Routes đầy đủ cho API
5. ✅ Storage folder `storage/app/public/resources`
6. ✅ File tracking (download count, last accessed)

### Frontend (100%)
1. ✅ Nút "Resource Library" trong sidebar
2. ✅ Modal hiển thị resources dạng grid
3. ✅ Upload multiple files
4. ✅ Filter theo type và category
5. ✅ Search resources
6. ✅ Preview ảnh
7. ✅ Copy link, download, delete
8. ✅ Insert ảnh vào note

## 🎯 Tính năng

### 1. Upload Resources
- Click nút "Resource Library" (icon 📚) trong sidebar
- Click nút "Upload"
- Chọn một hoặc nhiều files (max 50MB/file)
- Nhập category (optional)
- Files sẽ được upload và hiển thị ngay

### 2. Quản lý Resources
**Các loại file được hỗ trợ:**
- 🖼️ Images: jpg, png, gif, svg, webp
- 📄 Documents: pdf, doc, docx, xls, xlsx, txt, md
- 🎥 Videos: mp4, avi, mov, wmv
- 🎵 Audio: mp3, wav, ogg
- 📎 Other: zip, rar, etc.

**Thông tin được lưu:**
- Tên file và mô tả
- Loại file (type) và category
- Kích thước file
- Người upload
- Số lần download
- Lần truy cập cuối

### 3. Sử dụng Resources trong Notes

#### Cách 1: Insert trực tiếp (cho ảnh)
1. Mở Resource Library
2. Click vào ảnh muốn insert
3. Chọn "1. Insert as Image"
4. Ảnh sẽ được thêm vào note đang mở

#### Cách 2: Copy link
1. Click vào resource
2. Chọn "2. Copy Link"
3. Paste link vào note:
   - Ảnh: `![Alt text](link)`
   - File: `[File name](link)`

#### Cách 3: Link trong markdown
```markdown
# Ví dụ link ảnh
![Logo](http://127.0.0.1:8081/storage/resources/1234567890_abc.png)

# Ví dụ link file
[Download Template](http://127.0.0.1:8081/storage/resources/template.pdf)

# Ví dụ link với download
[Meeting Notes Template](/api/resources/1/download)
```

### 4. Filter và Search
- **Search box**: Tìm theo tên hoặc mô tả
- **Type filter**: Lọc theo loại (Images, Documents, Videos, Audio, Other)
- **Category filter**: Lọc theo category (general, templates, images, documents, etc.)

### 5. Actions cho mỗi Resource
Khi click vào resource, bạn có các options:

**Cho ảnh:**
1. Insert as Image - Thêm vào note đang mở
2. Copy Link - Copy URL vào clipboard
3. Download - Tải file về máy
4. Delete - Xóa resource

**Cho files khác:**
1. Copy Link - Copy URL vào clipboard
2. Download - Tải file về máy
3. View Details - Xem thông tin chi tiết
4. Delete - Xóa resource

## 📂 Cấu trúc Storage

```
storage/
  app/
    public/
      resources/           # Resource Library files
        1234567890_abc.png
        1234567891_xyz.pdf
        meeting-notes-template.md
        project-plan-template.md
      attachments/         # Note-specific attachments
        ...
```

## 🔗 API Endpoints

```
GET    /api/resources                    - List all resources
POST   /api/resources                    - Upload new resource
GET    /api/resources/categories         - Get all categories
GET    /api/resources/{id}               - Get resource details
GET    /api/resources/{id}/download      - Download resource
PUT    /api/resources/{id}               - Update resource info
DELETE /api/resources/{id}               - Delete resource
```

## 💡 Use Cases

### 1. Logo và Brand Assets
Upload logo công ty một lần, sử dụng trong nhiều notes:
```markdown
![Company Logo](/storage/resources/logo.png)
```

### 2. Templates
Lưu các templates thường dùng:
- Meeting notes template
- Project plan template
- Report template
- Email template

### 3. Shared Documents
Lưu tài liệu chung để nhiều notes có thể reference:
```markdown
Xem thêm: [Company Handbook](/api/resources/5/download)
```

### 4. Screenshots và Diagrams
Upload một lần, dùng nhiều nơi:
```markdown
## Architecture
![System Architecture](/storage/resources/architecture-diagram.png)

## Flow
![User Flow](/storage/resources/user-flow.png)
```

## 🎨 Templates có sẵn

Hệ thống đã có sẵn 2 templates:
1. **meeting-notes-template.md** - Template cho meeting notes
2. **project-plan-template.md** - Template cho project planning

Bạn có thể download và sử dụng làm base cho notes mới.

## 🔒 Permissions
- Tất cả users có thể xem và download resources
- Chỉ người upload có thể delete resource của mình
- Admin có thể delete bất kỳ resource nào

## 📊 Statistics
Mỗi resource track:
- **download_count**: Số lần được download
- **last_accessed_at**: Lần cuối được truy cập

Giúp bạn biết resources nào được sử dụng nhiều nhất.

## 🚀 Tips & Tricks

1. **Đặt tên có ý nghĩa**: Dùng tên mô tả rõ ràng thay vì "image1.png"
2. **Sử dụng categories**: Phân loại resources để dễ tìm kiếm
3. **Thêm description**: Giúp người khác hiểu resource là gì
4. **Optimize images**: Nén ảnh trước khi upload để tiết kiệm storage
5. **Reuse resources**: Thay vì upload lại, tìm trong library trước

## 🐛 Troubleshooting

**Không upload được file:**
- Check file size (max 50MB)
- Check file type có được hỗ trợ không
- Check storage space

**Không thấy ảnh trong note:**
- Check URL có đúng không
- Check file còn tồn tại trong storage không
- Check permissions

**Link bị broken:**
- Resource có thể đã bị xóa
- Check URL format: `/storage/resources/filename` hoặc `/api/resources/id/download`
