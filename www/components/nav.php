<?php
ob_start();
?>

<nav class="nav">
    <ul>
        <li><a href="../lists.html">Users</a></li>
        <li><a href="table-groups.php">Groups</a></li>
    </ul>
</nav>

<?php
$navbar = ob_get_clean();
?>