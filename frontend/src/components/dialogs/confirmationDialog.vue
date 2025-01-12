<!-- ConfirmationDialog.vue -->
<template>
    <v-dialog v-model="showDialog" max-width="500px">
      <v-card>
        <v-toolbar :color="color">
        <v-card-title >{{ title }}</v-card-title>
    </v-toolbar>
        <v-card-text>{{ message }}</v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="confirm">Confirm</v-btn>
          <v-btn color="grey" @click="cancel">Cancel</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const props = defineProps({
    title: String,
    message: String,
    color: String,
  });
  
  const emit = defineEmits(['confirm', 'cancel']);
  
  const showDialog = ref(false);
  
  function openDialog() {
    showDialog.value = true;
  }
  
  function closeDialog() {
    showDialog.value = false;
  }
  
  function confirm() {
    emit('confirm');
    closeDialog();
  }
  
  function cancel() {
    emit('cancel');
    closeDialog();
  }
  
  defineExpose({ openDialog, closeDialog });
  </script>
  