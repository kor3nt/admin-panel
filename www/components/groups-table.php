<?php
$title = "Groups list";

require_once './functions/connection.php';

$connect = start_connection();

// Limit per page
$resultsPerPage = 5;

// Params for url
$page = isset($_GET['number']) && is_numeric($_GET['number']) ? (int) $_GET['number'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$offset = ($page - 1) * $resultsPerPage;

$where = "";
if (!empty($search)) {
    $safeSearch = $connect->real_escape_string($search);
    $where = "WHERE name LIKE '%$safeSearch%'";
}

// Count results
$countQuery = "SELECT COUNT(*) as total FROM `groups` $where";
$countResult = $connect->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalRows = $countRow['total'];
$totalPages = ceil($totalRows / $resultsPerPage);

// Get results
$query = "SELECT * FROM `groups` $where LIMIT $resultsPerPage OFFSET $offset";
$result = $connect->query($query);

stop_connection($connect);

ob_start();
?>
<header>
    <h1>Group Management</h1>

    <div class="header-actions">
        <a href="/group/create" id="add-btn" class="primary-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Create group
        </a>

        <form method="GET" class="search-box">
            <input type="text" name="search" id="search" placeholder="Search...">
            <button type="submit" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
                <span>Search</span>
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
                <?php
                    // Show records or empty
                    if ($totalRows > 0) {
                        while($row = $result->fetch_assoc()){
                            echo "<tr data-id=".$row["id"].">
                                    <td>".$row['id']."</td>
                                    <td>".$row['name']."</td>
                                    <td>
                                       <a href='/group/".$row['id']."/edit' class='edit-btn'>
                                            Edit
                                        </a>
                                        <a href='/group/".$row['id']."/delete'  class='delete-btn'>
                                            Delete
                                        </a>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr>
                                <td colspan='3' class='text-center'>No records</td>
                            </tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>

    <div id="pagination">
        <?php
            // Pagination
            $baseUrl = '?';
            if (!empty($search)) {
                $baseUrl .= 'search=' . urlencode($search) . '&';
            }

            for ($i = 1; $i <= $totalPages; $i++) {

                $active = ($i == $page) ? 'active' : '';
                echo "<a href='{$baseUrl}number=$i' class='$active'>$i</a>";
            }
        ?>
    </div>
</main>

<script src="/scripts/group.js"></script>

<?php
$body = ob_get_clean();
?>