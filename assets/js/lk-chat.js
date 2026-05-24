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
    const quickRepliesEl = document.getElementById(cfg.quickRepliesId || "lkChatQuickReplies");
    const sendBtn =
      form && (form.querySelector('button[type="submit"]') || form.querySelector(".lk-chat-send-btn"));
    const apiUrl = cfg.apiUrl || "/likhalokal/api/messages.php";
    const errorElId = cfg.errorId || "lkChatError";
    let errorEl = document.getElementById(errorElId);
    let lastRenderedSignature = "";

    const chatReady = !!(listEl && form);

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
      const signature = messages && messages.length ? messages[messages.length - 1].id + ":" + messages.length : "empty";
      if (signature === lastRenderedSignature) {
        return;
      }
      lastRenderedSignature = signature;
      listEl.innerHTML = "";
      if (!messages || !messages.length) {
        listEl.innerHTML =
          '<p class="text-center text-muted small py-4">No messages yet. Start the conversation!</p>';
        listEl.scrollTop = listEl.scrollHeight;
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

    function renderQuickReplies(items) {
      if (!quickRepliesEl) return;
      quickRepliesEl.innerHTML = "";
      if (!Array.isArray(items) || !items.length) {
        quickRepliesEl.classList.add("d-none");
        return;
      }
      const intro = document.createElement("span");
      intro.className = "lk-quick-replies-label";
      intro.textContent = "Ask about";
      quickRepliesEl.appendChild(intro);
      items.forEach((item) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "lk-quick-reply-chip";
        btn.textContent = item.label || item.type || "Question";
        btn.dataset.faqType = item.type || "";
        btn.dataset.faqLabel = item.label || item.type || "Question";
        quickRepliesEl.appendChild(btn);
      });
      quickRepliesEl.classList.remove("d-none");
    }

    async function loadQuickReplies() {
      if (!quickRepliesEl || cfg.conversationType !== "business_inquiry" || !cfg.businessId) return;
      try {
        const params = new URLSearchParams();
        params.set("action", "quick_replies");
        params.set("business_id", cfg.businessId);
        const res = await fetch(apiUrl + "?" + params.toString(), { credentials: "same-origin" });
        const json = await res.json();
        const items = json.success && json.data && Array.isArray(json.data.quick_replies)
          ? json.data.quick_replies
          : [];
        renderQuickReplies(items);
      } catch (err) {
        console.error("Quick replies failed:", err);
        renderQuickReplies([]);
      }
    }

    async function sendQuickReply(type, label, button) {
      if (!type || !label) return;
      clearError();
      if (button) button.disabled = true;

      const fd = new FormData();
      fd.append("action", "send");
      fd.append("csrf_token", cfg.csrf || "");
      fd.append("message_content", label);
      fd.append("conversation_type", "business_inquiry");
      fd.append("business_id", String(cfg.businessId || ""));
      fd.append("receiver_id", String(cfg.receiverId || ""));
      fd.append("faq_type", type);
      if (cfg.productId) fd.append("product_id", String(cfg.productId));

      try {
        const res = await fetch(apiUrl, { method: "POST", credentials: "same-origin", body: fd });
        const json = await res.json();
        if (!json.success) {
          showError(json.message || "Unable to send quick reply.");
          return;
        }
        await load();
      } catch (err) {
        console.error("Quick reply send failed:", err);
        showError("Network error. Please try again.");
      } finally {
        if (button) button.disabled = false;
      }
    }

    if (chatReady) {
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
        loadQuickReplies();
        setInterval(load, cfg.pollMs || 3500);
      }

      if (quickRepliesEl) {
        quickRepliesEl.addEventListener("click", function (e) {
          const btn = e.target.closest(".lk-quick-reply-chip");
          if (!btn) return;
          sendQuickReply(btn.dataset.faqType || "", btn.dataset.faqLabel || btn.textContent || "", btn);
        });
      }
    }

    initNewMessage();
    initDeleteConversation();

    function initNewMessage() {
      const newCfg = cfg.newMessage || window.LK_NEW_MESSAGE;
      if (!newCfg) return;
      const newForm = document.getElementById(newCfg.formId || "lkNewMessageForm");
      const legacySelect = document.getElementById(newCfg.receiverId || "lkNewMessageReceiver");
      const searchInput = document.getElementById(newCfg.searchId || "lkNewMessageReceiverSearch");
      const resultsEl = document.getElementById(newCfg.resultsId || "lkNewMessageReceiverResults");
      const selectedEl = document.getElementById(newCfg.selectedId || "lkNewMessageSelected");
      const conversationTypeInput = document.getElementById(newCfg.conversationTypeId || "lkNewMessageConversationType");
      const receiverIdInput = document.getElementById(newCfg.receiverIdId || "lkNewMessageReceiverId");
      const roleInput = document.getElementById(newCfg.roleId || "lkNewMessageReceiverRole");
      const businessIdInput = document.getElementById(newCfg.businessIdId || "lkNewMessageBusinessId");
      const redirectInput = document.getElementById(newCfg.redirectId || "lkNewMessageRedirect");
      const textInput = document.getElementById(newCfg.inputId || "lkNewMessageText");
      const submitBtn =
        document.getElementById(newCfg.submitId || "lkNewMessageSubmit") ||
        (newForm && newForm.querySelector('button[type="submit"]'));
      const errorHost = document.getElementById(newCfg.errorId || "lkNewMessageError");
      const clearBtn = selectedEl ? selectedEl.querySelector("[data-recipient-clear]") : null;
      const selectedLabel = selectedEl ? selectedEl.querySelector("[data-selected-label]") : null;
      const selectedMeta = selectedEl ? selectedEl.querySelector("[data-selected-meta]") : null;
      const searchUrl = newCfg.searchUrl || cfg.receiverSearchUrl || cfg.receiversUrl || "";
      let searchTimer = 0;
      let activeController = null;
      let selectedReceiver = null;

      if (!newForm || !textInput) return;

      function setNewError(message) {
        if (!errorHost) {
          if (message) alert(message);
          return;
        }
        errorHost.textContent = message || "";
        errorHost.classList.toggle("d-none", !message);
      }

      function escapeText(value) {
        return (value || "")
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;");
      }

      function updateSubmitState() {
        const hasMessage = textInput.value.trim() !== "";
        let hasReceiver = false;
        if (searchInput) {
          hasReceiver = !!(receiverIdInput && receiverIdInput.value);
        } else if (legacySelect) {
          hasReceiver = !!legacySelect.value;
        }
        if (submitBtn) {
          submitBtn.disabled = !(hasReceiver && hasMessage);
        }
      }

      function renderHint(message) {
        if (!resultsEl) return;
        resultsEl.innerHTML = '<div class="lk-recipient-empty">' + escapeText(message) + "</div>";
        resultsEl.classList.add("show");
      }

      function hideResults() {
        if (!resultsEl) return;
        resultsEl.classList.remove("show");
      }

      function clearSelectedReceiver() {
        selectedReceiver = null;
        if (conversationTypeInput) conversationTypeInput.value = "";
        if (receiverIdInput) receiverIdInput.value = "";
        if (roleInput) roleInput.value = "";
        if (businessIdInput) businessIdInput.value = "";
        if (redirectInput) redirectInput.value = "";
        if (selectedEl) selectedEl.classList.add("d-none");
        if (searchInput) {
          searchInput.value = "";
          searchInput.disabled = false;
          searchInput.focus();
        }
        renderHint("Type a name to search");
        updateSubmitState();
      }

      function selectReceiver(receiver) {
        selectedReceiver = receiver;
        if (conversationTypeInput) conversationTypeInput.value = receiver.conversation_type || "";
        if (receiverIdInput) receiverIdInput.value = receiver.receiver_id || "";
        if (roleInput) roleInput.value = receiver.role || receiver.role_label || "";
        if (businessIdInput) businessIdInput.value = receiver.business_id || "";
        if (redirectInput) redirectInput.value = receiver.redirect || "";
        if (selectedLabel) selectedLabel.textContent = receiver.label || "Selected receiver";
        if (selectedMeta) {
          const metaParts = [receiver.role_label, receiver.meta].filter(Boolean);
          selectedMeta.textContent = metaParts.join(" - ");
        }
        if (selectedEl) selectedEl.classList.remove("d-none");
        if (searchInput) {
          searchInput.value = "";
          searchInput.disabled = true;
        }
        hideResults();
        updateSubmitState();
      }

      function renderResults(receivers) {
        if (!resultsEl) return;
        if (!receivers.length) {
          renderHint("No users found");
          return;
        }
        resultsEl.innerHTML = receivers
          .map(function (receiver, index) {
            return (
              '<button type="button" class="lk-recipient-result" data-index="' +
              index +
              '">' +
              '<strong>' +
              escapeText(receiver.label) +
              "</strong>" +
              "<span>" +
              escapeText(receiver.role_label || "") +
              "</span>" +
              (receiver.meta ? "<small>" + escapeText(receiver.meta) + "</small>" : "") +
              "</button>"
            );
          })
          .join("");
        resultsEl.classList.add("show");
        resultsEl.querySelectorAll("[data-index]").forEach(function (button) {
          button.addEventListener("click", function () {
            const receiver = receivers[Number(button.dataset.index)];
            if (receiver) {
              selectReceiver(receiver);
            }
          });
        });
      }

      async function searchReceivers(query) {
        if (!searchUrl || !resultsEl) return;
        if (activeController) {
          activeController.abort();
        }
        activeController = new AbortController();
        try {
          const url = searchUrl + "?q=" + encodeURIComponent(query);
          const res = await fetch(url, { credentials: "same-origin", signal: activeController.signal });
          const json = await res.json();
          if (!json.success) {
            renderHint(json.message || "Could not search receivers");
            return;
          }
          const receivers = json.data && Array.isArray(json.data.receivers) ? json.data.receivers : [];
          renderResults(receivers);
        } catch (err) {
          if (err.name !== "AbortError") {
            console.error("Receiver search failed:", err);
            renderHint("Could not search receivers");
          }
        }
      }

      if (searchInput) {
        renderHint("Type a name to search");
        searchInput.addEventListener("input", function () {
          clearTimeout(searchTimer);
          const query = searchInput.value.trim();
          if (selectedReceiver) {
            clearSelectedReceiver();
            return;
          }
          if (query.length < 1) {
            renderHint("Type a name to search");
            return;
          }
          renderHint("Searching...");
          searchTimer = setTimeout(function () {
            searchReceivers(query);
          }, 220);
        });
        searchInput.addEventListener("focus", function () {
          if (!selectedReceiver && resultsEl && !resultsEl.classList.contains("show")) {
            renderHint(searchInput.value.trim() ? "Searching..." : "Type a name to search");
            if (searchInput.value.trim()) {
              searchReceivers(searchInput.value.trim());
            }
          }
        });
      }

      if (clearBtn) {
        clearBtn.addEventListener("click", clearSelectedReceiver);
      }

      textInput.addEventListener("input", updateSubmitState);
      if (legacySelect) {
        legacySelect.addEventListener("change", updateSubmitState);
      }
      updateSubmitState();

      newForm.addEventListener("submit", async function (event) {
        event.preventDefault();
        setNewError("");
        const message = textInput.value.trim();
        if (searchInput && !(receiverIdInput && receiverIdInput.value)) {
          setNewError("Choose who you want to message.");
          return;
        }
        if (!searchInput) {
          const option = legacySelect && legacySelect.options[legacySelect.selectedIndex];
          if (!option || !option.value) {
            setNewError("Choose who you want to message.");
            return;
          }
        }
        if (!message) {
          setNewError("Type your first message.");
          return;
        }

        const fd = new FormData();
        fd.append("action", "send");
        fd.append("csrf_token", cfg.csrf || "");
        fd.append("message_content", message);
        if (searchInput) {
          fd.append("conversation_type", conversationTypeInput.value || "business_inquiry");
          if (receiverIdInput.value) fd.append("receiver_id", receiverIdInput.value);
          if (businessIdInput.value) fd.append("business_id", businessIdInput.value);
        } else {
          const option = legacySelect.options[legacySelect.selectedIndex];
          fd.append("conversation_type", option.dataset.conversationType || "business_inquiry");
          if (option.dataset.receiverId) fd.append("receiver_id", option.dataset.receiverId);
          if (option.dataset.businessId) fd.append("business_id", option.dataset.businessId);
        }

        if (submitBtn) submitBtn.disabled = true;
        try {
          const res = await fetch(apiUrl, { method: "POST", credentials: "same-origin", body: fd });
          const json = await res.json();
          if (!json.success) {
            setNewError(json.message || "Could not create conversation.");
            return;
          }
          let redirect = newCfg.defaultRedirect || window.location.href;
          if (searchInput && redirectInput && redirectInput.value) {
            redirect = redirectInput.value;
          } else if (!searchInput && legacySelect) {
            const option = legacySelect.options[legacySelect.selectedIndex];
            redirect = option.dataset.redirect || redirect;
          }
          window.location.href = redirect;
        } catch (err) {
          console.error("New message failed:", err);
          setNewError("Network error. Please try again.");
        } finally {
          if (submitBtn) submitBtn.disabled = false;
        }
      });
    }

    function initDeleteConversation() {
      const buttons = document.querySelectorAll("[data-delete-conversation]");
      if (!buttons.length) return;
      buttons.forEach(function (button) {
        button.addEventListener("click", async function (event) {
          event.preventDefault();
          event.stopPropagation();
          if (!confirm("Are you sure you want to delete this conversation?")) {
            return;
          }
          const fd = new FormData();
          fd.append("action", "delete_conversation");
          fd.append("csrf_token", cfg.csrf || button.dataset.csrf || "");
          fd.append("conversation_type", button.dataset.conversationType || cfg.conversationType || "business_inquiry");
          if (button.dataset.businessId) fd.append("business_id", button.dataset.businessId);
          if (button.dataset.receiverId) fd.append("receiver_id", button.dataset.receiverId);

          button.disabled = true;
          try {
            const res = await fetch(apiUrl, { method: "POST", credentials: "same-origin", body: fd });
            const json = await res.json();
            if (!json.success) {
              alert(json.message || "Could not delete conversation.");
              button.disabled = false;
              return;
            }
            window.location.href = button.dataset.redirect || window.location.href;
          } catch (err) {
            console.error("Delete conversation failed:", err);
            alert("Network error. Please try again.");
            button.disabled = false;
          }
        });
      });
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
