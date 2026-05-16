/* Admin dashboard small helpers — Chart.js loaded from CDN on dashboard page */
document.querySelectorAll("[data-confirm]").forEach((btn) => {
  btn.addEventListener("click", function (e) {
    if (!confirm(btn.getAttribute("data-confirm"))) {
      e.preventDefault();
    }
  });
});
