import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import vuetify from './plugins/vuetify'
import { loadFonts } from './plugins/webfontloader'
import { createPinia } from 'pinia';
import axios from 'axios'
import './registerServiceWorker'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { useUserStore } from '@/stores/userStore'; // Adjust the path to your store as needed



axios.defaults.baseURL = "http://localhost:8080/"

setInterval(function checkTokenExpiration() {
  const userStore = useUserStore();
  if (userStore.isAuthenticated && new Date() >= new Date(userStore.tokenExpiration)) {
    userStore.session_logout();
  }
}, 60000);

const pinia = createPinia();
pinia.use(piniaPluginPersistedstate)
loadFonts()

createApp(App)
  .use(router)
  .use(vuetify)
  .use(pinia)
  .mount('#app')
