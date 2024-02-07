<template>
    <v-card class="fill-height" flat>
    <v-toolbar class="bg-primary">  
        <v-toolbar-title class="font-weight-black text-h6">
            <!-- Use router-link to navigate back -->
            <router-link :to="{ name: 'userDashboard' }">
              <v-icon class="mt-n1" color="white">mdi-chevron-left</v-icon>
            </router-link>
            Discover
          </v-toolbar-title>            <v-spacer></v-spacer>
           
          
        </v-toolbar>
          
        
        <v-col
        cols="12"
        sm="8"
        md="4"
        class="mx-auto pb-0 mb-0 mt-9"
        
        
      >
      <v-card elevation="24" max-width="444" class="mx-auto">
        <!-- Display a loading indicator while loading -->
        <v-overlay :value="isLoading" color="white">
          <v-progress-circular indeterminate size="64"></v-progress-circular>
        </v-overlay>
    
        <!-- Carousel is displayed when not loading -->
        <v-carousel v-if="!isLoading" :continuous="true" hide-delimiters touch show-arrows="hover" progress="primary">
          <v-carousel-item v-for="(pet, i) in recentPets" :key="i">
            <v-img :src="pet.imageUrl" cover height="350"></v-img>
            <!-- Display pet details in a v-card -->
            <v-card class="mb-6" variant="tonal">
              <v-card-text class="px-4 py-2">
                <!-- Display pet details here -->
                <div class="text-caption font-weight-medium text-grey">Pet ID: {{ pet.pet_id }}</div>
                <div class="text-caption font-weight-medium text-grey">Name: {{ pet.name }}</div>
                <div class="text-caption font-weight-medium text-grey">Age: {{ pet.age }}</div>
                <div class="text-caption font-weight-medium text-grey">Species: {{ pet.species }}</div>
                <div class="text-caption font-weight-medium text-grey">Breed: {{ pet.breed }}</div>
                <div class="text-caption font-weight-medium text-grey">Status: {{ pet.status }}</div>
                <div class="text-caption font-weight-medium text-grey">Info: {{ pet.info }}</div>
                <div class="text-caption font-weight-medium text-grey">Created At: {{ pet.created_at }}</div>
                <div class="text-caption font-weight-medium text-grey">Updated At: {{ pet.updated_at }}</div>
              </v-card-text>
            </v-card>
          </v-carousel-item>
        </v-carousel>
      </v-card>
            </v-col>
          
            </v-card>
            
</template>
<script setup>
import { useUserStore } from '@/stores/userStore'; // Adjust the path to your store as needed
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Import useRouter
import { baseUrl } from '@/config/config.js';

const userStore = useUserStore();
const userDetails = userStore.userDetails;
const router = useRouter(); // Access the router instance

const recentPets = ref([]); // Define the recentPets ref
const isLoading = ref(true); // Define a loading state

const fetchRecentPets = async () => {
  try {
    const response = await axios.get('user/recent-pets'); // Replace with your API endpoint
    recentPets.value = response.data.map((pet) => ({
      imageUrl: pet.photo ? `${baseUrl}${'pets/'}${pet.photo}` : `${baseUrl}default.jpg`, // Use pet.photo if available, else use a default image
      ...pet, // Include all pet details in the pet object
    }));
  } catch (error) {
    console.error('Failed to fetch recent pets:', error);
  } finally {
    isLoading.value = false; // Set loading state to false when loading is complete
  }
};

onMounted(() => {
  fetchRecentPets();
});
</script>