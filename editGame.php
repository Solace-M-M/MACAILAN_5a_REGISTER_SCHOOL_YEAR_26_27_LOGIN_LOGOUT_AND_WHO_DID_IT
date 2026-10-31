<?php
// editGame.php - UPDATE the CHILD record (the studio drop-down can move the game to another studio)
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();                              // not logged in => redirected to login.php

$id = (int) ($_GET['id'] ?? 0);
$game = getGameById($pdo, $id);              // JOIN gives us the studio name too
if (!$game) {
    setFlash('error', 'Game not found.');
    redirect('index.php');
}
$studioOptions = getStudioOptions($pdo);     // parents for the drop-down
$values = $_SESSION['old'] ?? $game;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$pageTitle = 'Edit game #' . $id;
require 'views/header.php';
?>
<p class="crumbs"><a href="studio.php?id=<?= (int) $game['studio_id'] ?>">&larr; Back to <?= e($game['studio_name']) ?></a></p>
<section class="card">
    <h2>Edit game #<?= $id ?> &mdash; <?= e($game['title']) ?></h2>
    <p class="muted">Currently owned by <?= e($game['studio_name']) ?></p>
    <?php if ($errors): ?><div class="flash error">Please fix the highlighted fields.</div><?php endif; ?>
    <form action="core/handleForms.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="update_game">
        <input type="hidden" name="id" value="<?= $id ?>">
        <?php require 'views/gameFields.php'; ?>
        <button type="submit" class="btn primary">Save changes</button>
        <a href="studio.php?id=<?= (int) $game['studio_id'] ?>" class="btn ghost">Cancel</a>
    </form>
</section>
<?php require 'views/footer.php'; ?>
