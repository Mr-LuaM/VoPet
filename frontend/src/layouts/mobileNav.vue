<template>
    <v-card flat>  
        <v-layout>
      <v-main >
        <router-view></router-view>
      </v-main>
    </v-layout>

        <v-bottom-navigation  v-model="value"   class="fixed-bottom-navigation" active color="primary"  grow :elevation="0" density="comfortable">
          <v-btn to="/user/dashboard" variant="flat">
            <v-icon>mdi-home</v-icon>
            Home
          </v-btn>
  
          <v-btn to="/user/announcements" variant="flat">
            <v-icon>mdi-bell</v-icon>
            News
          </v-btn>
  
          <v-btn to="/user/gis-map" >
          <v-img
                src="@/assets/icons/map.png"
                alt="John"
                aspect-ratio="1/1"
                :width="40"
                cover
              ></v-img>
           
          </v-btn>
  
          <v-btn to="/user/contact" variant="flat">
            <v-icon>mdi-phone</v-icon>
            Contact
          </v-btn>
          <v-btn to="/user/profile" variant="flat">
            <v-icon>mdi-human-greeting</v-icon>
            Profile
          </v-btn>
          
  
          
        </v-bottom-navigation>
  
    </v-card>
  </template>
  
  <style>
.main-content {
  flex: 1;
  overflow: auto; /* Ensure content can scroll independently of the navigation bar */
}
</style>

<script setup>
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';

const value = ref(0); // Default active index
const route = useRoute();

const routeToIndexMap = {
  '/user/dashboard': 0,
  '/user/announcements': 1,
  '/user/gis-map': 2,
  '/user/contact': 3,
  '/user/profile': 4,
};


watch(() => route.path, (newPath) => {
  console.log('New path:', newPath); // Debugging line
  value.value = routeToIndexMap[newPath] ?? null;
});
import { onMounted } from 'vue';

onMounted(() => {
  value.value = routeToIndexMap[route.path] ?? null;
});

</script>
