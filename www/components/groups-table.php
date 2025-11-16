<?php
require_once './functions/connection.php';

session_start();
$connect = start_connection();

// Limit
$resultsPerPage = 5;

// Page number
$page = isset($_GET['number']) && is_numeric($_GET['number']) ? (int) $_GET['number'] : 1;
if ($page < 1) $page = 1;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (strlen($search) > 100) $search = substr($search, 0, 100);

// Build WHERE
$where = "";
$params = [];
$types = "";

if ($search !== "") {
    $where = "WHERE name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Count total rows
$sqlCount = "SELECT COUNT(*) AS total FROM `groups` $where";
$stmt = $connect->prepare($sqlCount);

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$totalRows = $stmt->get_result()->fetch_assoc()["total"];
$stmt->close();

$totalPages = max(1, ceil($totalRows / $resultsPerPage));
if ($page > $totalPages) $page = $totalPages;

// Fetch rows
$offset = ($page - 1) * $resultsPerPage;
$sqlList = "SELECT id, name FROM `groups` $where ORDER BY id LIMIT $resultsPerPage OFFSET $offset";

$stmt = $connect->prepare($sqlList);

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

stop_connection($connect);

$title = "Groups list";

require_once "./components/pagination.php";

ob_start();
?>

<header>
    <h1>Group Management</h1>

    <div class="header-actions">

        <a href="/group/create" id="add-btn" class="primary-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Create group
        </a>

        <form method="GET" class="search-box">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search...">
            <button type="submit" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
                Search
            </button>
        </form>

    </div>
</header>

<main>
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
                <?php if ($totalRows > 0): ?>
                    <?php foreach ($groups as $g): ?>
                        <tr data-id="<?= $g["id"] ?>">
                            <td><?= (int)$g['id'] ?></td>
                            <td><?= htmlspecialchars($g['name']) ?></td>
                            <td>
                                <a href="/group/<?= $g['id'] ?>/edit" class="edit-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
                                    Edit
                                </a>
                                <a href="/group/<?= $g['id'] ?>/delete" class="delete-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No records</td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <div id="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= pageUrl($i, $search) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</main>

<script src="/scripts/group.js"></script>
<script src="/scripts/delete-action.js"></script>

<?php
$body = ob_get_clean();
?>
