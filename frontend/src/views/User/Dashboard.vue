<template>
    <v-card class="fill-height" flat>
    <v-toolbar class="bg-surface ">  
        <div class="pl-3">
            <v-img
            :width="45"
    class="mx-auto"
            aspect-ratio="1/1"
            src="@/assets/images/pic2.png"
           
          ></v-img>
        </div>
            <v-toolbar-title class="font-weight-black  text-primary text-h6">Vopet</v-toolbar-title>
            <v-spacer></v-spacer>
           
            <v-btn variant="flat" color="secondary"  size="x-small" @click="userStore.logout">
               Logout 
            </v-btn></v-toolbar>
            <v-col
            cols="12"
            sm="8"
            md="4"
            class="mx-auto pb-0 mb-0"
            
          >
            <v-card  variant="tonal" class="bg-primary mb-3">
                <v-card-item>
                  <v-card-title>Welcome {{ userDetails.fname }} </v-card-title>
          
                  <v-card-subtitle>{{ userDetails.email }}</v-card-subtitle>
                </v-card-item>
          
                <v-card-text>
                    Veterinary Office Pet Adoption System with GIS Technology
                </v-card-text>
            </v-card>
            <v-row justify="center">
                <v-col cols="4"   class="d-flex align-stretch">
                  <v-card class="d-flex flex-column justify-center align-center" flat>
                    <v-card-title class="text-center">{{ petCounts.available }}</v-card-title>                    <v-card-subtitle class="text-center">Pets Available</v-card-subtitle>
                  </v-card>
                </v-col>
              
                <v-col cols="4"  class="d-flex align-stretch">
                  <v-card class="d-flex flex-column justify-center align-center" flat>
                    <v-card-title class="text-center">{{  petCounts.processing }}</v-card-title>
                    <v-card-subtitle class="text-center">Pets Proccessing</v-card-subtitle>
                  </v-card>
                </v-col>
              
                <v-col cols="4"  class="d-flex align-stretch">
                  <v-card class="d-flex flex-column justify-center align-center" flat>
                    <v-card-title class="text-center">{{ petCounts.adopted }}</v-card-title>
                    <v-card-subtitle class="text-center">Pets Adopted</v-card-subtitle>
                  </v-card>
                </v-col>
              </v-row>
              <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between mt-3">
                <a></a>
        
                <router-link
                class="text-caption text-decoration-none text-primary mb-3"
                to="/petGallery" 
              >
                View Pet Gallery<v-icon icon="mdi-chevron-right"></v-icon>
              </router-link>
              </div>
              


         
              <v-card class="mx-auto pa-3 mb-4"  variant="tonal">
                <v-row>
                  <v-col v-for="(pet, index) in recentPets" :key="index" cols="3" >
                    <router-link :to="{ name: 'petGallery' }">
 
                    <v-img
                      class="text-white rounded"
                      height="100"
                    width="70"
                      :src="pet.imageUrl"
                      cover
                    ></v-img>
                    </router-link>
                  </v-col>
                </v-row>
              </v-card>
              
            
      
              <v-card class="mb-6" variant="tonal">
                <v-card-subtitle class="px-4 pt-4 text-subtitle-1 font-weight-bold">
                  PET ADOPTION PROGRAM
                  <br>
                  <span class="text-caption">TERMS AND CONDITIONS:</span>
                </v-card-subtitle>
                <v-card-text class="px-4 py-2">
                  <div class="text-caption font-weight-meduim text-grey ">1. Must be a Filipino Citizen</div>
                  <div class="text-caption font-weight-meduim text-grey ">2. Must be at least 18 years old and above</div>
                  <div class="text-caption font-weight-meduim text-grey ">3. Must be able to provide proper food and shelter for adopted animal</div>
                  <div class="text-caption font-weight-meduim text-grey ">4. Preferably resident of the City to facilitate monitoring</div>
                  <div class="text-caption font-weight-meduim text-grey ">5. Willing to abide by the Conditions as stipulated in the adoption agreement, to mention:</div>
                  <div class="text-caption font-weight-meduim text-grey ml-4 ">- The Office may periodically check the condition of the animal while in the care of adopter</div>
                  <div class="text-caption font-weight-meduim text-grey ml-4 ">- In case of any sign of illness, adopter must report and submit the animal for proper treatment to the City Veterinary Office</div>
                  <div class="text-caption font-weight-meduim text-grey ml-4 ">- Adopter may not sell nor transfer the ownership of the animal without permission from the City Veterinary Office</div>
                </v-card-text>
              </v-card>
              
              </v-col>
              
            </v-card>
            
</template>
<script setup>
import { baseUrl } from '@/config/config.js';
import { useUserStore } from '@/stores/userStore'; // Adjust the path to your store as needed
import { ref, onMounted  } from 'vue';
import axios from 'axios';
// Use the store
const userStore = useUserStore();
const userDetails = userStore.userDetails;

const petCounts = ref({
  available: 0,
  processing: 0,
  adopted: 0,
});
const recentPets = ref([]);


const fetchPetCounts = async () => {
  try {
    const response = await axios.get('/user/petCounts', {
      headers: { Authorization: `Bearer ${userStore.token}` }
    }); // Replace with your API endpoint

    const { available, processing, adopted } = response.data;

    petCounts.value.available = available;
    petCounts.value.processing = processing;
    petCounts.value.adopted = adopted;
  } catch (error) {
    console.error('Error fetching pet counts:', error);
  }
};

const fetchRecentPets = async () => {
  try {
    const response = await axios.get('user/recent-pets'); // Replace with your API endpoint
    recentPets.value = response.data.map((pet) => ({
      imageUrl: pet.photo ? `${baseUrl}${'pets/'}${pet.photo}` : `${baseUrl}default.jpg`, // Use pet.photo if available, else use a default image
    }));
  } catch (error) {
    console.error('Failed to fetch recent pets:', error);
  }
};



onMounted(() => {
  fetchPetCounts();
  fetchRecentPets();
});
  </script>