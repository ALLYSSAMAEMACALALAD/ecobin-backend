<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Drop-offs</h1>
<div class="two-col">
    <section>
        <h2>Record a drop-off</h2>
        <?php if ($errors): ?>
            <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= eco_e($err) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form method="post" action="<?= eco_e(eco_url('admin/dropoffs')) ?>" class="form">
            <label>Resident Eco-ID (from the QR)
                <input type="text" name="eco_id" placeholder="ECOID-0003" maxlength="12" required value="<?= eco_e($values['eco_id'] ?? '') ?>" style="text-transform:uppercase">
            </label>
            <label>Collection point
                <select name="point_id" required>
                    <option value="">-- choose --</option>
                    <?php foreach ($points as $p): ?>
                        <option value="<?= (int) $p['point_id'] ?>" <?= ((string) ($values['point_id'] ?? '') === (string) $p['point_id']) ? 'selected' : '' ?>><?= eco_e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Material
                <select name="material" required>
                    <option value="">-- choose --</option>
                    <?php foreach ($rates as $mat => $rate): ?>
                        <option value="<?= eco_e($mat) ?>" <?= (($values['material'] ?? '') === $mat) ? 'selected' : '' ?>><?= eco_e($mat) ?> (<?= (int) $rate ?> pts/kg)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Weight (kg)
                <input type="number" name="weight" min="0.01" max="500" step="0.01" required value="<?= eco_e($values['weight'] ?? '') ?>">
            </label>
            <button type="submit" class="btn">Verify and credit points</button>
        </form>
    </section>
    <section>
        <h2>Points rates</h2>
        <table class="table">
            <thead><tr><th>Material</th><th>Points per kg</th></tr></thead>
            <tbody>
            <?php foreach ($rates as $mat => $rate): ?>
                <tr><td><?= eco_e($mat) ?></td><td><?= (int) $rate ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="muted small">Points = weight (kg) &times; rate, rounded to the nearest point.</p>
    </section>
</div>

<h2>Recent drop-offs</h2>
<?php if (empty($logs)): ?>
    <p class="muted">No drop-offs recorded yet.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th>When</th><th>Resident</th><th>Eco-ID</th><th>Point</th><th>Material</th><th>kg</th><th>Points</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $l): ?>
            <tr>
                <td><?= eco_e($l['logged_at']) ?></td>
                <td><?= eco_e($l['resident_name']) ?></td>
                <td><?= eco_e($l['qr_value']) ?></td>
                <td><?= eco_e($l['point_name']) ?></td>
                <td><?= eco_e($l['material_type']) ?></td>
                <td><?= eco_e($l['weight_kg']) ?></td>
                <td>+<?= (int) $l['points_awarded'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
