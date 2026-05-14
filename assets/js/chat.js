/**
 * Chat polling for message.php
 */
(function () {
  const cfg = window.LK_CHAT;
  if (!cfg || !cfg.businessId) return;

  const listEl = document.getElementById("chatMessages");
  const form = document.getElementById("chatForm");
  const input = document.getElementById("chatInput");
  const csrf = cfg.csrf || "";

  function escapeHtml(s) {
    return (s || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function render(messages) {
    if (!listEl) return;
    listEl.innerHTML = "";
    messages.forEach((m) => {
      const mine = String(m.sender_id) === String(cfg.me);
      const wrap = document.createElement("div");
      wrap.className = "mb-3 d-flex " + (mine ? "justify-content-end" : "justify-content-start");
      const bubble = document.createElement("div");
      bubble.className = "p-3 shadow-sm " + (mine ? "chat-bubble-out" : "chat-bubble-in");
      bubble.style.maxWidth = "78%";
      bubble.innerHTML =
        "<div>" +
        escapeHtml(m.message_content).replace(/\n/g, "<br>") +
        '</div><div class="small mt-1 ' +
        (mine ? "text-white-50" : "text-muted") +
        '">' +
        escapeHtml(m.created_at) +
        "</div>";
      wrap.appendChild(bubble);
      listEl.appendChild(wrap);
    });
    listEl.scrollTop = listEl.scrollHeight;
  }

  async function load() {
    const url =
      "../api/messages.php?business_id=" +
      encodeURIComponent(cfg.businessId);
    const res = await fetch(url, { credentials: "same-origin" });
    const json = await res.json();
    if (json.success) {
      render(json.data || []);
      await fetch("../api/messages.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "mark_read", business_id: cfg.businessId, csrf_token: csrf }),
      });
    }
  }

  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const text = (input && input.value) || "";
      if (!text.trim()) return;
      const body = {
        action: "send",
        business_id: cfg.businessId,
        product_id: cfg.productId || null,
        receiver_id: cfg.receiverId || null,
        message_content: text.trim(),
        csrf_token: csrf,
      };
      const res = await fetch("../api/messages.php", {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });
      const json = await res.json();
      if (json.success) {
        input.value = "";
        load();
      } else {
        alert(json.message || "Unable to send");
      }
    });
  }

  load();
  setInterval(load, 3000);
})();
