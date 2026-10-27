<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Audit logs</h1>
<h2>Recent actions</h2>
<?php if (empty($logs)): ?>
    <p class="muted">No actions recorded yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>When</th><th>By</th><th>Action</th><th>Table</th><th>Record</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
            <tr>
                <td><?= eco_e($l['created_at']) ?></td>
                <td><?= eco_e($l['actor'] ?? 'System') ?></td>
                <td><?= eco_e($l['action']) ?></td>
                <td><?= eco_e($l['table_name'] ?? '-') ?></td>
                <td><?= $l['record_id'] !== null ? (int) $l['record_id'] : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Notification queue</h2>
<?php if (empty($notifications)): ?>
    <p class="muted">No notifications queued yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>#</th><th>Recipient</th><th>Channel</th><th>Message</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($notifications as $n): ?>
            <tr>
                <td><?= (int) $n['notif_id'] ?></td>
                <td><?= eco_e($n['recipient']) ?></td>
                <td><?= eco_e($n['channel']) ?></td>
                <td><?= eco_e($n['message']) ?></td>
                <td><?= eco_e($n['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
