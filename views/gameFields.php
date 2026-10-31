<?php
// Form fields for the CHILD (game). Expects $values, $errors and $studioOptions.
// $lockedStudioId (optional): when set, the game is added to that studio (hidden field).
$field = fn(string $n, $default = '') => $values[$n] ?? $default;
$error = fn(string $n) => isset($errors[$n]) ? '<small class="error">' . e($errors[$n]) . '</small>' : '';
?>
<div class="grid">
    <?php if (isset($lockedStudioId)): ?>
        <input type="hidden" name="studio_id" value="<?= (int) $lockedStudioId ?>">
        <?= $error('studio_id') ?>
    <?php else: ?>
        <label>Studio (parent) *
            <select name="studio_id" required>
                <?php foreach ($studioOptions as $s): ?>
                    <option value="<?= (int) $s['studio_id'] ?>" <?= (int) $field('studio_id') === (int) $s['studio_id'] ? 'selected' : '' ?>><?= e($s['studio_name']) ?></option>
                <?php endforeach; ?>
            </select><?= $error('studio_id') ?>
        </label>
    <?php endif; ?>
    <label>Game title *
        <input type="text" name="title" maxlength="100" required value="<?= e($field('title')) ?>"><?= $error('title') ?>
    </label>
    <label>Genre *
        <select name="genre" required>
            <option value="">-- choose --</option>
            <?php foreach (GENRES as $o): ?><option <?= $field('genre') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
        </select><?= $error('genre') ?>
    </label>
    <label>Platform *
        <select name="platform" required>
            <option value="">-- choose --</option>
            <?php foreach (PLATFORMS as $o): ?><option <?= $field('platform') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
        </select><?= $error('platform') ?>
    </label>
    <label>Game engine *
        <select name="game_engine" required>
            <option value="">-- choose --</option>
            <?php foreach (GAME_ENGINES as $o): ?><option <?= $field('game_engine') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
        </select><?= $error('game_engine') ?>
    </label>
    <label>Status
        <select name="release_status">
            <?php foreach (GAME_STATUSES as $o): ?><option <?= $field('release_status', 'In Development') === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
        </select><?= $error('release_status') ?>
    </label>
    <label>Price ($) *
        <input type="number" name="price" min="0" max="9999.99" step="0.01" required value="<?= e($field('price', '0')) ?>"><?= $error('price') ?>
    </label>
    <label>Release date
        <input type="date" name="release_date" value="<?= e($field('release_date')) ?>"><?= $error('release_date') ?>
    </label>
</div>
