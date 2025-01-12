<template>
    <v-card flat class="bg-background">
      <v-toolbar color="primary" extended style="height: 130px; outline-bottom: 4px solid #FE7839;" class="rounded-b">
        <v-toolbar-title class="font-weight-bold mt-6">Contact Details</v-toolbar-title>
      </v-toolbar>
  
      <v-card class="mx-auto rounded-circle" max-width="130px" style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;">
        <v-avatar size="120">
          <v-img src="@/assets/images/pic13.png" alt="Profile Picture"></v-img>
        </v-avatar>
      </v-card>
  
      <div v-for="(detail, index) in contactDetails" :key="index" class="my-2 bg-background">
        <div class="text-center font-weight-bold text-body-1 pt-3 text-decoration-none bg-background">
          <!-- Use a computed property or method to dynamically render the correct HTML element -->
          <template v-if="detail.type.toLowerCase()  === 'phone'">
            <a class="text-decoration-none text-black" :href="`tel:${detail.value}`">{{ detail.value }}</a>
          </template>
          <template v-else-if="detail.type.toLowerCase() === 'email'">
            <a class="text-decoration-none text-black" :href="`mailto:${detail.value}`">{{ detail.value }}</a>
          </template>
          <template v-else-if="detail.type.toLowerCase() === 'social media'">
            <a class="text-decoration-none text-black" :href="detail.value" target="_blank">{{ detail.description }}</a>
          </template>
          <template v-else>
            {{ detail.value }}
          </template>
        </div>
        <div class="text-center text-caption bg-background">{{ detail.description }}</div>
      </div>

      <v-col cols="12" sm="6" md="4" class="mx-auto">
      <div class="pa-3"> 
    <router-link :to="{ name: 'chat' }" tag="v-btn" class=" text-decoration-none" > <v-btn
    block
    class=" mt-2 rounded-pill"
    color="secondary"
    size="large"
   
  >

Chat
  </v-btn>
  </router-link>
  </div>
  </v-col>
    </v-card>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  
  const contactDetails = ref([]);
  
  onMounted(async () => {
    try {
      const response = await axios.get('user/getContacts');
      contactDetails.value = response.data.contacts; // Assuming API response structure
    } catch (error) {
      console.error('Error fetching contact details:', error);
    }
  });
  
  function getIcon(type) {
    switch (type.toLowerCase()) {
      case 'phone':
        return 'mdi-phone';
      case 'email':
        return 'mdi-email';
      case 'address':
        return 'mdi-map-marker';
      case 'social media':
        return 'mdi-web';
      default:
        return 'mdi-information-outline';
    }
  }
  </script>
  