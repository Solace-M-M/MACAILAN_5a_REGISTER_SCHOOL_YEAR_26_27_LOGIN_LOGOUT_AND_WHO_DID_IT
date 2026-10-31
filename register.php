<?php
// register.php - create a new user account
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';

redirectIfLoggedIn();
$values = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
$field = fn(string $n) => $values[$n] ?? '';
$error = fn(string $n) => isset($errors[$n]) ? '<small class="error">' . e($errors[$n]) . '</small>' : '';

$pageTitle = 'Register';
require 'views/header.php';
?>
<section class="card">
    <h2>Create an account</h2>
    <p class="muted">Fields marked * are required.</p>
    <?php if ($errors): ?><div class="flash error">Please fix the highlighted fields.</div><?php endif; ?>
    <form action="core/handleAuth.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="register">
        <div class="grid">
            <label>First name *
                <input type="text" name="first_name" maxlength="50" required value="<?= e($field('first_name')) ?>"><?= $error('first_name') ?>
            </label>
            <label>Last name *
                <input type="text" name="last_name" maxlength="50" required value="<?= e($field('last_name')) ?>"><?= $error('last_name') ?>
            </label>
            <label>Username *
                <input type="text" name="username" maxlength="30" required autocomplete="username" value="<?= e($field('username')) ?>"><?= $error('username') ?>
            </label>
            <label>Email *
                <input type="email" name="email" maxlength="100" required value="<?= e($field('email')) ?>"><?= $error('email') ?>
            </label>
            <label>Address *
                <input type="text" name="address" maxlength="255" required value="<?= e($field('address')) ?>"><?= $error('address') ?>
            </label>
            <label>Age *
                <input type="number" name="age" min="13" max="120" required value="<?= e($field('age')) ?>"><?= $error('age') ?>
            </label>
            <label>Password * (8+ characters, a letter and a number)
                <input type="password" name="password" required autocomplete="new-password"><?= $error('password') ?>
            </label>
            <label>Confirm password *
                <input type="password" name="password_confirm" required autocomplete="new-password"><?= $error('password_confirm') ?>
            </label>
        </div>
        <button type="submit" class="btn primary">Register</button>
        <a href="login.php" class="btn ghost">I already have an account</a>
    </form>
</section>
<?php require 'views/footer.php'; ?>
