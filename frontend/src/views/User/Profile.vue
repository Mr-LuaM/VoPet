<template>
    <v-card flat >
      <v-toolbar
        color="primary"
        extended
        height="500px"
        style="height: 130px; outline-bottom: 4px solid #FE7839;"
        class="rounded-b"
      >
      <v-toolbar-title class="font-weight-bold mt-6">Profile</v-toolbar-title>
      </v-toolbar>
  
      <v-card
  class="mx-auto rounded-circle"
  max-width="130px"

  style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;">
  <v-avatar size="120"> 
    <v-img
    :src="avatarUrl" alt="Profile Picture" 
    ></v-img>
  </v-avatar>
</v-card>

<div class="text-center font-weight-bold text-h6 pt-3">{{ userDetails.fname }} {{ userDetails.lname }}</div>
    <div class="text-center text-caption ">{{ userDetails.role }}</div>
  
    <v-col
    cols="12"
    sm="8"
    md="4"
    class="mx-auto pa-0"
    
  >
<div class="pa-3"> <v-divider></v-divider></div>

<div class="pa-3"> 
    <router-link :to="{ name: 'edit-profile' }" tag="v-btn" class=" text-decoration-none" > <v-btn
    block
    class=" mt-2 rounded-pill"
    color="secondary"
    size="large"
   
  >

    Edit Profile
  </v-btn>
  </router-link>
  <router-link :to="{ name: 'edit-contacts' }" tag="v-btn" class=" text-decoration-none" >
  <v-btn
  block
  class=" mt-2 rounded-pill"
  color="secondary"
  size="large"
>
  Edit Contacts
</v-btn>
</router-link>

<router-link :to="{ name: 'edit-address' }" tag="v-btn" class=" text-decoration-none" >
    <v-btn
    block
    class=" mt-2 rounded-pill"
    color="secondary"
    size="large"
  >
    Edit Address
  </v-btn>
  </router-link>


  <router-link :to="{ name: 'security' }" tag="v-btn" class=" text-decoration-none" >
    <v-btn
    block
    class=" mt-2 rounded-pill"
    color="secondary"
    size="large"
  >
    Security
  </v-btn>
  </router-link>
</div>


<div class="pa-3"> <v-divider></v-divider></div>


<div class="pa-3">  

    <router-link :to="{ name: 'adoption-history' }" tag="v-btn" class=" text-decoration-none" >
        <v-btn
        block
        class=" mt-2 rounded-pill"
        color="secondary"
        size="large"
      >
        Adoption History
      </v-btn>
      </router-link>


</div>


<div class="pa-3"> <v-divider></v-divider></div>
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
import { useImageUrl } from '@/composables/useImageUrl';
// Use the store
const userStore = useUserStore();
const userDetails = userStore.userDetails;

const { imageUrl: avatarUrl } = useImageUrl(userStore.userDetails.picture_url);
  </script>