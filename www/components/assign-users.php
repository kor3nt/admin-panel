<?php
require_once './functions/connection.php';
$connect = start_connection();

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    die("Invalid group ID.");
}

$groupId = (int)$_GET["id"];

// Fetch group
$stmt = $connect->prepare("SELECT name FROM groups WHERE id = ?");
$stmt->bind_param("i", $groupId);
$stmt->execute();
$res = $stmt->get_result();
$group = $res->fetch_assoc();
$stmt->close();

if (!$group) {
    die("Group not found.");
}

// Fetch all users
$users = $connect->query("
    SELECT id, username FROM users ORDER BY username ASC
")->fetch_all(MYSQLI_ASSOC);

// Fetch assigned users
$stmt = $connect->prepare("SELECT user_id FROM users_groups WHERE group_id = ?");
$stmt->bind_param("i", $groupId);
$stmt->execute();
$res = $stmt->get_result();
$assignedIds = array_column($res->fetch_all(MYSQLI_ASSOC), 'user_id');
$stmt->close();

// Save data
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $selected = $_POST["users"] ?? [];

    // Remove old assignments
    $stmt = $connect->prepare("DELETE FROM users_groups WHERE group_id = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $stmt->close();

    // Insert new
    $stmt = $connect->prepare("INSERT INTO users_groups (user_id, group_id) VALUES (?, ?)");
    foreach ($selected as $uid) {
        $uid = (int)$uid;
        $stmt->bind_param("ii", $uid, $groupId);
        $stmt->execute();
    }
    $stmt->close();

    stop_connection($connect);

    header("Location: /group/$groupId");
    exit;
}

$title = "Assign Users";

ob_start();
?>

<main>
    <div class="form-container">

        <form action="#" method="post" class="form-box">

            <h2>Assign users to group: <?= htmlspecialchars($group["name"]) ?></h2>

            <div class="checkbox-list">
                <?php foreach ($users as $u): ?>
                    <label class="checkbox-item">
                        <input
                                type="checkbox"
                                name="users[]"
                                value="<?= $u['id'] ?>"
                                <?= in_array($u['id'], $assignedIds) ? 'checked' : '' ?>
                        >
                        <span><?= htmlspecialchars($u["username"]) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="primary-btn">Save</button>

        </form>

    </div>
</main>

<?php
$body = ob_get_clean();
?>
