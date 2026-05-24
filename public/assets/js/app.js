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

  window.addEventListener("scroll", function () {
    const navbar = document.querySelector(".lk-navbar");
    if (navbar) {
      if (window.scrollY > 50) {
        navbar.classList.add("lk-navbar-scrolled");
      } else {
        navbar.classList.remove("lk-navbar-scrolled");
      }
    }
  });

  /* Navbar search overlay */
  const overlay = document.getElementById("lkSearchOverlay");
  const toggle = document.getElementById("lkSearchToggle");
  const closeBtn = document.getElementById("lkSearchClose");
  const input = document.getElementById("lkSearchInput");
  const results = document.getElementById("lkSearchResults");
  const viewAll = document.getElementById("lkSearchViewAll");
  const apiUrl = overlay && overlay.dataset.api;
  let searchTimer = null;

  function openSearch() {
    if (!overlay) return;
    overlay.classList.add("is-open");
    overlay.setAttribute("aria-hidden", "false");
    toggle?.setAttribute("aria-expanded", "true");
    document.body.style.overflow = "hidden";
    setTimeout(() => input?.focus(), 200);
  }

  function closeSearch() {
    if (!overlay) return;
    overlay.classList.remove("is-open");
    overlay.setAttribute("aria-hidden", "true");
    toggle?.setAttribute("aria-expanded", "false");
    document.body.style.overflow = "";
    if (results) results.innerHTML = "";
    if (viewAll) viewAll.style.display = "none";
  }

  function renderResults(groups, query) {
    if (!results) return;
    if (!groups || !groups.length) {
      results.innerHTML = '<p class="text-white text-center small py-3">No results found.</p>';
      if (viewAll) viewAll.style.display = "none";
      return;
    }
    let html = "";
    groups.forEach((g) => {
      html += '<div class="lk-search-group"><h6>' + escapeHtml(g.name) + "</h6>";
      g.items.forEach((item) => {
        html +=
          '<a href="' +
          escapeHtml(item.url) +
          '"><strong>' +
          escapeHtml(item.label) +
          "</strong><span class=\"d-block small text-muted\">" +
          escapeHtml(item.meta || "") +
          "</span></a>";
      });
      html += "</div>";
    });
    results.innerHTML = html;
    if (viewAll && query) {
      viewAll.href = (document.body.dataset.baseUrl || "/likhalokal/public/") + "search.php?q=" + encodeURIComponent(query);
      viewAll.style.display = "inline";
    }
  }

  function escapeHtml(s) {
    return (s || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  async function runSearch(q) {
    if (!apiUrl || q.length < 2) {
      if (results) results.innerHTML = "";
      if (viewAll) viewAll.style.display = "none";
      return;
    }
    const returnTo = encodeURIComponent(window.location.pathname + window.location.search);
    const res = await fetch(apiUrl + "?q=" + encodeURIComponent(q) + "&return=" + returnTo, { credentials: "same-origin" });
    const json = await res.json();
    if (json.success) {
      renderResults(json.data.groups || [], q);
    }
  }

  toggle?.addEventListener("click", function (e) {
    e.preventDefault();
    if (overlay?.classList.contains("is-open")) closeSearch();
    else openSearch();
  });
  closeBtn?.addEventListener("click", closeSearch);
  overlay?.addEventListener("click", function (e) {
    if (e.target === overlay) closeSearch();
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeSearch();
  });
  input?.addEventListener("input", function () {
    const q = input.value.trim();
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => runSearch(q), 280);
  });

  /* Global Scroll Reveal Observer */
  function initScrollReveal() {
    const revealEls = document.querySelectorAll(".reveal, .reveal-left, .reveal-right, .reveal-scale");
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0,
      rootMargin: "0px 0px 100px 0px"
    });
    revealEls.forEach((el) => observer.observe(el));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initScrollReveal);
  } else {
    initScrollReveal();
  }
})();

