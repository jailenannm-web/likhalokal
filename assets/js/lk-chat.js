/**
 * Shared LikhaLokal chat UI (public/message, user/messages, seller/messages, admin/messages)
 */
(function () {
  function init() {
    const cfg = window.LK_CHAT || window.LK_USER_CHAT || window.LK_SELLER_CHAT || window.LK_ADMIN_CHAT;
    if (!cfg) return;

    const listEl =
      document.getElementById(cfg.listId || "chatMessages") ||
      document.getElementById("lkUserChatMessages") ||
      document.getElementById("lkSellerChatMessages") ||
      document.getElementById("lkAdminChatMessages");
    const form =
      document.getElementById(cfg.formId || "chatForm") ||
      document.getElementById("lkUserChatForm") ||
      document.getElementById("lkSellerChatForm") ||
      document.getElementById("lkAdminChatForm");
    const input =
      document.getElementById(cfg.inputId || "chatInput") ||
      document.getElementById("lkUserChatInput") ||
      document.getElementById("lkSellerChatInput") ||
      document.getElementById("lkAdminChatInput");
    const fileInput = document.getElementById(cfg.fileId || "chatAttachment");
    const previewEl = document.getElementById(cfg.previewId || "chatAttachmentPreview");
    const sendBtn =
      form && (form.querySelector('button[type="submit"]') || form.querySelector(".lk-chat-send-btn"));
    const apiUrl = cfg.apiUrl || "/likhalokal/api/messages.php";
    const errorElId = cfg.errorId || "lkChatError";
    let errorEl = document.getElementById(errorElId);

    if (!listEl || !form) {
      return;
    }

    function getAppBase() {
      if (cfg.appBase) {
        return String(cfg.appBase).replace(/\/$/, "");
      }
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
        if (p.startsWith("uploads/")) {
          p = "assets/" + p;
        } else if (p.indexOf("uploads/messages/") !== -1) {
          p = "assets/" + p.replace(/^.*?(uploads\/messages\/)/, "uploads/messages/");
          if (!p.startsWith("assets/")) p = "assets/" + p;
        } else {
          p = "assets/uploads/messages/" + p.replace(/^.*\//, "");
        }
      }
      return p;
    }

    function buildAttachmentUrl(message) {
      if (message.attachment_url) {
        return message.attachment_url;
      }
      const normalized = normalizeAttachmentPath(message.attachment_path);
      if (!normalized) return null;
      const appBase = getAppBase();
      return (appBase ? appBase : "") + "/" + normalized;
    }

    function ensureErrorEl() {
      if (errorEl) return errorEl;
      const host = form.closest(".border-top") || form.parentElement;
      if (!host) return null;
      errorEl = document.createElement("div");
      errorEl.id = errorElId;
      errorEl.className = "lk-chat-error small text-danger mb-2 d-none";
      errorEl.setAttribute("role", "alert");
      host.insertBefore(errorEl, form);
      return errorEl;
    }

    function showError(msg) {
      const el = ensureErrorEl();
      if (!el) {
        console.error("Chat error:", msg);
        return;
      }
      el.textContent = msg;
      el.classList.remove("d-none");
    }

    function clearError() {
      if (errorEl) {
        errorEl.textContent = "";
        errorEl.classList.add("d-none");
      }
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
      const p = (m.attachment_path || "").toLowerCase();
      return /\.(jpe?g|png|gif|webp)$/.test(p);
    }

    function appendAttachment(bubble, m, mine) {
      if (!m.attachment_path && !m.attachment_url) return;
      const url = buildAttachmentUrl(m);
      if (!url) return;

      if (isImageAttachment(m)) {
        const img = document.createElement("img");
        img.src = url;
        img.alt = "Message attachment";
        img.loading = "lazy";
        img.className = "lk-chat-attachment-img";
        if (mine) {
          bubble.classList.add("is-mine");
        }
        img.onerror = function () {
          console.warn("Attachment image failed:", url);
          const fallback = document.createElement("div");
          fallback.className = "small text-muted fst-italic";
          fallback.textContent = "Image could not be loaded";
          img.replaceWith(fallback);
        };
        bubble.appendChild(img);
      } else {
        const a = document.createElement("a");
        a.href = url;
        a.target = "_blank";
        a.rel = "noopener";
        a.className = "lk-chat-attachment-link small";
        a.textContent = "Download attachment";
        bubble.appendChild(a);
      }
    }

    function render(messages) {
      listEl.innerHTML = "";
      if (!messages || !messages.length) {
        listEl.innerHTML =
          '<p class="text-center text-muted small py-4">No messages yet. Start the conversation!</p>';
        return;
      }
      messages.forEach((m) => {
        const mine = String(m.sender_id) === String(cfg.me);
        const wrap = document.createElement("div");
        wrap.className = "mb-3 d-flex " + (mine ? "justify-content-end" : "justify-content-start");
        const bubble = document.createElement("div");
        bubble.className =
          "p-3 shadow-sm " + (mine ? "lk-bubble-out chat-bubble-out is-mine" : "lk-bubble-in chat-bubble-in");
        bubble.style.maxWidth = "78%";

        const text = (m.message_content || "").trim();
        const isPlaceholderOnly =
          text === "" || /^attachment\.?$/i.test(text) || text === "[Image]";

        if (!isPlaceholderOnly) {
          const body = document.createElement("div");
          body.style.fontSize = "0.95rem";
          body.innerHTML = escapeHtml(m.message_content).replace(/\n/g, "<br>");
          bubble.appendChild(body);
        }

        appendAttachment(bubble, m, mine);

        const time = document.createElement("div");
        time.className = "small mt-1 opacity-75";
        time.textContent = m.created_at || "";
        if (m.is_auto_reply == 1) {
          time.textContent = (time.textContent ? time.textContent + " · " : "") + "Auto-reply";
        }
        bubble.appendChild(time);
        wrap.appendChild(bubble);
        listEl.appendChild(wrap);
      });
      listEl.scrollTop = listEl.scrollHeight;
    }

    function buildQuery() {
      const params = new URLSearchParams();
      if (cfg.conversationType === "admin_support") {
        params.set("conversation_type", "admin_support");
        if (cfg.receiverId) params.set("receiver_id", cfg.receiverId);
      } else if (cfg.businessId) {
        params.set("business_id", cfg.businessId);
        if (cfg.receiverId) params.set("receiver_id", cfg.receiverId);
      }
      return params.toString();
    }

    async function load() {
      const q = buildQuery();
      if (!q && cfg.conversationType !== "admin_support") return;
      try {
        const res = await fetch(apiUrl + "?" + q, { credentials: "same-origin" });
        const json = await res.json();
        if (!json.success) {
          showError(json.message || "Could not load messages.");
          return;
        }
        const rows = Array.isArray(json.data) ? json.data : [];
        render(rows);
        const markBody = {
          action: "mark_read",
          csrf_token: cfg.csrf,
          conversation_type: cfg.conversationType || "business_inquiry",
        };
        if (cfg.businessId) markBody.business_id = cfg.businessId;
        if (cfg.receiverId) markBody.receiver_id = cfg.receiverId;
        await fetch(apiUrl, {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(markBody),
        });
      } catch (err) {
        console.error("Chat load failed:", err);
        showError("Could not load messages. Please refresh.");
      }
    }

    async function sendMessage() {
      const text = (input && input.value) || "";
      const hasFile = fileInput && fileInput.files && fileInput.files[0];
      if (!text.trim() && !hasFile) {
        showError("Please enter a message or choose an image.");
        return;
      }

      clearError();
      if (sendBtn) sendBtn.disabled = true;

      const fd = new FormData();
      fd.append("action", "send");
      fd.append("csrf_token", cfg.csrf || "");
      fd.append("message_content", text.trim());
      if (cfg.conversationType) fd.append("conversation_type", cfg.conversationType);
      if (cfg.businessId) fd.append("business_id", String(cfg.businessId));
      if (cfg.receiverId) fd.append("receiver_id", String(cfg.receiverId));
      if (cfg.productId) fd.append("product_id", String(cfg.productId));
      if (hasFile) fd.append("attachment", fileInput.files[0]);

      try {
        const res = await fetch(apiUrl, { method: "POST", credentials: "same-origin", body: fd });
        let json;
        try {
          json = await res.json();
        } catch (parseErr) {
          console.error("Chat API returned non-JSON:", parseErr);
          showError("Server error. Could not send message.");
          return;
        }
        if (json.success) {
          if (input) input.value = "";
          if (fileInput) fileInput.value = "";
          if (previewEl) previewEl.innerHTML = "";
          await load();
        } else {
          showError(json.message || "Unable to send message");
          console.error("Chat send failed:", json);
        }
      } catch (err) {
        console.error("Chat send error:", err);
        showError("Network error. Please try again.");
      } finally {
        if (sendBtn) sendBtn.disabled = false;
      }
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      sendMessage();
    });

    if (fileInput && previewEl) {
      fileInput.addEventListener("change", function () {
        previewEl.innerHTML = "";
        const f = fileInput.files && fileInput.files[0];
        if (f && f.type.startsWith("image/")) {
          const img = document.createElement("img");
          img.src = URL.createObjectURL(f);
          img.className = "rounded mt-2";
          img.style.maxHeight = "120px";
          previewEl.appendChild(img);
        }
      });
    }

    if (cfg.conversationType === "admin_support" || cfg.businessId) {
      load();
      setInterval(load, cfg.pollMs || 3500);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
