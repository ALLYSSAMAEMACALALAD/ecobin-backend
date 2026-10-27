<?php include APP_DIR . 'views/partials/header.php'; ?>
<h1>My Eco-ID</h1>
<section class="eco-card">
    <div class="eco-qr">
        <div id="eco-qr" class="qr-box" role="img" aria-label="QR code for <?= eco_e($eco_id) ?>"></div>
        <p class="muted small">Show this at the drop-off hub to get points.</p>
    </div>
    <div class="eco-info">
        <p class="muted">Resident</p>
        <p class="eco-name"><?= eco_e($me['full_name'] ?? '') ?></p>
        <p class="muted">Eco-ID</p>
        <p class="eco-id"><?= eco_e($eco_id) ?></p>
        <p class="muted">Points balance</p>
        <p><strong><?= number_format((int) ($me['points_balance'] ?? 0)) ?> pts</strong></p>
    </div>
</section>
<script src="<?= eco_e(eco_url('assets/js/qrcode.min.js')) ?>"></script>
<script>
  (function () {
    var box = document.getElementById('eco-qr');
    var value = <?= json_encode($eco_id) ?>;
    if (typeof QRCode === 'undefined') {
      box.insertAdjacentHTML('afterend', '<p class="muted">QR library did not load. Use the Eco-ID above.</p>');
      return;
    }
    new QRCode(box, { text: value, width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
  })();
</script>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
