<?php
include 'db.php';  // Đảm bảo file db.php có session_start();

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        // Prepared statement
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Kiểm tra mật khẩu bằng password_verify
            if (password_verify($password, $user['password'])) {
                // Đăng nhập thành công: bảo mật session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Handle Remember Me
                if (isset($_POST['remember'])) {
                    // Generate secure random token
                    $token = bin2hex(random_bytes(32));

                    // Store in DB
                    $updateStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $token, $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();

                    // Set cookie for 30 days
                    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true); // HTTPOnly
                }

                // Chuyển hướng về index
                header("Location: index.php");
                exit();  // Quan trọng: dừng script ngay sau header
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Fluency">

    <title>Đăng Nhập - Leverage Fluency</title>

    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <link rel="icon" type="image/png" href="icons/icon-192.png">
    <link rel="stylesheet" href="style.css">
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

                <div style="display: flex; align-items: center; margin-bottom: 16px; justify-content: flex-start;">
                    <input type="checkbox" name="remember" id="remember"
                        style="width: auto; margin-right: 8px; margin-bottom: 0;">
                    <label for="remember"
                        style="margin-bottom: 0; cursor: pointer; color: var(--text-secondary); text-transform: none;">Ghi
                        nhớ đăng nhập</label>
                </div>

                <button type="submit" name="login" class="btn auth-btn">Đăng Nhập</button>
            </form>

            <p class="auth-link">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</body>

</html>