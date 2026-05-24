<?php

declare(strict_types=1);
?>
<?php if (empty($hidePublicFooter)): ?>
<!-- Premium Footer Styles -->
<style>
.lk-custom-footer {
    background: linear-gradient(135deg, #00152b 0%, #001F3F 100%) !important;
    border-top: 3px solid #f39200 !important;
}
.lk-custom-footer .footer-logo-container {
    transition: transform 0.3s ease;
}
.lk-custom-footer .footer-logo-container:hover {
    transform: scale(1.05);
}
.lk-custom-footer .footer-nav-link {
    font-family: 'Montserrat', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6) !important;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    padding-bottom: 2px;
}
.lk-custom-footer .footer-nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #f39200;
    transition: width 0.3s ease;
}
.lk-custom-footer .footer-nav-link:hover {
    color: #ffffff !important;
}
.lk-custom-footer .footer-nav-link:hover::after {
    width: 100%;
}
.lk-custom-footer .footer-social-link {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: rgba(255, 255, 255, 0.6) !important;
    font-size: 0.95rem;
    transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.lk-custom-footer .footer-social-link:hover {
    background: #f39200;
    color: #00152b !important;
    border-color: #f39200;
    transform: translateY(-4px) scale(1.1);
    box-shadow: 0 6px 16px rgba(243, 146, 0, 0.35);
}
</style>

<footer class="text-white py-4 mt-auto position-relative lk-custom-footer" style="overflow: hidden;">
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            
            <!-- Left: Logo & Copyright -->
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white px-3 py-1 rounded-pill shadow-sm footer-logo-container">
                    <img src="<?= asset_url('images/likhalokal-logo.png') ?>" alt="LikhaLokal Logo" style="height: 30px; object-fit: contain;">
                </div>
                <span class="small text-white-50 fw-bold" style="font-family: 'Montserrat', sans-serif; letter-spacing: 1px;">&copy; <?= date('Y') ?> LikhaLokal</span>
            </div>

            <!-- Center: Links -->
            <div class="d-flex flex-wrap justify-content-center gap-4">
                <a href="<?= e(BASE_URL) ?>index.php" class="footer-nav-link">Home</a>
                <a href="<?= e(BASE_URL) ?>tourism.php" class="footer-nav-link">Tourism</a>
                <a href="<?= e(BASE_URL) ?>products.php" class="footer-nav-link">Products</a>
                <a href="<?= e(BASE_URL) ?>about.php" class="footer-nav-link">About</a>
                <a href="<?= e(BASE_URL) ?>team.php" class="footer-nav-link">Our Team</a>
            </div>

            <!-- Right: Socials -->
            <div class="d-flex gap-3">
                <a href="#" class="footer-social-link" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="footer-social-link" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" class="footer-social-link" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="#" class="footer-social-link" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="#" class="footer-social-link" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>

        </div>
    </div>
</footer>
<?php endif; ?>
<div class="modal fade" id="guestAuthModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login required</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Please login or register to continue.</div>
      <div class="modal-footer">
        <?php
        $guestReturn = $_SERVER['REQUEST_URI'] ?? '';
        $guestLoginUrl = function_exists('login_url_with_redirect') && is_safe_post_login_redirect($guestReturn)
            ? login_url_with_redirect($guestReturn)
            : BASE_URL . 'login.php';
        ?>
        <a href="<?= e($guestLoginUrl) ?>" class="btn btn-primary">Login</a>
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
