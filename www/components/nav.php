<?php
ob_start();
?>

<nav class="nav">
    <ul>
        <li><a href="users-table.php">Users</a></li>
        <li><a href="groups-table.php">Groups</a></li>
    </ul>
</nav>

<?php
$navbar = ob_get_clean();
?>