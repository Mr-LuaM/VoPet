<template>
    <v-container>
        <div class="text-caption-h6 font-weight-bold text-primary"> Pet Owner’s Statistics / Populations </div>

      <div id="map" style="height: 700px;" class="rounded"></div>
 
    </v-container>
  </template>
  
  <script>
  import { ref, onMounted, onBeforeUnmount } from 'vue';
  import L from 'leaflet';
  import 'leaflet/dist/leaflet.css';
  import axios from 'axios';

  
  export default {
    setup() {
      const map = ref(null);
      const markers = ref([]);
  
      onMounted(async () => {
        map.value = L.map('map', {
          center: [12.8797, 121.7740], // Centered on the Philippines
          zoom: 6,
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors',
        }).addTo(map.value);
  
        await fetchAndPlotLocations();
      });
  
      async function geocodeLocation(locationName) {
        const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationName + ', Philippines')}&limit=1`;
        const response = await axios.get(nominatimUrl, { headers: { 'User-Agent': 'YourAppName' } });
        if (response.data && response.data.length > 0) {
          const { lat, lon } = response.data[0];
          return { lat: parseFloat(lat), lng: parseFloat(lon) };
        }
        throw new Error(`Geocoding failed for location: ${locationName}`);
      }
  
      async function addBubble(coords, location, petCount) {
        if (!map.value || !coords) return; // Ensure map is initialized and coords are not null

        const radius = Math.sqrt(petCount) * 10;
        const marker = L.circleMarker([coords.lat, coords.lng], {
          radius,
          fillColor: "red",
          color: "#000",
          weight: 1,
          opacity: 1,
          fillOpacity: 0.8
        }).addTo(map.value);
  
        const popupContent = `${location}: ${petCount} pets`;
        marker.bindPopup(popupContent);
  
        markers.value.push(marker);
      }
      L.Popup.prototype._animateZoom = function (e) {
  if (!this._map) {
    return
  }
  var pos = this._map._latLngToNewLayerPoint(this._latlng, e.zoom, e.center),
    anchor = this._getAnchor()
  L.DomUtil.setPosition(this._container, pos.add(anchor))
}
      async function fetchAndPlotLocations() {
        try {
          const response = await axios.get('/admin/user-locations');
          const locations = response.data;
          for (let location of locations) {
            const coords = await geocodeLocation(location.location);
            if (coords) addBubble(coords, location.location, location.pet_count);
          }
        } catch (error) {
          console.error('Failed to fetch or plot locations:', error);
        }
      }
  
      onBeforeUnmount(() => {
        markers.value.forEach(marker => marker.removeFrom(map.value)); // Remove markers from the map
        markers.value = []; // Clear markers array
        if (map.value) {
          map.value.remove(); // Cleans up the map instance
          map.value = null;
        }
      });
  
      return {};
    },
  };
  </script>
  
  <style>
  /* Additional styles can go here */
  </style>
  