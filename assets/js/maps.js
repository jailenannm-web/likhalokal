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

const LIKHA_DEFAULT_CENTER = { lat: 14.172, lng: 122.945 };

function likhaInitMapPicker(containerId, latInputId, lngInputId) {
  const el = document.getElementById(containerId);
  const latIn = document.getElementById(latInputId);
  const lngIn = document.getElementById(lngInputId);
  if (!el) return;

  function setInputs(pos) {
    if (latIn) latIn.value = pos.lat().toFixed(7);
    if (lngIn) lngIn.value = pos.lng().toFixed(7);
  }

  const lat0 = latIn && latIn.value ? parseFloat(latIn.value) : NaN;
  const lng0 = lngIn && lngIn.value ? parseFloat(lngIn.value) : NaN;
  const hasCoords = !isNaN(lat0) && !isNaN(lng0);
  const center = hasCoords ? { lat: lat0, lng: lng0 } : LIKHA_DEFAULT_CENTER;

  likhaMapsLoadScript(function (ok) {
    if (!ok) {
      el.innerHTML = '<p class="text-muted small p-3 mb-0">Map picker unavailable. Enter coordinates manually or configure GOOGLE_MAPS_API_KEY.</p>';
      likhaMapsEmbedFallback(el, hasCoords ? lat0 : null, hasCoords ? lng0 : null, "", "Vinzons, Camarines Norte");
      return;
    }
    const map = new google.maps.Map(el, { zoom: hasCoords ? 15 : 13, center, mapTypeControl: false });
    const marker = new google.maps.Marker({ position: center, map, draggable: true });
    map.addListener("click", function (e) {
      marker.setPosition(e.latLng);
      setInputs(e.latLng);
    });
    marker.addListener("dragend", function () {
      setInputs(marker.getPosition());
    });
  });
}

window.initMapPickers = function () {
  likhaInitMapPicker("businessMapPicker", "businessLatitude", "businessLongitude");
  likhaInitMapPicker("attractionMapPicker", "attractionLatitude", "attractionLongitude");
};

window.likhaMapsLoadScript = likhaMapsLoadScript;
window.likhaInitMap = likhaInitMap;
window.likhaMapsEmbedFallback = likhaMapsEmbedFallback;
window.likhaInitMapPicker = likhaInitMapPicker;
