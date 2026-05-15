<?php

declare(strict_types=1);
?>
<footer class="text-white py-4 mt-auto position-relative" style="background: linear-gradient(to right, #1b4332, #0d2119); overflow: hidden;">
    <!-- Abstract background shape -->
    <div class="position-absolute rounded-circle" style="background: #f39200; width: 150px; height: 150px; bottom: -50px; right: 5%; opacity: 0.1; filter: blur(20px);"></div>

    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            
            <!-- Left: Logo & Copyright -->
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-3 py-1 rounded-pill shadow-sm">
                    <img src="<?= asset_url('images/likhalokal-logo.png') ?>" alt="LikhaLokal Logo" style="height: 30px; object-fit: contain;">
                </div>
                <span class="small text-white-50 fw-bold" style="font-family: 'Montserrat', sans-serif; letter-spacing: 1px;">&copy; <?= date('Y') ?> LikhaLokal</span>
            </div>

            <!-- Center: Links -->
            <div class="d-flex flex-wrap justify-content-center gap-4">
                <a href="<?= e(BASE_URL) ?>index.php" class="text-white-50 text-decoration-none fw-medium hover-white" style="transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">Home</a>
                <a href="<?= e(BASE_URL) ?>tourism.php" class="text-white-50 text-decoration-none fw-medium hover-white" style="transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">Tourism</a>
                <a href="<?= e(BASE_URL) ?>products.php" class="text-white-50 text-decoration-none fw-medium hover-white" style="transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">Products</a>
                <a href="<?= e(BASE_URL) ?>about.php" class="text-white-50 text-decoration-none fw-medium hover-white" style="transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">About</a>
                <a href="<?= e(BASE_URL) ?>team.php" class="text-white-50 text-decoration-none fw-medium hover-white" style="transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">Our Team</a>
            </div>

            <!-- Right: Socials -->
            <div class="d-flex gap-3">
                <a href="#" class="text-white-50 text-decoration-none fs-5" style="transition: color 0.3s;" onmouseover="this.style.color='#f39200'" onmouseout="this.style.color='rgba(255,255,255,0.5)'" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="text-white-50 text-decoration-none fs-5" style="transition: color 0.3s;" onmouseover="this.style.color='#f39200'" onmouseout="this.style.color='rgba(255,255,255,0.5)'" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="text-white-50 text-decoration-none fs-5" style="transition: color 0.3s;" onmouseover="this.style.color='#f39200'" onmouseout="this.style.color='rgba(255,255,255,0.5)'" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" class="text-white-50 text-decoration-none fs-5" style="transition: color 0.3s;" onmouseover="this.style.color='#f39200'" onmouseout="this.style.color='rgba(255,255,255,0.5)'" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="#" class="text-white-50 text-decoration-none fs-5" style="transition: color 0.3s;" onmouseover="this.style.color='#f39200'" onmouseout="this.style.color='rgba(255,255,255,0.5)'" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>

        </div>
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
