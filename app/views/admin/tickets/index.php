<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Dumping tickets</h1>
<nav class="filters">
    <a class="<?= $filter === '' ? 'active' : '' ?>" href="<?= eco_e(eco_url('admin/tickets')) ?>">All</a>
    <?php foreach ($statuses as $s): ?>
        <a class="<?= $filter === $s ? 'active' : '' ?>" href="<?= eco_e(eco_url('admin/tickets?status=' . rawurlencode($s))) ?>"><?= eco_e($s) ?></a>
    <?php endforeach; ?>
</nav>
<?php if (empty($reports)): ?>
    <p class="muted">No tickets match this filter.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>#</th><th>Filed</th><th>Resident</th><th>Location</th><th>Status</th><th>Assigned</th></tr></thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
            <tr>
                <td><a href="<?= eco_e(eco_url('admin/tickets/' . (int) $r['report_id'])) ?>">#<?= (int) $r['report_id'] ?></a></td>
                <td><?= eco_e($r['created_at']) ?></td>
                <td><?= eco_e($r['resident_name']) ?></td>
                <td><?= eco_e($r['location_text']) ?></td>
                <td><span class="badge badge-<?= eco_e(strtolower(str_replace(' ', '-', $r['status']))) ?>"><?= eco_e($r['status']) ?></span></td>
                <td><?= eco_e($r['assignee_name'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
