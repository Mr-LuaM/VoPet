<template>
    <div ref="mapContainer" style="height: 900px;"></div>
  </template>
  
  <script>
  import { onMounted, ref } from 'vue';
  import L from 'leaflet';
  import 'leaflet/dist/leaflet.css';
  import 'leaflet.locatecontrol';
  import 'leaflet.locatecontrol/dist/L.Control.Locate.min.css';
  
  export default {
    setup() {
      const mapContainer = ref(null);
      let mapInstance = null;
  
      onMounted(() => {
        if (!mapInstance) {
          mapInstance = L.map(mapContainer.value).setView([0, 0], 1);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
          }).addTo(mapInstance);
          locateUser(mapInstance);
          L.control.locate({ keepCurrentZoomLevel: true }).addTo(mapInstance);
        }
      });
  
      async function locateUser(map) {
        map.locate({ setView: true, maxZoom: 16 });
        map.on('locationfound', async (e) => {
          const radius = e.accuracy / 2;
  
          const userIcon = L.icon({
            iconUrl: '/img/user_marker.png',
            iconSize: [38, 38],
            iconAnchor: [19, 38],
            popupAnchor: [0, -38]
          });
  
          const marker = L.marker(e.latlng, { icon: userIcon }).addTo(map);
          marker.bindPopup(`<b>You are here</b><br>Latitude: ${e.latlng.lat.toFixed(5)}<br>Longitude: ${e.latlng.lng.toFixed(5)}`).openPopup();
  
          L.circle(e.latlng, radius).addTo(map);
  
          try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}`);
            const data = await response.json();
            const locationName = data.display_name;
  
            marker.setPopupContent(`<b>You are here:</b><br>${locationName}<br>Latitude: ${e.latlng.lat.toFixed(5)}<br>Longitude: ${e.latlng.lng.toFixed(5)}`).openPopup();
            fetchNearbyVeterinary(map, e.latlng);
          } catch (error) {
            console.error("Failed to fetch location name:", error);
          }
        });
      }
  
      async function fetchNearbyVeterinary(map, latlng) {
        const bbox = `${latlng.lat-0.1},${latlng.lng-0.1},${latlng.lat+0.1},${latlng.lng+0.1}`;
        const overpassUrl = `https://overpass-api.de/api/interpreter?data=[out:json];(node[amenity=veterinary](${bbox}););out;`;
  
        try {
          const response = await fetch(overpassUrl);
          const data = await response.json();
  
          const vetIcon = L.icon({
            iconUrl: '/img/pet_marker.png',
            iconSize: [38, 38],
            iconAnchor: [19, 19],
            popupAnchor: [0, -19]
          });
  
          data.elements.forEach((clinic) => {
            const clinicName = clinic.tags.name || 'Veterinary Clinic';
            const clinicAddress = clinic.tags['addr:street'] ? clinic.tags['addr:street'] : 'Address not available';
            const clinicDetails = `${clinicName}<br>${clinicAddress}`;
  
            L.marker([clinic.lat, clinic.lon], { icon: vetIcon })
              .addTo(map)
              .bindPopup(clinicDetails);
          });
        } catch (error) {
          console.error("Failed to fetch veterinary clinics:", error);
        }
      }
  
      return { mapContainer };
    },
  };
  </script>
  
  <style scoped>
  #map { height: 100%; }
  </style>
  