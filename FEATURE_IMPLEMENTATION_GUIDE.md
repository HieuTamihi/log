# Hướng dẫn triển khai các tính năng mới

## ✅ Đã hoàn thành Backend:
1. Migration cho `note_attachments` table
2. Migration cho `note_tabs` table  
3. Model `NoteAttachment` và `NoteTab`
4. Routes cho upload/delete attachments và CRUD tabs
5. Controller methods trong `NoteController`
6. Storage link đã được tạo

## 📝 Cần cập nhật Frontend:

### 1. Thêm ảnh vào note
- Thêm nút upload ảnh trong markdown toolbar
- Hiển thị danh sách ảnh đã upload
- Cho phép insert ảnh vào markdown content
- Preview ảnh khi hover

### 2. Hiển thị tình trạng note ngoài folder  
- ✅ Đã có trong sidebar tree
- Cần thêm status indicator trong folder view

### 3. Thêm các tab của note
- Thêm tab navigation ở đầu note panel
- Mỗi tab có content riêng
- Cho phép tạo/xóa/đổi tên tab
- Drag & drop để sắp xếp thứ tự tab

### 4. Link note trong nội dung note
- Detect pattern [[Note Name]] trong markdown
- Tự động convert thành clickable link
- Click vào link sẽ mở note đó

### 5. Hiển thị button option khi click vào card
- Ẩn buttons View/Edit/Delete mặc định
- Chỉ hiện khi click vào card
- Thêm animation fade in/out

### 6. Nơi lưu trữ ảnh và templates
- ✅ Storage đã được config tại `storage/app/public/attachments`
- Có thể truy cập qua `/storage/attachments/filename`
- Tạo thư mục templates: `storage/app/public/templates`

## 🔧 Code mẫu cho Frontend

### Upload ảnh button (thêm vào markdown toolbar):
```javascript
<button type="button" onclick="uploadImage()" class="md-btn" title="Upload Image">
    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;">
        <path d="M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M8.5,13.5L11,16.5L14.5,12L19,18H5L8.5,13.5Z"/>
    </svg>
</button>
<input type="file" id="image-upload" accept="image/*" style="display: none;" onchange="handleImageUpload(event)">
```

### Tab navigation (thêm vào note panel):
```javascript
<div class="tab-navigation" style="display: flex; gap: 4px; padding: 8px; border-bottom: 1px solid #e5e5e5; overflow-x: auto;">
    <button class="tab-btn active" data-tab="main">Main</button>
    ${note.tabs.map(tab => `
        <button class="tab-btn" data-tab="${tab.id}">${tab.name}</button>
    `).join('')}
    <button class="tab-btn-add" onclick="createNewTab(${note.id})">+</button>
</div>
```

### Link note detection:
```javascript
function processNoteLinks(content) {
    // Replace [[Note Name]] with clickable links
    return content.replace(/\[\[([^\]]+)\]\]/g, (match, noteName) => {
        return `<a href="#" onclick="openNoteByName('${noteName}'); return false;" class="note-link">${noteName}</a>`;
    });
}
```

### Card hover buttons:
```css
.card-actions {
    opacity: 0;
    transition: opacity 0.2s;
}

.card:hover .card-actions {
    opacity: 1;
}
```

## 📂 Cấu trúc thư mục storage:
```
storage/
  app/
    public/
      attachments/     # Ảnh và files của notes
      templates/       # Templates cho notes
```

## 🚀 Các bước tiếp theo:
1. Cập nhật `openNote()` function để load tabs và attachments
2. Thêm UI components cho tabs
3. Thêm upload image functionality
4. Implement note linking trong markdown
5. Thêm hover effect cho card buttons
6. Tạo thư mục templates và seed một số templates mẫu
