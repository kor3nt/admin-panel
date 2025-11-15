<?php
require_once __DIR__ . '/../functions/connection.php';

$connect = start_connection();

// Validate params
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    die("Invalid user ID.");
}

$userId = (int) $_GET["id"];

// Delete user
$stmt = $connect->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

stop_connection($connect);

// Redirect back to list
header("Location: /users");
exit;
