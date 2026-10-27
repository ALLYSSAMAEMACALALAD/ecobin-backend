<?php include APP_DIR . 'views/partials/header.php'; ?>
<section class="narrow wide">
    <h1>Report illegal dumping</h1>
    <p class="muted">Describe what you saw, where it is, and add a clear photo. The municipal office will review your report.</p>
    <?php if ($errors): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $err): ?><li><?= eco_e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" action="<?= eco_e(eco_url('resident/reports/new')) ?>" enctype="multipart/form-data" class="form">
        <label>Description
            <textarea name="description" rows="4" maxlength="1000" required><?= eco_e($values['description'] ?? '') ?></textarea>
        </label>
        <label>Location (street or landmark)
            <input type="text" name="location" maxlength="255" value="<?= eco_e($values['location'] ?? '') ?>" required>
        </label>
        <div class="row">
            <button type="button" class="btn btn-outline" id="use-gps">Use my current location</button>
            <span class="muted small" id="gps-status"></span>
        </div>
        <input type="hidden" name="latitude" id="latitude" value="<?= eco_e($values['latitude'] ?? '') ?>">
        <input type="hidden" name="longitude" id="longitude" value="<?= eco_e($values['longitude'] ?? '') ?>">
        <label>Photo (JPG, PNG, or WEBP, max 5 MB)
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required>
        </label>
        <div class="row">
            <button type="submit" class="btn">Submit report</button>
            <a href="<?= eco_e(eco_url('resident/reports')) ?>">Cancel</a>
        </div>
    </form>
</section>
<script>
  document.getElementById('use-gps').addEventListener('click', function () {
    var status = document.getElementById('gps-status');
    if (!navigator.geolocation) { status.textContent = 'Location is not supported on this device.'; return; }
    status.textContent = 'Getting location...';
    navigator.geolocation.getCurrentPosition(function (pos) {
      document.getElementById('latitude').value = pos.coords.latitude.toFixed(7);
      document.getElementById('longitude').value = pos.coords.longitude.toFixed(7);
      status.textContent = 'Location saved.';
    }, function () {
      status.textContent = 'Could not get your location. You can still type the location above.';
    });
  });
</script>
<?php include APP_DIR . 'views/partials/footer.php'; ?>
