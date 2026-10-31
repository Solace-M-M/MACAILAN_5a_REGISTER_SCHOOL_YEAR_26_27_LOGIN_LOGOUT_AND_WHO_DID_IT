<?php
// studio.php - CHILD entity: the games of ONE studio, plus a form to add a game to it
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();                              // not logged in => redirected to login.php

$studioId = (int) ($_GET['id'] ?? 0);
$studio   = getStudioById($pdo, $studioId);          // READ the parent
if (!$studio) {
    setFlash('error', 'Studio not found.');
    redirect('index.php');
}
$games = getGamesByStudio($pdo, $studioId);          // READ all children of this parent

$values = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
$lockedStudioId = $studioId;                         // the new game belongs to this studio

$pageTitle = $studio['studio_name'] . ' - Games';
require 'views/header.php';
?>

<p class="crumbs"><a href="index.php">&larr; All studios</a></p>

<section class="card">
    <h2><?= e($studio['studio_name']) ?></h2>
    <p class="muted"><?= e($studio['studio_type']) ?> studio &middot; <?= e($studio['country']) ?> &middot; founded <?= (int) $studio['year_founded'] ?></p>
    <div class="stats">
        <span>Founder: <strong><?= e($studio['founder_name']) ?></strong></span>
        <span>Email: <strong><?= e($studio['email']) ?></strong></span>
        <span>Games registered: <strong><?= (int) $studio['games_count'] ?></strong></span>
        <span>Added by: <strong><?= e($studio['added_by_name'] ?? 'System') ?></strong></span>
        <span>Last updated by: <strong><?= e($studio['updated_by_name'] ?? 'System') ?></strong>
              on <?= e(date('M j, Y g:i A', strtotime($studio['last_updated']))) ?></span>
    </div>
</section>

<section class="card">
    <h2>Add a game to <?= e($studio['studio_name']) ?></h2>
    <?php if ($errors): ?><div class="flash error">Please fix the highlighted fields.</div><?php endif; ?>
    <form action="core/handleForms.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="add_game">
        <?php require 'views/gameFields.php'; ?>
        <button type="submit" class="btn primary">Add game</button>
    </form>
</section>

<section class="card">
    <h2>Games by <?= e($studio['studio_name']) ?> (<?= count($games) ?>)</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Title</th><th>Genre</th><th>Platform</th><th>Engine</th><th>Status</th>
                    <th>Price</th><th>Release date</th><th>Added by</th><th>Last updated</th><th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$games): ?>
                <tr><td colspan="11" class="empty">This studio has no games yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($games as $g): ?>
                <tr>
                    <td><?= (int) $g['game_id'] ?></td>
                    <td><?= e($g['title']) ?></td>
                    <td><?= e($g['genre']) ?></td>
                    <td><?= e($g['platform']) ?></td>
                    <td><?= e($g['game_engine']) ?></td>
                    <td><span class="badge <?= strtolower(str_replace(' ', '', e($g['release_status']))) ?>"><?= e($g['release_status']) ?></span></td>
                    <td class="num"><?= (float) $g['price'] == 0 ? 'Free' : '$' . number_format((float) $g['price'], 2) ?></td>
                    <td><?= $g['release_date'] ? e(date('M j, Y', strtotime($g['release_date']))) : 'TBA' ?></td>
                    <td class="who"><?= e($g['added_by_name'] ?? 'System') ?><small><?= e(date('M j, Y g:i A', strtotime($g['date_added']))) ?></small></td>
                    <td class="who"><?= e($g['updated_by_name'] ?? 'System') ?><small><?= e(date('M j, Y g:i A', strtotime($g['last_updated']))) ?></small></td>
                    <td class="actions">
                        <div class="row-actions">
                            <a class="btn small" href="editGame.php?id=<?= (int) $g['game_id'] ?>">Edit</a>
                            <a class="btn small danger" href="deleteGame.php?id=<?= (int) $g['game_id'] ?>">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'views/footer.php'; ?>
