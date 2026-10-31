<?php
// Shared page header with navigation. Expects $pageTitle. Shows the one-time flash message.
$flash = pullFlash();
$me = isLoggedIn() ? currentUser() : null;
$root = $root ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Emilio Aguinaldo Gaming') ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a href="index.php" class="brand">🎮 Emilio Aguinaldo Gaming <span>One studio, many games</span></a>
    <?php if ($me): ?>
    <nav class="nav">
        <a href="index.php">Studios</a>
        <a href="activity.php">Activity log</a>
        <span class="who">Logged in as <strong><?= e($me['username']) ?></strong></span>
        <form action="core/handleAuth.php" method="post" class="inline">
            <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="btn small">Log out</button>
        </form>
    </nav>
    <?php endif; ?>
</header>
<main>
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endif; ?>
