<?php
require_once './functions/connection.php';
$connect = start_connection();

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    die("Invalid user ID.");
}

$userId = (int)$_GET["id"];

// Fetch user
$stmt = $connect->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Fetch all groups
$groups = $connect->query("
    SELECT id, name FROM groups ORDER BY name ASC
")->fetch_all(MYSQLI_ASSOC);

// Fetch user assigned groups
$stmt = $connect->prepare("SELECT group_id FROM users_groups WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$assignedIds = array_column($res->fetch_all(MYSQLI_ASSOC), "group_id");
$stmt->close();

// Save on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $selected = $_POST["groups"] ?? [];

    // Remove old assignments
    $stmt = $connect->prepare("DELETE FROM users_groups WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Insert new
    $stmt = $connect->prepare("INSERT INTO users_groups (user_id, group_id) VALUES (?, ?)");
    foreach ($selected as $gid) {
        $gid = (int)$gid;
        $stmt->bind_param("ii", $userId, $gid);
        $stmt->execute();
    }
    $stmt->close();

    stop_connection($connect);

    header("Location: /user/$userId");
    exit;
}

$title = "Assign Groups";

ob_start();
?>

<main>
    <div class="form-container">
        <form action="#" method="post" class="form-box">
            <h2>Assign groups to user: <?= htmlspecialchars($user["username"]) ?></h2>
            <div class="checkbox-list">
                <?php foreach ($groups as $g): ?>
                    <label class="checkbox-item">
                        <input
                                type="checkbox"
                                name="groups[]"
                                value="<?= $g['id'] ?>"
                                <?= in_array($g['id'], $assignedIds) ? 'checked' : '' ?>
                        >
                        <span><?= htmlspecialchars($g["name"]) ?></span>
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
