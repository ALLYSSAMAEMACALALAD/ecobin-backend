<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>Reports</h1>
<div class="kpis">
    <div class="kpi"><span>Total reports</span><strong><?= (int) $total ?></strong></div>
    <div class="kpi"><span>Open</span><strong><?= (int) $counts['Open'] ?></strong></div>
    <div class="kpi"><span>In progress</span><strong><?= (int) $counts['In Progress'] ?></strong></div>
    <div class="kpi"><span>Resolved</span><strong><?= (int) $counts['Resolved'] ?> <small>(<?= (int) $resolved_pct ?>%)</small></strong></div>
</div>
<section class="card">
    <h2>Download</h2>
    <p class="muted">Export every dumping report for the municipal office.</p>
    <div class="row">
        <a class="btn" href="<?= eco_e(eco_url('admin/reports/export.pdf')) ?>">Download PDF</a>
        <a class="btn btn-outline" href="<?= eco_e(eco_url('admin/reports/export.csv')) ?>">Download CSV (Excel)</a>
    </div>
</section>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
