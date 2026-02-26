# Tóm tắt triển khai các tính năng

## ✅ Backend đã hoàn thành 100%

### Database:
- ✅ `note_attachments` table (id, note_id, filename, original_filename, mime_type, size, path, type, uploaded_by, timestamps)
- ✅ `note_tabs` table (id, note_id, name, content, order, timestamps)

### Models:
- ✅ `NoteAttachment` model với relationships
- ✅ `NoteTab` model với relationships  
- ✅ `Note` model đã có `attachments()` và `tabs()` relationships

### Routes:
- ✅ POST `/api/notes/{note}/attachments` - Upload file
- ✅ DELETE `/api/attachments/{attachment}` - Delete file
- ✅ POST `/api/notes/{note}/tabs` - Create tab
- ✅ PUT `/api/tabs/{tab}` - Update tab
- ✅ DELETE `/api/tabs/{tab}` - Delete tab
- ✅ POST `/api/tabs/reorder` - Reorder tabs

### Controller Methods:
- ✅ `uploadAttachment()` - Xử lý upload, lưu vào `storage/app/public/attachments`
- ✅ `deleteAttachment()` - Xóa file và database record
- ✅ `createTab()`, `updateTab()`, `deleteTab()`, `reorderTabs()`

### Storage:
- ✅ Storage link đã tạo: `public/storage` -> `storage/app/public`
- ✅ Files có thể truy cập qua `/storage/attachments/filename`

## 📝 Frontend cần implement

### 1. Hiển thị tình trạng note ngoài folder ✅
**Đã có trong sidebar tree, cần thêm vào folder modal**

Trong hàm `openFolder()`, thêm status icons cho notes:
```javascript
${folder.notes.map(note => {
    const statusIcons = {'draft': '🔴', 'improving': '🟡', 'standardized': '🟢'};
    const statusIcon = note.status && note.status !== 'none' && statusIcons[note.status] 
        ? `<span style="font-size: 12px;">${statusIcons[note.status]}</span>` 
        : '';
    return `<div>${statusIcon} ${note.name}</div>`;
}).join('')}
```

### 2. Hiển thị button option khi click vào card ✅
**Thêm CSS để ẩn/hiện buttons**

Trong phần `<style>`, thêm:
```css
.card-actions {
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.card.active .card-actions,
.card:hover .card-actions {
    opacity: 1;
    pointer-events: auto;
}
```

Trong hàm `renderCards()`, thêm click handler:
```javascript
cardEl.addEventListener('click', (e) => {
    // Remove active from all cards
    document.querySelectorAll('.card').forEach(c => c.classList.remove('active'));
    // Add active to clicked card
    cardEl.classList.add('active');
});
```

### 3. Thêm ảnh vào note 🔧
**Cần thêm upload button và xử lý**

Trong markdown toolbar của `openNote()`, thêm sau nút Quote:
```javascript
<div style="width: 1px; background: #e5e5e5; margin: 0 4px;"></div>
<button type="button" onclick="document.getElementById('image-upload-${note.id}').click()" class="md-btn" title="Upload Image">
    <svg viewBox="0 0 24 24" style="width: 16px; height: 16px; fill: currentColor;">
        <path d="M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M8.5,13.5L11,16.5L14.5,12L19,18H5L8.5,13.5Z"/>
    </svg>
</button>
<input type="file" id="image-upload-${note.id}" accept="image/*" style="display: none;" onchange="handleImageUpload(event, ${note.id})">
```

