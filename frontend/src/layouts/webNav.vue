<template>
  <v-app class="app-container">      <!-- App Bar -->
      <v-app-bar app color="primary" class="rounded-b-m">
        <v-app-bar-nav-icon @click="toggleSidebar"></v-app-bar-nav-icon>
        <v-toolbar-title class="ml-2">Admin</v-toolbar-title>
        <v-spacer></v-spacer>
      </v-app-bar>
  
      <!-- Navigation Drawer -->
      <v-navigation-drawer app v-model="sidebarOpen" class=" rounded-e bg-primary-darken-1">
        <div class="text-center ">
        <v-img width="150" :aspect-ratio="1" src="@/assets/images/pic2.png" cover class="pa-12 ma-3 mx-auto "></v-img>
      </div>  <div class="text-center font-weight-black text-h6  ">Vopet</div>

    <v-list density="comfortable" nav>
  <v-list-item
    link
    to="/admin/dashboard"
    prepend-icon="mdi-view-dashboard"
  >
    <v-list-item-title>Dashboard</v-list-item-title>
  </v-list-item>

  <!-- Divider -->
  <v-container>
    <v-divider></v-divider>
  </v-container>

  <!-- Pet Management -->
  <v-list-item
    link
    to="/admin/petManagement"
    prepend-icon="mdi-dog"
  >
    <v-list-item-title>Pet Management</v-list-item-title>
  </v-list-item>

  <!-- Account Management -->
  <v-list-item
    link
    to="/admin/accountManagement"
    prepend-icon="mdi-account-group"
  >
    <v-list-item-title>Account Management</v-list-item-title>
  </v-list-item>

  <!-- Divider -->
  <v-container>
    <v-divider></v-divider>
  </v-container>

  <!-- Transaction -->
  <v-list-item
    link
    to="/admin/transaction"
    prepend-icon="mdi-book-open-page-variant"
  >
    <v-list-item-title>Transaction</v-list-item-title>
  </v-list-item>

  <!-- History -->
  <v-list-item
    link
    to="/admin/history"
    prepend-icon="mdi-history"
  >
    <v-list-item-title>History</v-list-item-title>
  </v-list-item>

   <!-- History -->
   <v-list-item
   link
   to="/admin/medicalHistory"
   prepend-icon="mdi-medical-bag"
 >
   <v-list-item-title>Medical History</v-list-item-title>
 </v-list-item>

    <!-- History -->
    <v-list-item
    link
    to="/admin/rescue"
    prepend-icon="mdi-ambulance"
  >
    <v-list-item-title>Pets to be rescued</v-list-item-title>
  </v-list-item>

  <!-- Divider -->
  <v-container>
    <v-divider></v-divider>
  </v-container>

  <!-- Message -->
  <v-list-item
    link
    to="/admin/message"
    prepend-icon="mdi-message-reply-text-outline"
  >
    <v-list-item-title>Message</v-list-item-title>
  </v-list-item>

  <!-- Announcements -->
  <v-list-item
    link
    to="/admin/announcements"
    prepend-icon="mdi-post"
  >
    <v-list-item-title>Announcements</v-list-item-title>
  </v-list-item>

  <!-- Divider -->
  <v-container>
    <v-divider></v-divider>
  </v-container>

  <!-- Profile -->
  <v-list-item
    link
    to="/admin/profile"
    prepend-icon="mdi-account-edit"
  >
    <v-list-item-title>Profile</v-list-item-title>
  </v-list-item>
</v-list>

        <template v-slot:append>
          <div class="pa-2">
            <v-btn @click="goToSettings" block variant="flat" class="bg-info">Settings</v-btn>
          </div>
          <div class="pa-2">
            <v-btn @click="userStore.logout" block variant="flat"  class="bg-error">Logout</v-btn>
          </div>
        </template>
      </v-navigation-drawer>
  
      <!-- Main Content -->
      <v-main  class="main-content" >
        
      
          <div class="pa-2">
        <v-breadcrumbs :items="breadcrumbs">
          <template v-slot:prepend>
            <v-icon size="small">mdi-home</v-icon>
          </template>
        </v-breadcrumbs>
        </div>
        <router-view>
        </router-view>
      </v-main>
    </v-app>
  </template>
  <script setup>
  import { ref, watch, onUnmounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useUserStore } from '@/stores/userStore';
  
  const router = useRouter();
  const route = useRoute();
  const userStore = useUserStore();
  const sidebarOpen = ref(window.innerWidth > 768); // Initialize directly without needing onMounted
  const breadcrumbs = ref([]);
  
  // Function to update breadcrumbs based on current route
  const updateBreadcrumbs = () => {
    const routeSegments = route.path.split('/').filter(segment => segment !== '');
    breadcrumbs.value = routeSegments.map(segment => {
      // Optionally, you can add more logic here to customize breadcrumb names
      // For example, using route names or meta fields
      return segment.charAt(0).toUpperCase() + segment.slice(1);
    });
  };
  const goToSettings = () => {
  router.push('settings');
}
  // Watch for route changes and update breadcrumbs
  watch(route, () => {
    updateBreadcrumbs();
  }, { immediate: true });
  
  // Handle sidebar state based on window resize
  const handleResize = () => {
    sidebarOpen.value = window.innerWidth > 768;
  };
  window.addEventListener('resize', handleResize);
  
  // Cleanup to remove event listener when component unmounts
  onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
  });
  
  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
  };
  </script>
  <style>
  /* Ensure the app container fits the entire viewport */
  .app-container {
    height: 100vh;
    overflow: hidden; /* Prevent the app container from scrolling */
  }
  
  /* Main content area styling */
  .main-content {
    height: calc(100vh - 48px); /* Adjust based on app bar height */
    overflow-y: auto; /* Allow the main content area to scroll */
  }
  </style>
  
  
