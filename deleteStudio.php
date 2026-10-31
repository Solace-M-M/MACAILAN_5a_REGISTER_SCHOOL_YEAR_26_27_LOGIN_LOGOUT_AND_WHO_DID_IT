<?php
// deleteStudio.php - confirmation before DELETING the PARENT (its games are deleted too)
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();                              // not logged in => redirected to login.php

$id = (int) ($_GET['id'] ?? 0);
$studio = getStudioById($pdo, $id);          // includes games_count
if (!$studio) {
    setFlash('error', 'Studio not found.');
    redirect('index.php');
}
$pageTitle = 'Delete studio #' . $id;
require 'views/header.php';
?>
<section class="card">
    <h2>Delete this studio?</h2>
    <p>You are about to permanently delete <strong><?= e($studio['studio_name']) ?></strong>.
       <?php if ((int) $studio['games_count'] > 0): ?>
           Because of the one-to-many relationship, its <strong><?= (int) $studio['games_count'] ?> game(s)</strong> will be deleted too.
       <?php endif; ?>
       This cannot be undone.</p>
    <form action="core/handleForms.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete_studio">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit" class="btn danger">Yes, delete</button>
        <a href="index.php" class="btn ghost">Cancel</a>
    </form>
</section>
<?php require 'views/footer.php'; ?>
