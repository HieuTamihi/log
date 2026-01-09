<?php include 'db.php';
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    } else {
        // Kiểm tra username đã tồn tại
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Tên người dùng đã tồn tại!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $ins->bind_param("ss", $username, $hash);
            if ($ins->execute()) {
                $_SESSION['success_message'] = "Đăng ký thành công! Bạn có thể đăng nhập.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Lỗi khi đăng ký: " . $ins->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1>Đăng Ký</h1>
            <p class="auth-subtitle">
                Tạo tài khoản để bắt đầu quản lý vấn đề
            </p>

            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <button type="submit" name="register" class="btn auth-btn">Đăng Ký</button>
            </form>

            <p class="auth-link">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </p>
        </div>
    </div>
</body>

</html>