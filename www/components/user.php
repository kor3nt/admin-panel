<?php
require_once './functions/connection.php';

$connect = start_connection();

// Load user by id
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    die("Invalid user ID.");
}

$userId = (int) $_GET["id"];

// User info
$stmt = $connect->prepare("SELECT id, name, surname, username, birthday FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

// Load groups of user
$query = "
    SELECT g.id, g.name
    FROM groups g
    JOIN users_groups ug ON g.id = ug.group_id
    WHERE ug.user_id = ?
    ORDER BY g.id
";

$stmt = $connect->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$title = "User details";
ob_start();
?>

<header>
    <h1>User: <?= htmlspecialchars($user["username"]) ?></h1>

    <div class="header-actions">
        <div class="actions-btn">
            <a href="/user/<?= $user["id"] ?>/edit" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                Edit user
            </a>

            <a href="/user/<?= $user["id"] ?>/delete" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Delete user
            </a>
        </div>


        <a href="/user/<?= $user["id"] ?>/assign-groups" id="add-btn" class="primary-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Add to group
        </a>
    </div>
</header>

<main>
    <div class="details">
        <div>
            <span>Name</span>
            <p><?= htmlspecialchars($user["name"]) ?></p>
        </div>

        <div>
            <span>Surname</span>
            <p><?= htmlspecialchars($user["surname"]) ?></p>
        </div>

        <div>
            <span>Username</span>
            <p><?= htmlspecialchars($user["username"]) ?></p>
        </div>

        <div>
            <span>Birthday</span>
            <p><?= htmlspecialchars($user["birthday"]) ?></p>
        </div>
    </div>

    <h2>Groups</h2>

    <div class="table-container">
        <table class="custom-table" id="group-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if (empty($groups)): ?>
                <tr><td colspan="3" class="text-center">This user is not in any group.</td></tr>
            <?php else: ?>
                <?php foreach ($groups as $g): ?>
                    <tr data-id="<?= $g["id"] ?>">
                        <td><?= $g["id"] ?></td>
                        <td><?= htmlspecialchars($g["name"]) ?></td>
                        <td>
                            <a href="/group/<?= $g["id"] ?>/edit" class="edit-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                                Edit
                            </a>
                            <a href="/group/<?= $g["id"] ?>/remove-user/<?= $userId ?>" class="delete-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                Remove from group
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="/scripts/group.js"></script>

<?php
$body = ob_get_clean();
?>
