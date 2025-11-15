<?php
session_start();

require_once './functions/connection.php';

$connect = start_connection();
$error = "";

if (isset($_SESSION["user_id"])) {
    header("Location: /users");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "All fields are required.";
    } else {
        // Pobranie usera
        $stmt = $connect->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user["password"])) {
            $error = "Invalid username or password.";
        } else {
            // Login OK
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            header("Location: /users");
            exit;
        }
    }
}

$title = "Login";
ob_start();
?>

<div class="bg-gradient-blue">
    <div class="login-container">
        <form action="" method="post">

            <div class="form-input">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-input">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <?php if ($error): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$body = ob_get_clean();
?>
