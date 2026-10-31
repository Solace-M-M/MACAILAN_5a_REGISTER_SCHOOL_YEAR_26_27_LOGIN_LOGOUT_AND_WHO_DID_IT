<?php
// login.php - the login form (the first page a visitor sees)
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';

redirectIfLoggedIn();                     // already logged in => homepage
$values = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

$pageTitle = 'Log in';
require 'views/header.php';
?>
<section class="card narrow">
    <h2>Log in</h2>
    <p class="muted">Log in to manage game studios and their games.</p>
    <form action="core/handleAuth.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="login">
        <div class="grid one">
            <label>Username or email
                <input type="text" name="login" required autofocus autocomplete="username" value="<?= e($values['login'] ?? '') ?>">
            </label>
            <label>Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>
        </div>
        <button type="submit" class="btn primary">Log in</button>
        <a href="register.php" class="btn ghost">Create an account</a>
    </form>
</section>
<?php require 'views/footer.php'; ?>
