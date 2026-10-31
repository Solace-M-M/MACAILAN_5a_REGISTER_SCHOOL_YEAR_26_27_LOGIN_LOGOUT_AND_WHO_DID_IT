<?php
// editStudio.php - UPDATE the PARENT record
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();                              // not logged in => redirected to login.php

$id = (int) ($_GET['id'] ?? 0);
$studio = getStudioById($pdo, $id);
if (!$studio) {
    setFlash('error', 'Studio not found.');
    redirect('index.php');
}
$values = $_SESSION['old'] ?? $studio;       // after a failed save show what was typed
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$pageTitle = 'Edit studio #' . $id;
require 'views/header.php';
?>
<p class="crumbs"><a href="index.php">&larr; All studios</a></p>
<section class="card">
    <h2>Edit studio #<?= $id ?> &mdash; <?= e($studio['studio_name']) ?></h2>
    <?php if ($errors): ?><div class="flash error">Please fix the highlighted fields.</div><?php endif; ?>
    <form action="core/handleForms.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="update_studio">
        <input type="hidden" name="id" value="<?= $id ?>">
        <?php require 'views/studioFields.php'; ?>
        <button type="submit" class="btn primary">Save changes</button>
        <a href="index.php" class="btn ghost">Cancel</a>
    </form>
</section>
<?php require 'views/footer.php'; ?>
