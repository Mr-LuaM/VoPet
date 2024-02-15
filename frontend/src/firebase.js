// src/firebase.js
import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import axios from "axios";
import router from "@/router"; // Adjust the path if necessary
import { useUserStore } from "@/stores/userStore"; // Adjust the path if necessary

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyAgBNr4511jtM16IKwtCUDuHL2SZVE5y2s",
    authDomain: "push-notifcation-99402.firebaseapp.com",
    projectId: "push-notifcation-99402",
    storageBucket: "push-notifcation-99402.appspot.com",
    messagingSenderId: "111047369335",
    appId: "1:111047369335:web:5449b870afd6c63f7d5c72",
    measurementId: "G-0H32RHR2XM"
  };
  ;

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

onMessage(messaging, (payload) => {
  console.log('Message received:', payload);
  // Handle the incoming message as needed
});

getToken(messaging, { vapidKey: 'BItLmAyAHXJOu-UTNLof3mhxYjJkprO-3OnTYnLUAFqkOlr942RLdytyPwU0CMiojif1DHZtFe-lVGgALrZkEok' }).then((currentToken) => {
    if (currentToken) {
    // Move the userStore initialization inside a component or setup function
    const userStore = useUserStore();

    if (userStore.token) {
      if (router.currentRoute.value.path !== '/login') {
        axios.post('auth/save-token', { fcm_token: currentToken }, {
          headers: { Authorization: `Bearer ${userStore.token}` }
        });
      }
      console.log('Registration token:', currentToken);
    } else {
      console.log('No user token available. Unable to save registration token.');
    }
  } else {
    console.log('No registration token available. Request permission to generate one.');
  }
}).catch((err) => {
  console.error('An error occurred while retrieving the registration token:', err);
});

export { app, messaging };
