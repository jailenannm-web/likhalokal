/**
 * Legacy alias — user/messages.php uses lk-chat.js directly.
 * Kept so older script tags do not break; forwards config to LK_CHAT.
 */
(function () {
  if (window.LK_USER_CHAT && !window.LK_CHAT) {
    window.LK_CHAT = window.LK_USER_CHAT;
  }
  if (document.querySelector('script[src*="lk-chat.js"]')) {
    return;
  }
  const s = document.createElement("script");
  s.src = (window.LK_USER_CHAT && window.LK_USER_CHAT.assetBase
    ? window.LK_USER_CHAT.assetBase.replace(/\/?$/, "/") + "js/lk-chat.js?v=2"
    : "/likhalokal/assets/js/lk-chat.js?v=2");
  document.body.appendChild(s);
})();
