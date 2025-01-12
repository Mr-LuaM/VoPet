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
  storageBucket: "push-notifcation-99402.firebasestorage.app",
  messagingSenderId: "111047369335",
  appId: "1:111047369335:web:d1e8ade7bd132d4e7d5c72",
  measurementId: "G-456GWRH1WY",
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// Register the service worker and get the token
navigator.serviceWorker
  .register("/sw.js")
  .then((registration) => {
    console.log("Service Worker registered successfully:", registration);

    // Use the registered service worker for FCM
    return getToken(messaging, {
      vapidKey: "BFs_NcV0kbT93_DasIB8EwN2ed2SyWTI5A66kO0-aimi8x61IlMzncJyeQ-_L-2YBJ2OPvLP7jFWpiUmYkFQCMU",
      serviceWorkerRegistration: registration,
    });
  })
  .then((currentToken) => {
    if (currentToken) {
      console.log("Service Worker registered successfully:", currentToken);
      const userStore = useUserStore();

      if (userStore.token) {
        if (router.currentRoute.value.path !== "/login") {
          // Save the FCM token to the server
          axios
            .post(
              "auth/save-token",
              { fcm_token: currentToken },
              {
                headers: { Authorization: `Bearer ${userStore.token}` },
              }
            )
            .then(() => {
              console.log("FCM token saved successfully.");
            })
            .catch((error) => {
              console.error("Error saving FCM token:", error);
            });
        }
        console.log("Registration token:", currentToken);
      } else {
        console.log("No user token available. Unable to save registration token.");
      }
    } else {
      console.log("No registration token available. Request permission to generate one.");
    }
  })
  .catch((err) => {
    console.error("An error occurred while retrieving the registration token:", err);
  });

// Handle incoming messages
onMessage(messaging, (payload) => {
  console.log("Message received:", payload);
  // Handle the incoming message as needed
});

export { app, messaging };
