<template>
    <v-snackbar v-model="show" :timeout="timeout" :color="color">
      {{ message }}
  
      <template v-slot:action="{ attrs }">
        <v-btn color="white" text v-bind="attrs" @click="show = false">
          Close
        </v-btn>
      </template>
    </v-snackbar>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const show = ref(false);
  const message = ref('');
  const timeout = ref(3000);
  const color = ref('success');
  
  // Function to open the Snackbar from the parent component
  function openSnackbar(newMessage, newColor = 'success', newTimeout = 3000) {
    message.value = newMessage;
    color.value = newColor;
    timeout.value = newTimeout;
    show.value = true;
  }
  
  // Expose the openSnackbar function
  defineExpose({ openSnackbar });
  </script>
  