/**
 * Google Maps — set GOOGLE_MAPS_API_KEY in config/app.php (exposed as window.LIKHA_GOOGLE_KEY)
 */
function likhaMapsLoadScript(callback) {
  const key = window.LIKHA_GOOGLE_KEY;
  if (!key || key === "YOUR_GOOGLE_MAPS_API_KEY_HERE") {
    console.warn("Google Maps API key not configured.");
    if (typeof callback === "function") callback(false);
    return;
  }
  if (window.google && window.google.maps) {
    callback(true);
    return;
  }
  const s = document.createElement("script");
  s.src = "https://maps.googleapis.com/maps/api/js?key=" + encodeURIComponent(key) + "&callback=__likhaMapInit";
  window.__likhaMapInit = function () {
    callback(true);
  };
  document.head.appendChild(s);
}

function likhaInitMap(el, lat, lng, title, fallbackAddress) {
  if (!el) return;
  const latNum = parseFloat(lat);
  const lngNum = parseFloat(lng);
  const hasCoords = !isNaN(latNum) && !isNaN(lngNum);
  likhaMapsLoadScript(function (ok) {
    if (!ok || !hasCoords) {
      el.innerHTML =
        '<div class="p-3 small text-muted">Map preview unavailable. <a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=' +
        encodeURIComponent(fallbackAddress || title || "Vinzons") +
        '">Open in Google Maps</a></div>';
      return;
    }
    const center = { lat: latNum, lng: lngNum };
    const map = new google.maps.Map(el, { zoom: 14, center });
    new google.maps.Marker({ position: center, map, title: title || "" });
  });
}

window.likhaMapsLoadScript = likhaMapsLoadScript;
window.likhaInitMap = likhaInitMap;
