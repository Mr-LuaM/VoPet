<template>
    <v-container
    class="fill-height  "
    fluid
  >
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
        :width="200"
        aspect-ratio="1/1"
        src="../assets/images/pic5.png"
        class=" mx-auto"
      ></v-img>
      </div>
    <v-card
      class="py-8 px-6 text-center mx-auto ma-4 bg-background rounded-lg"
   
  flat
      max-width="400"
      width="100%"
    >
      <h3 class="text-h6 mb-4">Verify Your Account</h3>
  
      <div class="text-body-2">
        We sent a verification code to {{email}} <br>
  
        Please check your email and paste the code below.
      </div>
  
      <v-sheet color="background">
        <v-otp-input
          v-model="otp"
          variant="underlined"
        ></v-otp-input>
      </v-sheet>
  
      <v-btn
        class="my-4"
        color="secondary"
        height="40"
        text="Verify"
        variant="flat"
        width="70%"
      ></v-btn>
  
      <div class="text-caption">
        Didn't receive the code? <a href="#" @click.prevent="otp = ''"       class="text-primary text-decoration-none text-caption" rel="noopener noreferrer" >Resend</a>
      </div>
      
    </v-card>
</v-col>
</v-row>
</v-container>
  </template>
  <script setup>
  import { shallowRef } from 'vue'

  const otp = shallowRef('')
</script>