<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Hi, <?= eco_e($me['full_name'] ?? '') ?></h1>
<div class="cards">
    <article class="card stat">
        <span class="muted">Points balance</span>
        <strong><?= number_format((int) ($me['points_balance'] ?? 0)) ?> pts</strong>
    </article>
</div>
<h2>Upcoming collections</h2>
<?php if (empty($upcoming)): ?>
    <p class="muted">No upcoming collections yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Date</th><th>Time</th><th>Point</th><th>Waste type</th></tr></thead>
        <tbody>
        <?php foreach ($upcoming as $row): ?>
            <tr>
                <td><?= eco_e($row['collection_date']) ?></td>
                <td><?= eco_e($row['time_window']) ?></td>
                <td><?= eco_e($row['point_name']) ?></td>
                <td><?= eco_e($row['waste_type']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
