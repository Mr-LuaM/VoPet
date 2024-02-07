<template>
      <v-tabs
        v-model="tab"
        color="secondary"
        align-tabs="center"
        bg-color="surface"
        hide-slider
        center-active
        density="compact"
   
        
      >
        <v-tab :value="1" >Personal</v-tab>
        <v-tab :value="2"  >Contact</v-tab>
        <v-tab :value="3"  >Address</v-tab>
        <v-tab :value="3"  >Security</v-tab>
      </v-tabs>

      <v-window v-model="tab">
        <v-window-item>
            <personalForm/>
        </v-window-item>
   
      </v-window>
   
  </template>
  <script setup>
  import { ref } from 'vue'
  import personalForm from '@/components/profileComponents/personalForm'

  const tab = ref(null)
</script>