<?php
session_start();
require_once "./functions/connection.php";

$path = $_GET["path"] ?? "";
$parts = explode("/", trim($path, "/"));

// Router segments
$entity = $parts[0] ?? "users"; // user / group / login / users / groups
$id = $parts[1] ?? null; // numeric id
$action = $parts[2] ?? null; // edit / delete / create
$extra = $parts[3] ?? null; // extra id for example user_id

$publicPages = ["login"];

if (!isset($_SESSION["user_id"]) && !in_array($entity, $publicPages)) {
    header("Location: /login");
    exit;
}

require_once "./components/nav.php";

// Routes
$routes = [
    // Users
    "user" => [
        null => "user.php", // /user/{user_id} -> view
        "edit" => "user-form.php", // /user/{user_id}/edit
        "delete" => "delete-user.php", // /user/{user_id}/delete
        "assign-groups" => "assign-groups.php", // /group/{group_id}/assign-users
        "create" => "user-form.php", // /user/create
    ],

    // Groups
    "group" => [
        null => "group.php", // /group/{group_id}
        "edit" => "group-form.php", // /group/{group_id}/edit
        "delete" => "delete-group.php", // /group/{group_id}/delete
        "assign-users" => "assign-users.php", // /group/{group_id}/assign-users
        "create" => "group-form.php", // /group/create
        "remove-user" => "remove-user.php", // /group/{group_id}/remove-user/{user_id}
    ],

    // List view
    "users" => "users-table.php",
    "groups" => "groups-table.php",

    // Login
    "login" => "login.php",
    
    // Logout
    "logout" => "logout.php",
];

// Route resolution
if (!isset($routes[$entity])) {
    http_response_code(404);
    die("404 – Invalid entity");
}

$target = $routes[$entity];

// Check is array
if (is_array($target)) {

    // Create /user/create (without id)
    if ($id === "create") {
        $action = "create";
        $id = null;
    }

    if (!array_key_exists($action, $target)) {
        http_response_code(404);
        die("404 – Invalid action");
    }

    $file = $target[$action];

} else {
    // Basic site np. /users
    $file = $target;
}

// Give id - for example forms
if ($id !== null) {
    $_GET["id"] = $id;
}

if ($extra !== null) {
    $_GET["extra"] = $extra;
}

// Loading layout
require_once "./components/" . $file;

// Layout
$title = isset($title) ? $title . " | " : "";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?>Admin Panel</title>

    <link rel="stylesheet" href="/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

<!-- If login -->
<?php if (!empty($_SESSION["user_id"])): ?>
    <?= $navbar ?>
<?php endif; ?>

<!-- Show body -->
<?= $body ?>
</body>
</html>
