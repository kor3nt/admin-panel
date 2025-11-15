<?php

require_once './functions/connection.php';

$connect = start_connection();

// Find group by id
function findGroupById(mysqli $db, int $id): ?array {
    $stmt = $db->prepare("
        SELECT name FROM groups WHERE id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $group = $res->fetch_assoc();
    $stmt->close();

    return $group ?: null;
}

// Check unique for group name
function groupNameExists(mysqli $db, string $name, ?int $ignoreId = null): bool {
    if ($ignoreId) {
        $stmt = $db->prepare("
            SELECT id FROM groups WHERE name = ? AND id != ?
        ");

        $stmt->bind_param("si", $name, $ignoreId);
    } else {
        $stmt = $db->prepare("
            SELECT id FROM groups WHERE name = ?
        ");

        $stmt->bind_param("s", $name);
    }

    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

// Insert group
function insertGroup(mysqli $db, string $name): void {
    $stmt = $db->prepare("
        INSERT INTO groups (name) VALUES (?)
    ");

    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
}

// Update group
function updateGroup(mysqli $db, int $id, string $name): void {
    $stmt = $db->prepare("
        UPDATE groups SET name = ? WHERE id = ?
    ");

    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
    $stmt->close();
}

$values = ["name" => ""];
$errors = ["name" => ""];

$isEdit = false;
$editId = null;

// Detect edit
if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $isEdit = true;
    $editId = (int) $_GET["id"];

    $group = findGroupById($connect, $editId);
    if (!$group) {
        die("Group not found.");
    }

    $values = array_merge($values, $group);
}

// Check if post method
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $values["name"] = trim($_POST["name"] ?? "");

    // Validation
    if (strlen($values["name"]) < 2) {
        $errors["name"] = "Group name must contain at least 2 characters.";
    }

    // Group name uniqueness
    if (empty($errors["name"]) && groupNameExists($connect, $values["name"], $editId)) {
        $errors["name"] = "This group name already exists.";
    }

    $hasErrors = array_filter($errors, fn($v) => !empty($v));

    if (!$hasErrors) {
        if ($isEdit) {
            updateGroup($connect, $editId, $values["name"]);
        } else {
            insertGroup($connect, $values["name"]);
        }

        stop_connection($connect);
        header("Location: /groups");
        exit;
    }
}

$title = $isEdit ? "Edit group" : "Create group";

ob_start();
?>

<main>
    <div class="form-container">
        <form action="#" method="post" class="form-box">
            <div class="inputs-container">
                <div class="form-input">
                    <label for="name">Name</label>
                    <input type="text" value="<?= htmlspecialchars($values["name"]) ?>" name="name" id="name" required>

                    <?php if ($errors["name"]): ?>
                        <p class="error"><?= $errors["name"] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-save-icon lucide-save"><path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/></svg>
                Save
            </button>
        </form>
    </div>
</main>

<?php
$body = ob_get_clean();
?>