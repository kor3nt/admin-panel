<?php
require_once './functions/connection.php';

session_start();
$connect = start_connection();

// Limit per page
$resultsPerPage = 5;

// Page param
$page = isset($_GET['number']) && is_numeric($_GET['number']) ? (int) $_GET['number'] : 1;
if ($page < 1) $page = 1;

// Search param
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (strlen($search) > 100) {
    $search = substr($search, 0, 100);
}

// Build WHERE + prepared statement params
$where = "";
$params = [];
$types = "";

if ($search !== "") {
    $where = "WHERE username LIKE ? OR name LIKE ? OR surname LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

// Count total rows
$sqlCount = "SELECT COUNT(*) AS total FROM users $where";
$stmt = $connect->prepare($sqlCount);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$totalPages = max(1, ceil($totalRows / $resultsPerPage));
if ($page > $totalPages) $page = $totalPages;

// Fetch rows
$offset = ($page - 1) * $resultsPerPage;

$sqlList = "
    SELECT id, username, name, surname, birthday 
    FROM users
    $where
    ORDER BY id
    LIMIT $resultsPerPage OFFSET $offset
";

$stmt = $connect->prepare($sqlList);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

stop_connection($connect);

$title = "Users list";

require_once "./components/pagination.php";

ob_start();
?>

<header>
    <h1>User Management</h1>

    <div class="header-actions">
        <a href="/user/create" id="add-btn" class="primary-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 24 24">
                <path d="M5 12h14"/><path d="M12 5v14"/>
            </svg>
            Create User
        </a>

        <form method="GET" class="search-box">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search...">
            <button type="submit" class="primary-btn">Search</button>
        </form>
    </div>
</header>

<main>
    <div class="table-container">
        <table class="custom-table" id="user-table">

            <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Birth date</th>
                <th>Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php if ($totalRows > 0): ?>
                <?php foreach ($users as $u): ?>
                    <tr data-id="<?= $u["id"] ?>">
                        <td><?= (int)$u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['surname']) ?></td>
                        <td><?= htmlspecialchars(date('d.m.Y', strtotime($u['birthday']))) ?></td>

                        <td>
                            <a href="/user/<?= $u['id'] ?>/edit" class="edit-btn">Edit</a>
                            <a href="/user/<?= $u['id'] ?>/delete" class="delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No records</td>
                </tr>
            <?php endif; ?>
            </tbody>

        </table>
    </div>

    <div id="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= pageUrl($i, $search) ?>" class="<?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</main>

<script src="/scripts/user.js"></script>

<?php
$body = ob_get_clean();
?>