<?php
// activity.php - the audit trail: WHO inserted, updated or deleted what, and WHEN
require_once 'core/dbConfig.php';
require_once 'core/models.php';
require_once 'core/auth.php';
requireLogin();

$log = getRecentActivity($pdo, 100);
$pageTitle = 'Activity log';
require 'views/header.php';
?>
<section class="card">
    <h2>Activity log</h2>
    <p class="muted">The 100 most recent actions. Deleted records are traceable here too.</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Record</th><th>Details</th></tr></thead>
            <tbody>
            <?php if (!$log): ?><tr><td colspan="5" class="empty">No activity yet.</td></tr><?php endif; ?>
            <?php foreach ($log as $row): ?>
                <tr>
                    <td class="nowrap"><?= e(date('M j, Y g:i:s A', strtotime($row['created_at']))) ?></td>
                    <td><?= e($row['username']) ?></td>
                    <td><span class="badge act-<?= strtolower(e($row['action'])) ?>"><?= e($row['action']) ?></span></td>
                    <td><?= e($row['entity']) ?><?= $row['entity_id'] ? ' #' . (int) $row['entity_id'] : '' ?></td>
                    <td><?= e($row['details']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require 'views/footer.php'; ?>
