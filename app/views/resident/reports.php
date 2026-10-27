<?php include APP_DIR . 'views/partials/header.php'; ?>
<div class="row between">
    <h1>My reports</h1>
    <a class="btn" href="<?= eco_e(eco_url('resident/reports/new')) ?>">+ Report dumping</a>
</div>
<?php if (empty($reports)): ?>
    <p class="muted">You have not filed any reports yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>Filed</th><th>Location</th><th>Description</th><th>Status</th><th>Photo</th></tr></thead>
        <tbody>
        <?php foreach ($reports as $row): ?>
            <tr>
                <td><?= eco_e(date('M j, Y g:i A', strtotime($row['created_at']))) ?></td>
                <td><?= eco_e($row['location_text']) ?></td>
                <td><?= eco_e(mb_strimwidth($row['description'], 0, 90, '...')) ?></td>
                <td><span class="badge badge-<?= eco_e(strtolower(str_replace(' ', '-', $row['status']))) ?>"><?= eco_e($row['status']) ?></span></td>
                <td>
                    <?php if (!empty($row['photo_url'])): ?>
                        <a href="<?= eco_e(eco_url($row['photo_url'])) ?>" target="_blank" rel="noopener">View</a>
                    <?php else: ?>
                        <span class="muted">None</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
