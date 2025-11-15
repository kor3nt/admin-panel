<?php
require_once __DIR__ . '/../functions/connection.php';

$connect = start_connection();

// Router ustawił:
$groupId = isset($_GET["id"]) ? (int) $_GET["id"] : null;
$userId  = isset($_GET["extra"]) ? (int) $_GET["extra"] : null;

if (!$groupId || !$userId) {
    die("Invalid parameters.");
}

$stmt = $connect->prepare("
    DELETE FROM users_groups
    WHERE user_id = ? AND group_id = ?
");

$stmt->bind_param("ii", $userId, $groupId);
$stmt->execute();
$stmt->close();

stop_connection($connect);

// Redirect
if (!empty($_SERVER["HTTP_REFERER"])) {
    header("Location: " . $_SERVER["HTTP_REFERER"]);
} else {
    header("Location: /group/$groupId");
}
exit;
?>
