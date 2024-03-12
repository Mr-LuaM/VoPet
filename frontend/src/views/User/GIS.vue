<template>
  <div>
    <v-btn icon style="z-index: 1000; position: fixed; top: 20px; right: 20px;" @click="openCameraDialog">
      <v-icon>mdi-camera</v-icon>
    </v-btn>
    <div ref="mapContainer" style="height: 900px;"></div>

    <v-dialog  transition="dialog-top-transition"
    width="auto"
    v-model="dialog"
    persistent
    scrollable>
      <template v-slot:activator="{ isActive }"></template>
      <v-card>
        <v-toolbar color="secondary">
          <v-toolbar-title>Pet Ture</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn variant="plain" @click="switchCamera" icon>
            <v-icon>mdi-camera-switch</v-icon>
          </v-btn>
          <v-btn variant="plain" @click="closeCameraDialog" icon>
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>
        
        <div class="text-center font-weight-bold text-caption pt-3 text-primary">{{ locationName }}</div>
        <div class="text-center font-weight-bold text-caption">{{ latitude }}, {{ longitude }}</div>
        <v-card-text align="center" justify="center">
          <video class="rounded" ref="videoElement" autoplay style="max-width: 200px;"></video>
        
          <v-btn
          block
          class="mt-8"
          color="secondary"
          @click="takePhoto"
          :loading="loading"
        >
          Take Photo
        </v-btn></v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="closeCameraDialog">Close</v-btn>
        </v-card-actions>
       
   
      </v-card>
    </v-dialog>
    <DynamicSnackbar ref="snackbar" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.locatecontrol';
import 'leaflet.locatecontrol/dist/L.Control.Locate.min.css';
import axios from 'axios';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
const userStore = useUserStore();



const dialog = ref(false);
const locationName = ref('');
const latitude = ref('');
const longitude = ref('');
const mapContainer = ref(null);
const videoElement = ref(null);
const photoUrl = ref(null);
const loading = ref(false);
const snackbar = ref(null);

const usingRearCamera = ref(false);


const openCameraDialog = () => {
  dialog.value = true;
  openCamera();
};

const closeCameraDialog = () => {
  dialog.value = false;
  closeCamera();
};

const switchCamera = () => {
  usingRearCamera.value = !usingRearCamera.value;
  openCamera();
};

const openCamera = () => {
  const constraints = {
    video: { facingMode: usingRearCamera.value ? "environment" : "user" }
  };

  navigator.mediaDevices.getUserMedia(constraints)
    .then((stream) => {
      const video = videoElement.value;
      video.srcObject = stream;
    })
    .catch((error) => {
      console.error('Failed to open camera:', error);
      closeCameraDialog();
    });
};


const closeCamera = () => {
  const video = videoElement.value;
  const stream = video.srcObject;
  const tracks = stream.getTracks();
  tracks.forEach((track) => {
    track.stop();
  });
};

const takePhoto = async () => {
  const video = videoElement.value;
  const canvas = document.createElement('canvas');
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const context = canvas.getContext('2d');
  context.drawImage(video, 0, 0, canvas.width, canvas.height);
  const dataUrl = canvas.toDataURL('image/jpeg');

  // Convert DataURL to Blob
  const fetchRes = await fetch(dataUrl);
  const blob = await fetchRes.blob();

  // Preliminary validation to ensure no form data is missing
  if (!blob || !locationName.value || !latitude.value || !longitude.value) {
    // Assuming you have a snackbar ref set up to show error messages
    if (snackbar.value) {
      snackbar.value.openSnackbar('All fields are required.', 'error');
    }
    return; // Stop execution if validation fails
  }

  // Use FormData to package the image and location data
  const formData = new FormData();
  formData.append('photo', blob, 'captured_photo.jpg');
  formData.append('locationName', locationName.value);
  formData.append('latitude', latitude.value);
  formData.append('longitude', longitude.value);

  // Send the data to the backend
  try {
    const response = await axios.post('user/petture', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        // Correct way to set Authorization header
        'Authorization': `Bearer ${userStore.token}`
      },
    });

    // Check if the response is successful
    if (response.status === 201 && response.data.message === "File and location data saved successfully") {
      console.log('Photo and location data sent successfully.', response.data);
      // Show success message
      if (snackbar.value) {
        snackbar.value.openSnackbar(response.data.message, 'success');
      }
      // Close the camera dialog or perform any other success actions here
      closeCameraDialog();
    }
  } catch (error) {
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    // Show error message
    if (snackbar.value) {
      snackbar.value.openSnackbar(errorMessage, 'error');
    }
  }
};




const mapInstance = ref(null);

onMounted(() => {
  if (!mapInstance.value) {
    mapInstance.value = L.map(mapContainer.value).setView([0, 0], 1);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
    }).addTo(mapInstance.value);
    locateUser(mapInstance.value);
    L.control.locate({ keepCurrentZoomLevel: true }).addTo(mapInstance.value);
  }
});

const locateUser = (map) => {
  map.locate({ setView: true, maxZoom: 16, enableHighAccuracy: true });
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
      const name = data.display_name;
      loading.value = true;

      marker.setPopupContent(`<b>You are here:</b><br>${name}<br>Latitude: ${e.latlng.lat.toFixed(5)}<br>Longitude: ${e.latlng.lng.toFixed(5)}`).openPopup();
      locationName.value = name;
      latitude.value = e.latlng.lat.toFixed(5);
      longitude.value = e.latlng.lng.toFixed(5);
      fetchNearbyVeterinary(map, e.latlng);
      
    } catch (error) {
      console.error("Failed to fetch location name:", error);
    }finally {loading.value = false;}
  }
  );

  map.on('locationerror', (e) => {
    console.error("Failed to get your location:", e.message);
  });
  L.Popup.prototype._animateZoom = function (e) {
  if (!this._map) {
    return
  }
}
};



const fetchNearbyVeterinary = async (map, latlng) => {
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
      const name = clinic.tags.name || 'Veterinary Clinic';
      const address = clinic.tags['addr:street'] ? clinic.tags['addr:street'] : 'Address not available';
      const details = `${name}<br>${address}`;

      L.marker([clinic.lat, clinic.lon], { icon: vetIcon })
        .addTo(map)
        .bindPopup(details);
    });
  } catch (error) {
    console.error("Failed to fetch veterinary clinics:", error);
  }
};
</script>

