# Log App - Electron Desktop Application

Ứng dụng desktop đóng gói từ PHP web app, có thể cài đặt trên Windows, macOS và Linux.

## 📋 Yêu Cầu

### Development
- Node.js >= 18.x
- PHP >= 7.4 (phải được thêm vào PATH)
- npm hoặc yarn

### Production (Windows)
- Có thể bundle PHP vào app hoặc yêu cầu người dùng cài PHP

## 🚀 Cài Đặt & Chạy

### 1. Cài đặt dependencies

```bash
cd electron
npm install
```

### 2. Chạy development mode

```bash
npm start
```

App sẽ khởi động với PHP server trên port 8080.

## 📦 Build Production

### Build cho Windows (Installer + Portable)

```bash
npm run build:win
```

Output sẽ nằm trong thư mục `dist/`:
- `Log App Setup x.x.x.exe` - NSIS installer
- `Log App x.x.x.exe` - Portable version

### Build cho macOS

```bash
npm run build:mac
```

### Build cho Linux

```bash
npm run build:linux
```

## 🎨 Tùy Chỉnh Icons

Thay thế các file trong thư mục `assets/`:
- `icon.ico` - Windows icon (256x256 pixels)
- `icon.icns` - macOS icon
- `icon.png` - Linux icon và general use (512x512 pixels recommended)

### Tạo icons từ PNG

Sử dụng các tool online như:
- https://cloudconvert.com/png-to-ico
- https://iconverticons.com/online/

## 📁 Cấu Trúc Thư Mục

```
electron/
├── assets/
│   ├── icon.ico      # Windows icon
│   ├── icon.icns     # macOS icon
│   └── icon.png      # Linux/general icon
├── main.js           # Main Electron process
├── splash.html       # Splash screen
├── package.json      # App configuration
└── README.md         # This file
```

## ⚙️ Configuration

Chỉnh sửa trong `package.json` > `build`:

```json
{
  "build": {
    "appId": "com.yourcompany.logapp",  // App ID
    "productName": "Log App",            // Tên hiển thị
    ...
  }
}
```

## 🔧 Bundling PHP (Optional)

Để đóng gói PHP cùng với app (không cần cài PHP trên máy người dùng):

1. Tải PHP Windows binaries từ: https://windows.php.net/download/
2. Giải nén vào `electron/php/`
3. App sẽ tự động sử dụng PHP bundled

Cấu trúc:
```
electron/
├── php/
│   ├── php.exe
│   ├── php.ini
│   └── ... (các file PHP khác)
```

## 🐛 Troubleshooting

### PHP không được tìm thấy
- Đảm bảo PHP đã được cài và thêm vào PATH
- Chạy `php -v` trong terminal để kiểm tra

### Port 8080 đã được sử dụng
- Thay đổi port trong `main.js`: `CONFIG.port = 8081`

### Build fails
- Xóa `node_modules` và `package-lock.json`, chạy lại `npm install`
- Kiểm tra version Node.js >= 18

## 📝 Features

- ✅ PHP server nhúng (built-in)
- ✅ Splash screen với animation
- ✅ System tray icon
- ✅ Custom menu bar
- ✅ Single instance lock (chỉ cho phép 1 cửa sổ)
- ✅ Zoom in/out support
- ✅ Full screen mode
- ✅ Cross-platform (Windows, macOS, Linux)
- ✅ NSIS Installer cho Windows
- ✅ Portable version
