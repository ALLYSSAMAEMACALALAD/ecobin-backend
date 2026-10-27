<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Admin dashboard</h1>
<div class="kpis">
    <a class="kpi" href="<?= eco_e(eco_url('admin/tickets?status=Open')) ?>"><span>Open reports</span><strong><?= (int) $open_reports ?></strong></a>
    <a class="kpi" href="<?= eco_e(eco_url('admin/tickets?status=In%20Progress')) ?>"><span>In progress</span><strong><?= (int) $in_progress ?></strong></a>
    <a class="kpi" href="<?= eco_e(eco_url('admin/redemptions')) ?>"><span>Pending redemptions</span><strong><?= (int) $pending_redeem ?></strong></a>
    <div class="kpi"><span>Residents</span><strong><?= (int) $residents ?></strong></div>
    <div class="kpi"><span>Points awarded</span><strong><?= number_format((int) $points_total) ?></strong></div>
    <div class="kpi"><span>Resolved reports</span><strong><?= (int) $resolved ?></strong></div>
</div>

<div class="two-col">
    <section>
        <h2>Recent dumping reports</h2>
        <?php if (empty($recent_reports)): ?>
            <p class="muted">No reports yet.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>#</th><th>Location</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recent_reports as $r): ?>
                    <tr>
                        <td><a href="<?= eco_e(eco_url('admin/tickets/' . (int) $r['report_id'])) ?>">#<?= (int) $r['report_id'] ?></a></td>
                        <td><?= eco_e($r['location_text']) ?></td>
                        <td><span class="badge badge-<?= eco_e(strtolower(str_replace(' ', '-', $r['status']))) ?>"><?= eco_e($r['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section>
        <h2>Collections in the next 7 days</h2>
        <?php if (empty($upcoming)): ?>
            <p class="muted">No collections scheduled this week.</p>
        <?php else: ?>
            <table class="table">
                <thead><tr><th>Date</th><th>Time</th><th>Point</th></tr></thead>
                <tbody>
                <?php foreach ($upcoming as $s): ?>
                    <tr>
                        <td><?= eco_e($s['collection_date']) ?></td>
                        <td><?= eco_e($s['time_window']) ?></td>
                        <td><?= eco_e($s['point_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

<h2>Collection points and map</h2>
<table class="table">
    <thead><tr><th>Name</th><th>Type</th><th>Coordinates</th><th>Map</th></tr></thead>
    <tbody>
    <?php foreach ($points as $p): ?>
        <tr>
            <td><?= eco_e($p['name']) ?></td>
            <td><?= eco_e($p['point_type']) ?></td>
            <td><?= $p['latitude'] !== null ? eco_e($p['latitude'] . ', ' . $p['longitude']) : '<span class="muted">not set</span>' ?></td>
            <td>
                <?php if ($p['latitude'] !== null): ?>
                    <a target="_blank" rel="noopener" href="https://www.google.com/maps?q=<?= eco_e($p['latitude'] . ',' . $p['longitude']) ?>">Open in Google Maps</a>
                <?php else: ?>
                    <span class="muted">-</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
