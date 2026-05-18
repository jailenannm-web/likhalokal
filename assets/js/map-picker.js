/**
 * LikhaLokal map picker — requires Google Maps JS API with callback initLikhaMapPickers
 */
document.addEventListener('DOMContentLoaded', function () {
  window.initLikhaMapPickers = function () {
    document.querySelectorAll('[data-map-picker]').forEach(function (mapEl) {
      const latInputId = mapEl.dataset.latInput;
      const lngInputId = mapEl.dataset.lngInput;

      const latInput = document.getElementById(latInputId);
      const lngInput = document.getElementById(lngInputId);

      if (!latInput || !lngInput || !window.google || !google.maps) {
        return;
      }

      const defaultLat = parseFloat(mapEl.dataset.defaultLat || '14.1720000');
      const defaultLng = parseFloat(mapEl.dataset.defaultLng || '122.9450000');

      const savedLat = parseFloat(latInput.value);
      const savedLng = parseFloat(lngInput.value);

      const initialLat = Number.isFinite(savedLat) ? savedLat : defaultLat;
      const initialLng = Number.isFinite(savedLng) ? savedLng : defaultLng;

      const initialPosition = {
        lat: initialLat,
        lng: initialLng,
      };

      const map = new google.maps.Map(mapEl, {
        center: initialPosition,
        zoom: 14,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
      });

      const marker = new google.maps.Marker({
        position: initialPosition,
        map: map,
        draggable: true,
      });

      function updateInputs(position) {
        latInput.value = position.lat().toFixed(7);
        lngInput.value = position.lng().toFixed(7);
      }

      map.addListener('click', function (event) {
        marker.setPosition(event.latLng);
        updateInputs(event.latLng);
      });

      marker.addListener('dragend', function (event) {
        updateInputs(event.latLng);
      });
    });
  };
});
