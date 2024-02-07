<template>
    <v-app>
      <!-- App Bar -->
      <v-app-bar app color="primary" class="rounded-b-m">
        <v-app-bar-nav-icon @click="toggleSidebar"></v-app-bar-nav-icon>
        <v-toolbar-title class="ml-2">Admin</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-app-bar>
  
      <!-- Navigation Drawer -->
      <v-navigation-drawer app v-model="sidebarOpen" class="elevation-12 rounded-e">
        <v-img width="300" :aspect-ratio="1" src="@/assets/images/pic6.png" cover class="pa-10"></v-img>
        <v-list density="comfortable" nav>
          <v-list-item>
            <v-list-item-title>Dashboard</v-list-item-title>
          </v-list-item>
          <!-- Additional items -->
        </v-list>
        <template v-slot:append>
          <div class="pa-2">
            <v-btn @click="logout" block>Logout</v-btn>
          </div>
        </template>
      </v-navigation-drawer>
  
      <!-- Main Content -->
      <v-main>
        <router-view></router-view>
      </v-main>
    </v-app>
  </template>
  <script setup>
import { ref, onMounted, onUnmounted, watchEffect } from 'vue';
  
  const sidebarOpen = ref(false);
  
  onMounted(() => {
    // Set the initial state of the sidebar based on the window width
    sidebarOpen.value = window.innerWidth > 768; // Use 768px as the breakpoint between mobile and desktop
    // You can adjust this value based on your design's breakpoint
  
    // Optional: Resize listener to set initial state if you want to handle dynamic resizing
    // This part is optional and can be omitted if you only care about the initial state at page load
    const handleResize = () => {
      sidebarOpen.value = window.innerWidth > 768;
    };
  
    window.addEventListener('resize', handleResize);
  
    // Cleanup to remove event listener when component unmounts
    onUnmounted(() => {
      window.removeEventListener('resize', handleResize);
    });
  });
  
  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
  };
  </script>
  
  
