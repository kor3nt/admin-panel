<?php
ob_start();
?>

<div class="bg-gradient-blue">
    <div class="login-container">
        <form action="#" method="post">
            <div class="form-input">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>

            <div class="form-input">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>

            <button type="button" id="login-btn">Login</button>
        </form>
    </div>
</div>

<?php
$body = ob_get_clean();
?>