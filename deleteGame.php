<?php
// deleteGame.php - confirmation before DELETING a CHILD record
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();                              // not logged in => redirected to login.php

$id = (int) ($_GET['id'] ?? 0);
$game = getGameById($pdo, $id);
if (!$game) {
    setFlash('error', 'Game not found.');
    redirect('index.php');
}
$pageTitle = 'Delete game #' . $id;
require 'views/header.php';
?>
<section class="card">
    <h2>Delete this game?</h2>
    <p>You are about to permanently delete <strong><?= e($game['title']) ?></strong>
       (<?= e($game['genre']) ?>, by <?= e($game['studio_name']) ?>). The studio itself is not affected. This cannot be undone.</p>
    <form action="core/handleForms.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete_game">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit" class="btn danger">Yes, delete</button>
        <a href="studio.php?id=<?= (int) $game['studio_id'] ?>" class="btn ghost">Cancel</a>
    </form>
</section>
<?php require 'views/footer.php'; ?>
