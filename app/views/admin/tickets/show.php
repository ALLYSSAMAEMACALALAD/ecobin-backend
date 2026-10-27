<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Ticket #<?= (int) $report['report_id'] ?></h1>
<div class="two-col">
    <section>
        <dl class="details">
            <dt>Status</dt><dd><span class="badge badge-<?= eco_e(strtolower(str_replace(' ', '-', $report['status']))) ?>"><?= eco_e($report['status']) ?></span></dd>
            <dt>Filed</dt><dd><?= eco_e($report['created_at']) ?></dd>
            <dt>Resident</dt><dd><?= eco_e($report['resident_name']) ?> (<?= eco_e($report['resident_email']) ?>)</dd>
            <dt>Location</dt><dd><?= eco_e($report['location_text']) ?></dd>
            <dt>Coordinates</dt><dd><?= $report['latitude'] !== null ? eco_e($report['latitude'] . ', ' . $report['longitude']) : '<span class="muted">not given</span>' ?>
                <?php if ($report['latitude'] !== null): ?>
                    &middot; <a target="_blank" rel="noopener" href="https://www.google.com/maps?q=<?= eco_e($report['latitude'] . ',' . $report['longitude']) ?>">Map</a>
                <?php endif; ?>
            </dd>
            <dt>Assigned to</dt><dd><?= eco_e($report['assignee_name'] ?? 'Not yet assigned') ?></dd>
            <dt>Description</dt><dd><?= nl2br(eco_e($report['description'])) ?></dd>
        </dl>
        <h2>Update status</h2>
        <form method="post" action="<?= eco_e(eco_url('admin/tickets/' . (int) $report['report_id'] . '/status')) ?>" class="form inline-form">
            <select name="status">
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= eco_e($s) ?>" <?= $s === $report['status'] ? 'selected' : '' ?>><?= eco_e($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Save status</button>
        </form>
        <p class="muted small">Saving a status queues a notification to the resident. SMS and email sending needs a provider connected (see README).</p>
        <p><a href="<?= eco_e(eco_url('admin/tickets')) ?>">&larr; Back to tickets</a></p>
    </section>
    <section>
        <h2>Photo evidence</h2>
        <?php if (!empty($report['photo_url'])): ?>
            <a href="<?= eco_e(eco_url($report['photo_url'])) ?>" target="_blank" rel="noopener">
                <img class="photo" src="<?= eco_e(eco_url($report['photo_url'])) ?>" alt="Photo evidence for ticket #<?= (int) $report['report_id'] ?>">
            </a>
        <?php else: ?>
            <p class="muted">No photo attached.</p>
        <?php endif; ?>
    </section>
</div>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
