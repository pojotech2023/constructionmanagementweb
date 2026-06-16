// public/firebase-messaging-sw.js

importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyBkGxTIkwa48TQHXDqFSIWbuMvDFbI_5ZU",
    authDomain: "siginifynotification.firebaseapp.com",
    projectId: "siginifynotification",
    storageBucket: "siginifynotification.firebasestorage.app",
    messagingSenderId: "10288476650",
    appId: "1:10288476650:web:1d26d433dfd18e804cc293"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function (payload) {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/icon.png' // optional, or use your favicon
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});