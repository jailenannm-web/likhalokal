/**
 * Chat polling for message.php — delegates to lk-chat.js when present.
 */
(function () {
  if (window.LK_CHAT && document.querySelector('script[src*="lk-chat.js"]')) {
    return;
  }
  const cfg = window.LK_CHAT;
  if (!cfg || !cfg.businessId) return;

  const listEl = document.getElementById("chatMessages");
  const form = document.getElementById("chatForm");
  const input = document.getElementById("chatInput");
  const fileInput = document.getElementById("chatAttachment");
  const csrf = cfg.csrf || "";
  const apiUrl = cfg.apiUrl || "../api/messages.php";

  function getAppBase() {
    if (cfg.appBase) return String(cfg.appBase).replace(/\/$/, "");
    const parts = window.location.pathname.split("/").filter(Boolean);
    return parts.length ? "/" + parts[0] : "";
  }

  function normalizeAttachmentPath(path) {
    if (!path) return "";
    let p = String(path).replace(/\\/g, "/");
    p = p.replace(/^.*htdocs\/likhalokal\//i, "");
    p = p.replace(/^\/?likhalokal\//i, "");
    p = p.replace(/^\/+/, "");
    if (!p.startsWith("assets/")) {
      p = p.startsWith("uploads/") ? "assets/" + p : "assets/uploads/messages/" + p.replace(/^.*\//, "");
    }
    return p;
  }

  function buildAttachmentUrl(m) {
    if (m.attachment_url) return m.attachment_url;
    const p = normalizeAttachmentPath(m.attachment_path);
    if (!p) return null;
    return getAppBase() + "/" + p;
  }

  function escapeHtml(s) {
    return (s || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function isImageAttachment(m) {
    const t = (m.attachment_type || "").toLowerCase();
    if (t === "image" || t.indexOf("image/") === 0) return true;
    return /\.(jpe?g|png|gif|webp)$/i.test(m.attachment_path || "");
  }

  function render(messages) {
    if (!listEl) return;
    listEl.innerHTML = "";
    messages.forEach((m) => {
      const mine = String(m.sender_id) === String(cfg.me);
      const wrap = document.createElement("div");
      wrap.className = "mb-3 d-flex " + (mine ? "justify-content-end" : "justify-content-start");
      const bubble = document.createElement("div");
      bubble.className = "p-3 shadow-sm " + (mine ? "chat-bubble-out is-mine" : "chat-bubble-in");
      bubble.style.maxWidth = "78%";
      let html = "";
      const text = (m.message_content || "").trim();
      if (text && !/^attachment\.?$/i.test(text)) {
        html +=
          "<div style='font-family: Poppins, sans-serif; font-size: 0.95rem;'>" +
          escapeHtml(m.message_content).replace(/\n/g, "<br>") +
          "</div>";
      }
      if (m.attachment_path || m.attachment_url) {
        const url = buildAttachmentUrl(m);
        if (url && isImageAttachment(m)) {
          html +=
            '<img src="' +
            escapeHtml(url) +
            '" class="lk-chat-attachment-img" alt="Message attachment" loading="lazy" onerror="console.warn(\'Attachment image failed:\', this.src); this.outerHTML=\'<span class=small text-muted>Image could not be loaded</span>\';">';
        } else if (url) {
          html +=
            '<a href="' +
            escapeHtml(url) +
            '" class="lk-chat-attachment-link small" target="_blank" rel="noopener">Download attachment</a>';
        }
      }
      html +=
        '<div class="small mt-1 d-flex justify-content-between align-items-center ' +
        (mine ? "text-white-50" : "text-muted") +
        '"><span style="font-size: 0.75rem;">' +
        escapeHtml(m.created_at) +
        "</span></div>";
      bubble.innerHTML = html;
      wrap.appendChild(bubble);
      listEl.appendChild(wrap);
    });
    listEl.scrollTop = listEl.scrollHeight;
  }

  async function load() {
    let url = apiUrl + "?business_id=" + encodeURIComponent(cfg.businessId);
    if (cfg.receiverId) url += "&receiver_id=" + encodeURIComponent(cfg.receiverId);
    const res = await fetch(url, { credentials: "same-origin" });
    const json = await res.json();
    if (json.success) {
      render(json.data || []);
    }
  }

  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();
      const text = (input && input.value) || "";
      const hasFile = fileInput && fileInput.files && fileInput.files[0];
      if (!text.trim() && !hasFile) return;
      const fd = new FormData();
      fd.append("action", "send");
      fd.append("csrf_token", csrf);
      fd.append("message_content", text.trim());
      fd.append("business_id", String(cfg.businessId));
      if (cfg.receiverId) fd.append("receiver_id", String(cfg.receiverId));
      if (hasFile) fd.append("attachment", fileInput.files[0]);
      const res = await fetch(apiUrl, { method: "POST", credentials: "same-origin", body: fd });
      const json = await res.json();
      if (json.success) {
        if (input) input.value = "";
        if (fileInput) fileInput.value = "";
        load();
      } else {
        alert(json.message || "Unable to send");
      }
    });
  }

  load();
  setInterval(load, 3000);
})();
