<template>
    <v-row
    align="center"
    justify="center"
  >
    <v-col
      cols="12"
      sm="8"
      md="4"
    >
      <div class="text-center">
    <v-img

    aspect-ratio="16/9"
    cover
    src="@/assets/401.png"
  ></v-img>
  </div>
  <div class="text-center text-caption font-weight-bold mt-n8 mb-8">Hey, you're not allowed here</div>

  <div class="text-center text-h4 font-weight-bold">401: Unathorized</div>
  <v-card-text class="text-center">
    <router-link
      class="text-primary text-decoration-none text-caption"
      to="/login"
      rel="noopener noreferrer"
  
    >
      Back to login <v-icon icon="mdi-chevron-right"></v-icon>
    </router-link>
    
  </v-card-text>
  </v-col>
  </v-row>
</template>