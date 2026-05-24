/**
 * LikhaLokal map picker.
 * Uses Google Maps when the API is already loaded, otherwise loads Leaflet/OpenStreetMap.
 */
(function () {
  const DEFAULT_CENTER = { lat: 14.172, lng: 122.945 };
  const LEAFLET_CSS = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.css";
  const LEAFLET_JS = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";

  let leafletPromise = null;

  function numberOrNull(value) {
    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  function clampCoord(value, min, max) {
    const parsed = numberOrNull(value);
    if (parsed === null || parsed < min || parsed > max) {
      return null;
    }
    return parsed;
  }

  function loadScript(src) {
    return new Promise(function (resolve, reject) {
      const existing = document.querySelector('script[src="' + src + '"]');
      if (existing) {
        existing.addEventListener("load", resolve, { once: true });
        existing.addEventListener("error", reject, { once: true });
        if (existing.dataset.loaded === "1") {
          resolve();
        }
        return;
      }

      const script = document.createElement("script");
      script.src = src;
      script.async = true;
      script.defer = true;
      script.onload = function () {
        script.dataset.loaded = "1";
        resolve();
      };
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  function ensureLeaflet() {
    if (window.L) {
      return Promise.resolve(window.L);
    }
    if (!leafletPromise) {
      if (!document.querySelector('link[href="' + LEAFLET_CSS + '"]')) {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = LEAFLET_CSS;
        document.head.appendChild(link);
      }
      leafletPromise = loadScript(LEAFLET_JS).then(function () {
        return window.L;
      });
    }
    return leafletPromise;
  }

  function addLocateButton(mapEl, onLocate) {
    const existing = mapEl.parentElement
      ? mapEl.parentElement.querySelector("[data-map-locate-button]")
      : null;
    if (existing) {
      existing.addEventListener("click", onLocate);
      return;
    }

    const button = document.createElement("button");
    button.type = "button";
    button.className = "btn btn-sm btn-outline-primary mt-2";
    button.dataset.mapLocateButton = "1";
    button.innerHTML = '<i class="bi bi-crosshair me-1"></i> Use My Current Location';
    button.addEventListener("click", onLocate);
    mapEl.insertAdjacentElement("afterend", button);
  }

  function resolveInputs(mapEl) {
    const latInputId = mapEl.dataset.latInput;
    const lngInputId = mapEl.dataset.lngInput;
    return {
      latInput: latInputId ? document.getElementById(latInputId) : null,
      lngInput: lngInputId ? document.getElementById(lngInputId) : null,
    };
  }

  function initialState(mapEl, latInput, lngInput) {
    const defaultLat = clampCoord(mapEl.dataset.defaultLat, -90, 90) ?? DEFAULT_CENTER.lat;
    const defaultLng = clampCoord(mapEl.dataset.defaultLng, -180, 180) ?? DEFAULT_CENTER.lng;
    const savedLat = latInput ? clampCoord(latInput.value, -90, 90) : null;
    const savedLng = lngInput ? clampCoord(lngInput.value, -180, 180) : null;
    const hasSaved = savedLat !== null && savedLng !== null;

    return {
      lat: hasSaved ? savedLat : defaultLat,
      lng: hasSaved ? savedLng : defaultLng,
      hasSaved,
    };
  }

  function writeInputs(latInput, lngInput, lat, lng) {
    if (latInput) {
      latInput.value = Number(lat).toFixed(7);
    }
    if (lngInput) {
      lngInput.value = Number(lng).toFixed(7);
    }
  }

  function readInputs(latInput, lngInput) {
    const lat = latInput ? clampCoord(latInput.value, -90, 90) : null;
    const lng = lngInput ? clampCoord(lngInput.value, -180, 180) : null;
    return lat !== null && lng !== null ? { lat, lng } : null;
  }

  function initGooglePicker(mapEl, latInput, lngInput) {
    const state = initialState(mapEl, latInput, lngInput);
    const initialPosition = { lat: state.lat, lng: state.lng };
    const map = new google.maps.Map(mapEl, {
      center: initialPosition,
      zoom: state.hasSaved ? 16 : 14,
      mapTypeControl: true,
      streetViewControl: false,
      fullscreenControl: true,
    });
    const marker = new google.maps.Marker({
      position: initialPosition,
      map: map,
      draggable: true,
    });

    function moveTo(lat, lng, shouldWrite) {
      const position = new google.maps.LatLng(lat, lng);
      marker.setPosition(position);
      map.panTo(position);
      if (shouldWrite) {
        writeInputs(latInput, lngInput, lat, lng);
      }
    }

    map.addListener("click", function (event) {
      moveTo(event.latLng.lat(), event.latLng.lng(), true);
    });

    marker.addListener("dragend", function () {
      const position = marker.getPosition();
      if (position) {
        writeInputs(latInput, lngInput, position.lat(), position.lng());
      }
    });

    [latInput, lngInput].forEach(function (input) {
      if (!input) return;
      input.addEventListener("input", function () {
        const coords = readInputs(latInput, lngInput);
        if (coords) {
          moveTo(coords.lat, coords.lng, false);
        }
      });
    });

    addLocateButton(mapEl, function () {
      locateUser(function (lat, lng) {
        moveTo(lat, lng, true);
        map.setZoom(17);
      }, mapEl);
    });

    setTimeout(function () {
      google.maps.event.trigger(map, "resize");
      map.setCenter(marker.getPosition());
    }, 150);
  }

  function initLeafletPicker(mapEl, latInput, lngInput) {
    ensureLeaflet()
      .then(function (L) {
        const state = initialState(mapEl, latInput, lngInput);
        const map = L.map(mapEl).setView([state.lat, state.lng], state.hasSaved ? 16 : 14);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          maxZoom: 19,
          attribution: "&copy; OpenStreetMap contributors",
        }).addTo(map);

        const marker = L.marker([state.lat, state.lng], { draggable: true }).addTo(map);

        function moveTo(lat, lng, shouldWrite) {
          marker.setLatLng([lat, lng]);
          map.panTo([lat, lng]);
          if (shouldWrite) {
            writeInputs(latInput, lngInput, lat, lng);
          }
        }

        map.on("click", function (event) {
          moveTo(event.latlng.lat, event.latlng.lng, true);
        });

        marker.on("dragend", function () {
          const position = marker.getLatLng();
          writeInputs(latInput, lngInput, position.lat, position.lng);
        });

        [latInput, lngInput].forEach(function (input) {
          if (!input) return;
          input.addEventListener("input", function () {
            const coords = readInputs(latInput, lngInput);
            if (coords) {
              moveTo(coords.lat, coords.lng, false);
            }
          });
        });

        addLocateButton(mapEl, function () {
          locateUser(function (lat, lng) {
            moveTo(lat, lng, true);
            map.setView([lat, lng], 17);
          }, mapEl);
        });

        setTimeout(function () {
          map.invalidateSize();
        }, 150);
        window.addEventListener("resize", function () {
          map.invalidateSize();
        });
      })
      .catch(function () {
        mapEl.innerHTML =
          '<div class="p-3 small text-muted">Map picker could not be loaded. You can still enter latitude and longitude manually.</div>';
      });
  }

  function locateUser(callback, mapEl) {
    if (!navigator.geolocation) {
      setPickerMessage(mapEl, "Your browser does not support current location lookup.");
      return;
    }
    setPickerMessage(mapEl, "Finding your current location...");
    navigator.geolocation.getCurrentPosition(
      function (position) {
        setPickerMessage(mapEl, "");
        callback(position.coords.latitude, position.coords.longitude);
      },
      function () {
        setPickerMessage(mapEl, "Unable to get your current location. Please allow location access or choose on the map.");
      },
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
  }

  function setPickerMessage(mapEl, message) {
    let messageEl = mapEl.parentElement ? mapEl.parentElement.querySelector("[data-map-picker-message]") : null;
    if (!messageEl && message) {
      messageEl = document.createElement("div");
      messageEl.className = "small text-muted mt-1";
      messageEl.dataset.mapPickerMessage = "1";
      mapEl.insertAdjacentElement("afterend", messageEl);
    }
    if (messageEl) {
      messageEl.textContent = message;
      messageEl.hidden = message === "";
    }
  }

  function initPicker(mapEl) {
    if (!mapEl || mapEl.dataset.mapPickerReady === "1") {
      return;
    }
    const inputs = resolveInputs(mapEl);
    if (!inputs.latInput || !inputs.lngInput) {
      return;
    }

    mapEl.dataset.mapPickerReady = "1";
    if (window.google && google.maps) {
      initGooglePicker(mapEl, inputs.latInput, inputs.lngInput);
      return;
    }

    const googleKey = window.LIKHA_GOOGLE_KEY || "";
    const hasConfiguredGoogleKey =
      googleKey &&
      googleKey !== "YOUR_GOOGLE_MAPS_API_KEY_HERE" &&
      googleKey !== "PASTE_REAL_GOOGLE_MAPS_API_KEY_HERE";
    if (hasConfiguredGoogleKey) {
      setTimeout(function () {
        if (window.google && google.maps) {
          initGooglePicker(mapEl, inputs.latInput, inputs.lngInput);
        } else {
          initLeafletPicker(mapEl, inputs.latInput, inputs.lngInput);
        }
      }, 800);
      return;
    }

    initLeafletPicker(mapEl, inputs.latInput, inputs.lngInput);
  }

  window.initLikhaMapPickers = function () {
    document.querySelectorAll("[data-map-picker]").forEach(initPicker);
  };

  document.addEventListener("DOMContentLoaded", window.initLikhaMapPickers);
})();
