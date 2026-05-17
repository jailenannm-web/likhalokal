/**
 * Local user inbox chat (user/messages.php)
 */
(function () {
  const cfg = window.LK_USER_CHAT;
  if (!cfg || !cfg.businessId) return;

  const listEl = document.getElementById("lkUserChatMessages");
  const form = document.getElementById("lkUserChatForm");
  const input = document.getElementById("lkUserChatInput");
  const apiUrl = cfg.apiUrl;
  const tag = "div";

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
    if (!messages.length) {
      listEl.innerHTML =
        '<p class="text-center text-muted small py-4">No messages yet. Say hello to the seller!</p>';
      return;
    }
    messages.forEach((m) => {
      const mine = String(m.sender_id) === String(cfg.me);
      const wrap = document.createElement(tag);
      wrap.className = "mb-3 d-flex " + (mine ? "justify-content-end" : "justify-content-start");
      const bubble = document.createElement(tag);
      bubble.className = "p-3 shadow-sm " + (mine ? "lk-bubble-out" : "lk-bubble-in");
      bubble.style.maxWidth = "78%";
      const body = document.createElement(tag);
      body.style.fontSize = "0.95rem";
      body.innerHTML = escapeHtml(m.message_content).replace(/\n/g, "<br>");
      const time = document.createElement(tag);
      time.className = "small mt-1 opacity-75";
      time.textContent = m.created_at || "";
      bubble.appendChild(body);
      bubble.appendChild(time);
      wrap.appendChild(bubble);
      listEl.appendChild(wrap);
    });
    listEl.scrollTop = listEl.scrollHeight;
  }

  async function load() {
    const url = apiUrl + "?business_id=" + encodeURIComponent(cfg.businessId);
    const res = await fetch(url, { credentials: "same-origin" });
    const json = await res.json();
    if (json.success) {
      render(json.data || []);
      await fetch(apiUrl, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "mark_read",
          business_id: cfg.businessId,
          receiver_id: cfg.receiverId,
          csrf_token: cfg.csrf,
        }),
      });
    }
  }

  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const text = (input && input.value) || "";
      if (!text.trim()) return;
      const res = await fetch(apiUrl, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "send",
          business_id: cfg.businessId,
          receiver_id: cfg.receiverId,
          message_content: text.trim(),
          csrf_token: cfg.csrf,
        }),
      });
      const json = await res.json();
      if (json.success) {
        input.value = "";
        load();
      } else {
        alert(json.message || "Unable to send message");
      }
    });
  }

  load();
  setInterval(load, 4000);
})();
