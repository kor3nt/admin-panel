<?php
ob_start();
?>

<nav class="nav">
    <ul>
        <li><a href="/users">Users</a></li>
        <li><a href="/groups">Groups</a></li>
        <li><a href="/logout">Logout</a></li>
    </ul>
</nav>

<?php
$navbar = ob_get_clean();
?>