<?php
include 'db.php';  // Đảm bảo file db.php có session_start();

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Mật khẩu không đúng!";
            }
        } else {
            $error = "Tên đăng nhập không tồn tại!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="style.css">

    <!-- === CẤU HÌNH PWA CHÈN TẠI ĐÂY === -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Fluency Log">
    <link rel="icon" type="image/png" href="icon-192.png">
    <link rel="apple-touch-icon" href="icon-192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="icon-192.png">
    <link rel="apple-touch-icon" sizes="167x167" href="icon-192.png">
    <!-- ================================ -->
</head>

<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Đăng Nhập</h1>
            <p class="auth-subtitle">
                Đăng nhập để quản lý Vấn Đề & Giải Pháp
            </p>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert success"><?php echo $_SESSION['success_message'];
                                            unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Tên đăng nhập" required autofocus>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <button type="submit" name="login" class="btn auth-btn">Đăng Nhập</button>
            </form>

            <p class="auth-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
    </div>

    <!-- === ĐĂNG KÝ SERVICE WORKER === -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('PWA Service Worker đã chạy!', reg))
                    .catch(err => console.log('Lỗi PWA:', err));
            });
        }
    </script>
    <!-- ============================== -->
</body>

</html>