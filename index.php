<?php
// index.php - PARENT entity: register a studio (form) and list all studios
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();                              // not logged in => redirected to login.php

$search  = trim($_GET['q'] ?? '');
$studios = getStudios($pdo, $search);       // READ: each studio with its number of games

// Values/errors left by a failed submission (so the form keeps what was typed)
$values = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$pageTitle = 'Game Studios';
require 'views/header.php';
?>

<section class="card">
    <h2>Register a game studio</h2>
    <p class="muted">The parent record. Games are added to a studio on its own page. Fields marked * are required.</p>
    <?php if ($errors): ?><div class="flash error">Please fix the highlighted fields.</div><?php endif; ?>

    <form action="core/handleForms.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="add_studio">
        <?php require 'views/studioFields.php'; ?>
        <button type="submit" class="btn primary">Register studio</button>
    </form>
</section>

<section class="card">
    <h2>Registered studios (<?= count($studios) ?>)</h2>

    <form method="get" class="filters">
        <input type="search" name="q" placeholder="Search studio, founder or country" value="<?= e($search) ?>">
        <button type="submit" class="btn">Search</button>
        <a href="index.php" class="btn ghost">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Studio</th><th>Founder</th><th>Email</th><th>Country</th><th>Founded</th>
                    <th>Type</th><th>Website</th><th>Games</th><th>Added by</th><th>Last updated</th><th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$studios): ?>
                <tr><td colspan="12" class="empty">No studios found.</td></tr>
            <?php endif; ?>
            <?php foreach ($studios as $s): ?>
                <tr>
                    <td><?= (int) $s['studio_id'] ?></td>
                    <td><a href="studio.php?id=<?= (int) $s['studio_id'] ?>"><?= e($s['studio_name']) ?></a></td>
                    <td><?= e($s['founder_name']) ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td><?= e($s['country']) ?></td>
                    <td class="num"><?= (int) $s['year_founded'] ?></td>
                    <td><?= e($s['studio_type']) ?></td>
                    <td><?php if ($s['website']): ?><a href="<?= e($s['website']) ?>" target="_blank" rel="noopener noreferrer">Visit</a><?php else: ?>—<?php endif; ?></td>
                    <td class="num"><span class="badge released"><?= (int) $s['games_count'] ?></span></td>
                    <td class="who"><?= e($s['added_by_name'] ?? 'System') ?><small><?= e(date('M j, Y g:i A', strtotime($s['date_added']))) ?></small></td>
                    <td class="who"><?= e($s['updated_by_name'] ?? 'System') ?><small><?= e(date('M j, Y g:i A', strtotime($s['last_updated']))) ?></small></td>
                    <td class="actions">
                        <div class="row-actions">
                            <a class="btn small" href="studio.php?id=<?= (int) $s['studio_id'] ?>">Games</a>
                            <a class="btn small" href="editStudio.php?id=<?= (int) $s['studio_id'] ?>">Edit</a>
                            <a class="btn small danger" href="deleteStudio.php?id=<?= (int) $s['studio_id'] ?>">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'views/footer.php'; ?>
