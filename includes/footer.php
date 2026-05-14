<?php

declare(strict_types=1);
?>
<footer class="lk-footer text-white mt-5">
    <div class="container py-4 text-center">
        <div class="d-flex justify-content-center gap-3 mb-3 fs-4">
            <a href="#" class="text-white" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-white" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-white" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="text-white" aria-label="Phone"><i class="bi bi-telephone"></i></a>
            <a href="#" class="text-white" aria-label="Email"><i class="bi bi-envelope"></i></a>
        </div>
        <div class="small mb-2">
            <a class="text-white-50 text-decoration-none me-2" href="<?= e(BASE_URL) ?>index.php">Home</a>
            <a class="text-white-50 text-decoration-none me-2" href="<?= e(BASE_URL) ?>tourism.php">Tourism</a>
            <a class="text-white-50 text-decoration-none me-2" href="<?= e(BASE_URL) ?>products.php">Products</a>
            <a class="text-white-50 text-decoration-none me-2" href="<?= e(BASE_URL) ?>events.php">Events</a>
            <a class="text-white-50 text-decoration-none me-2" href="<?= e(BASE_URL) ?>cultural-info.php">Culture</a>
            <a class="text-white-50 text-decoration-none me-2" href="<?= e(BASE_URL) ?>about.php">About</a>
            <a class="text-white-50 text-decoration-none" href="<?= e(BASE_URL) ?>about.php#team">Our Team</a>
        </div>
        <p class="small text-white-50 mb-0">Copyright © 2025: Designed by Talisay-Vinzons Team, BSIT 2B - AY 25-26</p>
    </div>
</footer>
<div class="modal fade" id="guestAuthModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login required</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Please login or register to continue.</div>
      <div class="modal-footer">
        <a href="<?= e(BASE_URL) ?>login.php" class="btn btn-primary">Login</a>
        <a href="<?= e(BASE_URL) ?>register.php" class="btn btn-outline-secondary">Register</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(ASSET_URL) ?>js/app.js"></script>
<?php if (!empty($extraScripts)): ?>
    <?= $extraScripts ?>
<?php endif; ?>
</body>
</html>
