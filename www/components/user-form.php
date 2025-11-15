<?php
require_once './functions/connection.php';

$connect = start_connection();

// Finds users
function findUserById(mysqli $db, int $id): ?array {
    $stmt = $db->prepare("SELECT name, surname, username, birthday FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

// Check unique for group username
function usernameExists(mysqli $db, string $username, ?int $ignoreId = null): bool {
    if ($ignoreId) {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $ignoreId);
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
    }

    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();

    return $exists;
}

// Insert new user
function insertUser(mysqli $db, array $values, string $password): void {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO users (name, surname, username, password, birthday)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssss",
        $values["name"],
        $values["surname"],
        $values["username"],
        $hash,
        $values["birthday"],
    );

    $stmt->execute();
    $stmt->close();
}

// Update user's data
function updateUser(mysqli $db, int $id, array $values, ?string $password): void {

    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, surname = ?, username = ?, password = ?, birthday = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssssi",
            $values["name"],
            $values["surname"],
            $values["username"],
            $hash,
            $values["birthday"],
            $id,
        );
    } else {
        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, surname = ?, username = ?, birthday = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssssi",
            $values["name"],
            $values["surname"],
            $values["username"],
            $values["birthday"],
            $id,
        );
    }

    $stmt->execute();
    $stmt->close();
}

// Default values
$values = [
    "name" => "",
    "surname" => "",
    "username" => "",
    "birthday" => "",
];

$errors = [
    "name" => "",
    "surname" => "",
    "username" => "",
    "password" => "",
    "birthday" => "",
];

// Detect edit mode
$isEdit = false;
$editId = null;

if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $isEdit = true;
    $editId = (int) $_GET["id"];

    $user = findUserById($connect, $editId);
    if (!$user) {
        die("User not found.");
    }

    $values = array_merge($values, $user);
}

// Check if post method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Load submitted values
    foreach ($values as $key => $_) {
        $values[$key] = trim($_POST[$key] ?? "");
    }
    $password = trim($_POST["password"] ?? "");

    // Validation
    if (strlen($values["name"]) < 2) {
        $errors["name"] = "Name must contain at least 2 characters.";
    }

    if (strlen($values["surname"]) < 2) {
        $errors["surname"] = "Surname must contain at least 2 characters.";
    }

    if (!preg_match("/^[A-Za-z0-9_]{3,20}$/", $values["username"])) {
        $errors["username"] = "Username must be 3-20 characters (letters, numbers, underscore).";
    }

    if ($isEdit) {
        if (!empty($password) && strlen($password) < 6) {
            $errors["password"] = "Password must be at least 6 characters.";
        }
    } else {
        if (strlen($password) < 6) {
            $errors["password"] = "Password must be at least 6 characters.";
        }
    }

    if (empty($values["birthday"])) {
        $errors["birthday"] = "Birth date is required.";
    }

    // Username uniqueness
    if (empty($errors["username"]) && usernameExists($connect, $values["username"], $editId)) {
        $errors["username"] = "This username already exists.";
    }

    // Check if any errors exist
    $hasErrors = array_filter($errors, fn($v) => !empty($v));

    // Save if valid
    if (!$hasErrors) {
        if ($isEdit) {
            updateUser($connect, $editId, $values, $password ?: null);
        } else {
            insertUser($connect, $values, $password);
        }

        stop_connection($connect);
        header("Location: /users");
        exit;
    }
}


$title = $isEdit ? "Edit user" : "Create user";

ob_start();
?>
<main>
    <div class="form-container">
        <form method="post" class="form-box">
            <h1><?= $isEdit ? "Edit user" : "Create user" ?></h1>
            <div class="inputs-container">
                <div class="form-input">
                    <label for="name">Name</label>
                    <input type="text" value="<?= htmlspecialchars($values["name"]) ?>" name="name" id="name" required>

                    <?php if ($errors["name"]): ?>
                        <p class="error"><?= $errors["name"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-input">
                    <label for="surname">Surname</label>
                    <input type="text" value="<?= htmlspecialchars($values["surname"]) ?>" name="surname" id="surname" required>

                    <?php if ($errors["surname"]): ?>
                        <p class="error"><?= $errors["surname"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-input">
                    <label for="username">Username</label>
                    <input type="text" value="<?= htmlspecialchars($values["username"]) ?>" name="username" id="username" required>

                    <?php if ($errors["username"]): ?>
                        <p class="error"><?= $errors["username"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" <?= !$isEdit ? "required" : "" ?> >

                    <?php if ($errors["password"]): ?>
                        <p class="error"><?= $errors["password"] ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-input">
                    <label for="birthday">Birthday</label>
                    <input type="date" value="<?= htmlspecialchars($values["birthday"]) ?>" name="birthday" id="birthday" required>

                    <?php if ($errors["birthday"]): ?>
                        <p class="error"><?= $errors["birthday"] ?></p>
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