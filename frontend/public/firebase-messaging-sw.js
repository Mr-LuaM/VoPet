// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in
// your app's Firebase config object.
// https://firebase.google.com/docs/web/setup#config-object
firebase.initializeApp({
    apiKey: "AIzaSyAgBNr4511jtM16IKwtCUDuHL2SZVE5y2s",
    authDomain: "push-notifcation-99402.firebaseapp.com",
    projectId: "push-notifcation-99402",
    storageBucket: "push-notifcation-99402.appspot.com",
    messagingSenderId: "111047369335",
    appId: "1:111047369335:web:d1e8ade7bd132d4e7d5c72",
    measurementId: "G-456GWRH1WY"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();
messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
  
    // Extracting notification data
    const notificationData = payload.notification;
    const notificationTitle = notificationData.title;
    const notificationOptions = {
      body: notificationData.body,
      icon: 'assets/img/logo/pnp-logo.png', // Fallback icon, adjust the path as needed
      image: notificationData.image, // Large image from payload
      // You can add more options here as needed
    };
  
    // Show the custom notification
    self.registration.showNotification(notificationTitle, notificationOptions);
});