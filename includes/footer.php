<?php

declare(strict_types=1);
?>
<footer class="lk-footer text-white py-5 mt-5" style="background: var(--lk-navy);">
    <div class="container text-center">
        <h3 class="fw-bold mb-4" style="font-family: Impact, sans-serif; letter-spacing: 1px; color: var(--lk-orange);">LIKHALOKAL</h3>
        <div class="d-flex justify-content-center gap-4 mb-4 fs-4">
            <a href="#" class="text-white text-decoration-none border rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.color='var(--lk-navy)';" onmouseout="this.style.background='transparent'; this.style.color='#fff';" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="text-white text-decoration-none border rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.color='var(--lk-navy)';" onmouseout="this.style.background='transparent'; this.style.color='#fff';" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="text-white text-decoration-none border rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.color='var(--lk-navy)';" onmouseout="this.style.background='transparent'; this.style.color='#fff';" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="#" class="text-white text-decoration-none border rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.color='var(--lk-navy)';" onmouseout="this.style.background='transparent'; this.style.color='#fff';" aria-label="Phone"><i class="fa-solid fa-phone"></i></a>
            <a href="#" class="text-white text-decoration-none border rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#fff'; this.style.color='var(--lk-navy)';" onmouseout="this.style.background='transparent'; this.style.color='#fff';" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
        </div>
        <div class="mb-4">
            <a class="text-white text-decoration-none fw-bold me-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;" href="<?= e(BASE_URL) ?>index.php">Home</a>
            <a class="text-white text-decoration-none fw-bold me-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;" href="<?= e(BASE_URL) ?>tourism.php">Tourism</a>
            <a class="text-white text-decoration-none fw-bold me-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;" href="<?= e(BASE_URL) ?>products.php">Products</a>
            <a class="text-white text-decoration-none fw-bold me-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;" href="<?= e(BASE_URL) ?>events.php">Events</a>
            <a class="text-white text-decoration-none fw-bold text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;" href="<?= e(BASE_URL) ?>about.php">About</a>
        </div>
        <p class="small text-white-50 mb-0 fw-bold">Designed by Talisay-Vinzons Team</p>
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
<script src="<?= e(ASSET_URL) ?>js/app.js?v=<?= time() ?>"></script>
<?php if (!empty($extraScripts)): ?>
    <?= $extraScripts ?>
<?php endif; ?>
</body>
</html>