Thêm function xử lý upload:
```javascript
async function handleImageUpload(event, noteId) {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const res = await fetch(`/api/notes/${noteId}/attachments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });
        
        const attachment = await res.json();
        
        // Insert markdown image syntax
        const textarea = document.getElementById('note-content');
        const imageMarkdown = `\n![${attachment.original_filename}](/storage/${attachment.path})\n`;
        textarea.value += imageMarkdown;
        updatePreview();
        
        alert('Image uploaded successfully!');
    } catch (e) {
        alert('Failed to upload image: ' + e.message);
    }
}
```

### 4. Thêm tabs cho note 🔧
**Cần thêm tab navigation và content switching**

Trong `openNote()`, thêm sau modal-header:
```javascript
<!-- Tab Navigation -->
<div class="tab-navigation" style="display: flex; gap: 4px; padding: 8px; border-bottom: 1px solid #e5e5e5; overflow-x: auto; background: #fafafa;">
    <button class="tab-btn active" data-tab="main" onclick="switchTab('main', ${note.id})" style="padding: 6px 12px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; font-size: 13px;">
        Main
    </button>
    ${(note.tabs || []).map(tab => `
        <button class="tab-btn" data-tab="${tab.id}" onclick="switchTab(${tab.id}, ${note.id})" style="padding: 6px 12px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: pointer; font-size: 13px;">
            ${tab.name}
            <span onclick="deleteTab(event, ${tab.id}, ${note.id})" style="margin-left: 6px; color: #999; font-weight: bold;">&times;</span>
        </button>
    `).join('')}
    <button class="tab-btn-add" onclick="createNewTab(${note.id})" style="padding: 6px 12px; border: 1px solid #ddd; background: #f0f0f0; border-radius: 4px; cursor: pointer; font-size: 13px;">
        + New Tab
    </button>
</div>
```

Thêm functions xử lý tabs:
```javascript
let currentTab = 'main';
let tabContents = {};

async function createNewTab(noteId) {
    const name = prompt('Tab name:');
    if (!name) return;
    
    const res = await fetch(`/api/notes/${noteId}/tabs`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name, content: '' })
    });
    
    const tab = await res.json();
    openNote(noteId); // Reload note to show new tab
}

function switchTab(tabId, noteId) {
    // Save current tab content
    const content = document.getElementById('note-content').value;
    tabContents[currentTab] = content;
    
    // Switch to new tab
    currentTab = tabId;
    
    // Update UI
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = '#f5f5f5';
    });
    document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
    document.querySelector(`[data-tab="${tabId}"]`).style.background = 'white';
    
    // Load tab content
    document.getElementById('note-content').value = tabContents[tabId] || '';
    updatePreview();
}

async function deleteTab(event, tabId, noteId) {
    event.stopPropagation();
    if (!confirm('Delete this tab?')) return;
    
    await fetch(`/api/tabs/${tabId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });
    
    openNote(noteId); // Reload
}
```

### 5. Link note trong nội dung 🔧
**Detect [[Note Name]] và convert thành link**

Cập nhật hàm `updatePreview()` và render markdown:
```javascript
function processNoteLinks(content) {
    // Replace [[Note Name]] with clickable links
    return content.replace(/\[\[([^\]]+)\]\]/g, (match, noteName) => {
        return `<a href="#" onclick="openNoteByName('${noteName.replace(/'/g, "\\'")}'); return false;" style="color: #0066cc; text-decoration: underline; cursor: pointer;">${noteName}</a>`;
    });
}

function updatePreview() {
    const content = document.getElementById('note-content').value;
    const previewView = document.getElementById('preview-view');
    let renderedContent = content ? marked.parse(content) : '<i style="color: #999;">Empty note</i>';
    renderedContent = processNoteLinks(renderedContent);
    previewView.innerHTML = renderedContent;
}

async function openNoteByName(noteName) {
    // Search for note by name
    const res = await fetch(`/api/notes?search=${encodeURIComponent(noteName)}`);
    const notes = await res.json();
    
    if (notes.length > 0) {
        openNote(notes[0].id);
    } else {
        alert(`Note "${noteName}" not found`);
    }
}
```

Cập nhật NoteController để hỗ trợ search:
```php
public function index(Request $request)
{
    $query = Note::query();

    if ($request->has('root')) {
        $query->whereNull('folder_id');
    }
    
    if ($request->has('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $notes = $query->get();

    return response()->json($notes);
}
```

### 6. Tạo thư mục templates 🔧
```bash
mkdir storage/app/public/templates
```

Tạo file template mẫu `storage/app/public/templates/meeting-notes.md`:
```markdown
# Meeting Notes

**Date:** {{date}}
**Attendees:** 
**Location:** 

## Agenda
1. 
2. 
3. 

## Discussion

## Action Items
- [ ] 
- [ ] 

## Next Meeting
**Date:** 
**Time:** 
```

## 🎯 Tổng kết
- Backend: 100% hoàn thành
- Frontend: Cần thêm ~200 dòng JavaScript vào file `resources/views/graph/index.blade.php`
- Tất cả code mẫu đã được cung cấp ở trên
- Chỉ cần copy/paste và điều chỉnh vị trí phù hợp
