<?php
include 'db.php';
$sql = "ALTER TABLE users ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL";
if ($conn->query($sql) === TRUE) {
    echo "Column remember_token created successfully";
} else {
    echo "Error creating column: " . $conn->error;
}
?>