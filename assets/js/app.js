/**
 * LikhaLokal — shared front-end helpers
 */
(function () {
  const root = document.body.dataset.apiBase || "../api/";

  window.LikhaLokal = {
    apiBase: root,
    guestModal: function () {
      const el = document.getElementById("guestAuthModal");
      if (el && window.bootstrap) {
        new bootstrap.Modal(el).show();
      } else {
        alert("Please login or register to continue.");
      }
    },
    fetchJson: async function (url, options = {}) {
      const res = await fetch(url, {
        credentials: "same-origin",
        headers: { "Content-Type": "application/json", ...(options.headers || {}) },
        ...options,
      });
      const data = await res.json().catch(() => ({}));
      return { ok: res.ok, status: res.status, data };
    },
  };

  document.querySelectorAll("[data-require-auth]").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      if (document.body.dataset.loggedIn !== "1") {
        e.preventDefault();
        window.LikhaLokal.guestModal();
      }
    });
  });
})();
