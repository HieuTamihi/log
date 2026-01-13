<?php
include 'db.php';
$res = $conn->query('DESCRIBE users');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error;
}
?>