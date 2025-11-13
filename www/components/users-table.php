<?php
$title = "Users list";

require_once './functions/connection.php';

$connect = start_connection();

// Limit per page
$resultsPerPage = 5;

// Params for url
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$offset = ($page - 1) * $resultsPerPage;

$where = "";
if (!empty($search)) {
    $safeSearch = $connect->real_escape_string($search);
    $where = "WHERE username LIKE '%$safeSearch%'
        OR name LIKE '%$safeSearch%'
        OR surname LIKE '%$safeSearch%'";
}

// Count results
$countQuery = "SELECT COUNT(*) as total FROM `users` $where";
$countResult = $connect->query($countQuery);
$countRow = $countResult->fetch_assoc();
$totalRows = $countRow['total'];
$totalPages = ceil($totalRows / $resultsPerPage);

// Get results
$query = "SELECT * FROM `users` $where LIMIT $resultsPerPage OFFSET $offset";
$result = $connect->query($query);

stop_connection($connect);

ob_start();
?>
<div class="page-container">
    <header>
        <h1>User Management</h1>

        <div class="header-actions">
            <button id="add-btn" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Create User
            </button>

            <form method="GET" class="search-box">
                <input type="text" name="search" id="search" placeholder="Search...">
                <button type="submit" name="search-btn" class="primary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
                    Search
                </button>
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
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Birth Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                        // Show records or empty
                        if ($totalRows > 0) {
                            while($row = $result->fetch_assoc()){
                                echo "<tr>
                                        <td>".$row['id']."</td>
                                        <td>".$row['username']."</td>
                                        <td>".$row['name']."</td>
                                        <td>".$row['surname']."</td>
                                        <td>".$row['birthday']."</td>
                                        <td>
                                           <a href='#' class='edit-btn'>
                                                Edit
                                            </a>
                                            <a href='#' class='delete-btn'>
                                                Delete
                                            </a>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr>
                                    <td colspan='6' class='text-center'>No records</td>
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
                echo "<a href='{$baseUrl}page=$i' class='$active'>$i</a>";
            }
            ?>
        </div>
    </main>
</div>