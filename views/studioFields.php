<?php
// Form fields for the PARENT (studio). Expects $values and $errors.
$field = fn(string $n, $default = '') => $values[$n] ?? $default;
$error = fn(string $n) => isset($errors[$n]) ? '<small class="error">' . e($errors[$n]) . '</small>' : '';
?>
<div class="grid">
    <label>Studio name *
        <input type="text" name="studio_name" maxlength="100" required value="<?= e($field('studio_name')) ?>"><?= $error('studio_name') ?>
    </label>
    <label>Founder name *
        <input type="text" name="founder_name" maxlength="100" required value="<?= e($field('founder_name')) ?>"><?= $error('founder_name') ?>
    </label>
    <label>Email *
        <input type="email" name="email" maxlength="100" required value="<?= e($field('email')) ?>"><?= $error('email') ?>
    </label>
    <label>Country *
        <input type="text" name="country" maxlength="60" required value="<?= e($field('country')) ?>"><?= $error('country') ?>
    </label>
    <label>Year founded *
        <input type="number" name="year_founded" min="1970" max="<?= date('Y') ?>" required value="<?= e($field('year_founded', date('Y'))) ?>"><?= $error('year_founded') ?>
    </label>
    <label>Studio type *
        <select name="studio_type" required>
            <option value="">-- choose --</option>
            <?php foreach (STUDIO_TYPES as $o): ?>
                <option <?= $field('studio_type') === $o ? 'selected' : '' ?>><?= e($o) ?></option>
            <?php endforeach; ?>
        </select><?= $error('studio_type') ?>
    </label>
    <label>Website
        <input type="url" name="website" maxlength="255" placeholder="https://example.com" value="<?= e($field('website')) ?>"><?= $error('website') ?>
    </label>
</div>
