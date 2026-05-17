/**
 * Google Maps — set GOOGLE_MAPS_API_KEY in config/app.php (exposed as window.LIKHA_GOOGLE_KEY)
 */
function likhaMapsEmbedFallback(el, lat, lng, title, fallbackAddress) {
  if (!el) return;
  const q =
    lat != null && lng != null && !isNaN(lat) && !isNaN(lng)
      ? lat + "," + lng
      : fallbackAddress || title || "Vinzons, Camarines Norte";
  el.innerHTML =
    '<iframe width="100%" height="100%" style="border:0;min-height:220px;" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=' +
    encodeURIComponent(q) +
    '&z=14&output=embed"></iframe>';
}

function likhaMapsLoadScript(callback) {
  const key = window.LIKHA_GOOGLE_KEY;
  if (!key || key === "YOUR_GOOGLE_MAPS_API_KEY_HERE") {
    if (typeof callback === "function") callback(false);
    return;
  }
  if (window.google && window.google.maps) {
    callback(true);
    return;
  }
  if (window.__likhaMapLoading) {
    window.__likhaMapQueue = window.__likhaMapQueue || [];
    window.__likhaMapQueue.push(callback);
    return;
  }
  window.__likhaMapLoading = true;
  window.__likhaMapQueue = [callback];
  const s = document.createElement("script");
  s.src =
    "https://maps.googleapis.com/maps/api/js?key=" +
    encodeURIComponent(key) +
    "&callback=__likhaMapInit";
  s.async = true;
  s.defer = true;
  window.__likhaMapInit = function () {
    window.__likhaMapLoading = false;
    const q = window.__likhaMapQueue || [];
    window.__likhaMapQueue = [];
    q.forEach((cb) => typeof cb === "function" && cb(true));
  };
  s.onerror = function () {
    window.__likhaMapLoading = false;
    const q = window.__likhaMapQueue || [];
    window.__likhaMapQueue = [];
    q.forEach((cb) => typeof cb === "function" && cb(false));
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
      likhaMapsEmbedFallback(el, hasCoords ? latNum : null, hasCoords ? lngNum : null, title, fallbackAddress);
      return;
    }
    try {
      const center = { lat: latNum, lng: lngNum };
      const map = new google.maps.Map(el, {
        zoom: 14,
        center,
        mapTypeControl: false,
        streetViewControl: false,
      });
      new google.maps.Marker({ position: center, map, title: title || "" });
    } catch (err) {
      console.warn("Map init failed", err);
      likhaMapsEmbedFallback(el, latNum, lngNum, title, fallbackAddress);
    }
  });
}

window.likhaMapsLoadScript = likhaMapsLoadScript;
window.likhaInitMap = likhaInitMap;
window.likhaMapsEmbedFallback = likhaMapsEmbedFallback;
