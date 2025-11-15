<?php
require_once './functions/connection.php';

$connect = start_connection();

// Load group by id
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    die("Invalid group ID.");
}

$groupId = (int) $_GET["id"];

// Group info
$stmt = $connect->prepare("SELECT id, name FROM groups WHERE id = ?");
$stmt->bind_param("i", $groupId);
$stmt->execute();
$res = $stmt->get_result();
$group = $res->fetch_assoc();
$stmt->close();

if (!$group) {
    die("Group not found.");
}

// Group users (join pivot)
$query = "
    SELECT u.id, u.username, u.name, u.surname, u.birthday
    FROM users u
    JOIN users_groups ug ON u.id = ug.user_id
    WHERE ug.group_id = ?
    ORDER BY u.id
";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $groupId);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$title = "Group details";
ob_start();
?>

<header>
    <h1>Group: <?= htmlspecialchars($group["name"]) ?></h1>

    <div class="header-actions">
        <div class="actions-btn">
            <a  href="/group/<?= $groupId ?>/edit" class="primary-btn">
                Edit group
            </a>
            <a  href="/group/<?= $groupId ?>/delete" class="primary-btn">
                Delete group
            </a>
        </div>

        <a id="add-btn" class="primary-btn" href="/group/<?= $groupId ?>/assign-users">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Add users
        </a>
    </div>
</header>

<main>
    <h2>Users</h2>

    <div class="table-container">
        <table class="custom-table" id="user-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Birth Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center">No users in this group.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr data-id="<?= $u["id"] ?>">
                            <td><?= $u["id"] ?></td>
                            <td><?= htmlspecialchars($u["username"]) ?></td>
                            <td><?= htmlspecialchars($u["name"]) ?></td>
                            <td><?= htmlspecialchars($u["surname"]) ?></td>
                            <td><?= htmlspecialchars($u["birthday"]) ?></td>
                            <td>
                                <a href="/user/<?= $u["id"] ?>/edit"  class="edit-btn">Edit</a>
                                <a href="/group/<?= $groupId ?>/remove-user/<?= $u["id"] ?>" class="delete-btn">
                                    Remove from group
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="pagination"></div>
</main>

<script src="/scripts/user.js"></script>

<?php
$body = ob_get_clean();
?>