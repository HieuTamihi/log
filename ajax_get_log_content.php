<?php
include 'db.php';
requireLogin(); // Nếu cần check login, giữ nguyên

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT content FROM logs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result) {
        header('Content-Type: application/json');
        echo json_encode(['content' => $result['content']]);
    } else {
        echo json_encode(['error' => 'Không tìm thấy nội dung']);
    }
} else {
    echo json_encode(['error' => 'Thiếu ID']);
}
?>