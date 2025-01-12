<template>
    <v-card flat >

<div class="text-center font-weight-bold text-h6 pt-3">Change Settings</div>
    <div class="text-center text-caption ">{{ userDetails.role }}</div>
  
    <v-col
    cols="12"
    sm="8"
    md="4"
    class="mx-auto pa-0"
    
  >
<div class="pa-3"> <v-divider></v-divider></div>

<div class="pa-3"> 
    <router-link :to="{ name: 'admin-edit-vet-info' }" tag="v-btn" class=" text-decoration-none" > <v-btn
    block
    class=" mt-2 rounded-pill"
    color="secondary"
    size="large"
   
  >

   Change Veterinary Information
  </v-btn>
  </router-link>
  <div class="pa-3"> <v-divider></v-divider></div>

  <!-- <router-link :to="{ name: 'admin-edit-app-name' }" tag="v-btn" class=" text-decoration-none" >
  <v-btn
  block
  class=" mt-2 rounded-pill"
  color="secondary"
  size="large"
>
Change App Name
</v-btn>
</router-link>

<router-link :to="{ name: 'admin-edit-address' }" tag="v-btn" class=" text-decoration-none" >
    <v-btn
    block
    class=" mt-2 rounded-pill"
    color="secondary"
    size="large"
  >
   Change Logo
  </v-btn>
  </router-link> -->


</div>


<!-- <div class="pa-3"> <v-divider></v-divider></div> -->





</v-col>

<v-card-text class="text-center">
    <!-- Use a button or a simple anchor tag for logout -->
    <v-btn
      class="text-primary text-decoration-none text-caption" variant="text"
      @click=" userStore.logout();"
    >
      Logout <v-icon icon="mdi-chevron-right"></v-icon>
    </v-btn>
  </v-card-text>
  
    </v-card>

   




    
  </template>
  <script setup>

import { useUserStore } from '@/stores/userStore'; // Adjust the path to your store as needed
import { ref, computed  } from 'vue';
// Use the store
const userStore = useUserStore();
const userDetails = userStore.userDetails;

  </script>