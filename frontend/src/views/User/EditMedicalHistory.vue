<template>
    <v-card flat>
      <v-toolbar
        color="primary"
        extended
        height="500px"
        style="height: 130px; outline-bottom: 4px solid #FE7839;"
        class="rounded-b"
      >
        <v-btn
          icon
          class="hidden-xs-only mt-3"
          @click="router.push({ name: 'userProfile' });"
        >
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <v-toolbar-title class="font-weight-bold mt-3">Medical History</v-toolbar-title>
      </v-toolbar>
  
      <v-card
        class="mx-auto rounded-circle"
        max-width="130px"
        style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"
      >
        <v-avatar size="120"> <!-- You can adjust the size as needed -->
          <v-img src="@/assets/images/pic16.png" alt="Profile Picture"></v-img>
        </v-avatar>
      </v-card>
      
      <v-container style="max-height: 75vh; overflow-y: auto;"> <!-- Adjust max-height as needed -->
        <v-main>
          <div class="text-center font-weight-bold text-h6 pt-3">Pet Medical History</div>
  
          <v-col cols="12" sm="8" md="4" class="mx-auto px-10 pa-4">
            <v-list dense>
              <v-card v-for="(pet, index) in medicalHistoryData" :key="index" variant="outlined"  class="mb-4">
                <v-card-title>{{ pet.name }}</v-card-title>
                <v-card-text>
                  <p>Status: {{ pet.status }}</p>
                  <p>Age: {{ pet.age }}</p>
                  <p>Species: {{ pet.species }}</p>
                  <p>Breed: {{ pet.breed }}</p>
                  <p>Created At: {{ pet.created_at }}</p>
                  <v-list-item-group v-if="pet.medical_history.length > 0">
                    <v-list-item v-for="(condition, idx) in pet.medical_history" :key="idx">
                      <v-list-item-content>
                        <v-list-item-title>Condition: {{ condition.medical_condition }}</v-list-item-title>
                        <v-list-item-subtitle>Medication: {{ condition.medication }}</v-list-item-subtitle>
                        <v-list-item-subtitle>Dosage: {{ condition.dosage }}</v-list-item-subtitle>
                        <v-list-item-subtitle>Vaccination Type: {{ condition.vaccination_type }}</v-list-item-subtitle>
                        <v-list-item-subtitle>Vaccination Date: {{ condition.vaccination_date }}</v-list-item-subtitle>
                        <v-list-item-subtitle>Next Vaccination Date: {{ condition.next_vaccination_date }}</v-list-item-subtitle>
                        <!-- Add more fields as needed -->
                      </v-list-item-content>
                    </v-list-item>
                  </v-list-item-group>
                  <p v-else>No medical history available.</p>
                </v-card-text>
              </v-card>
            </v-list>
          </v-col>
        </v-main>
      </v-container>
    </v-card>
  </template>
  
  <script setup>
  import { ref, onMounted, shallowRef } from 'vue';
  import axios from 'axios';
  import { baseUrl } from '@/config/config.js';
  import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
  import { useRouter } from 'vue-router';
  
  const userStore = useUserStore();
  const router = useRouter();
  const itemsPerPage = shallowRef(1);
  const medicalHistoryData = ref([]);
  
  const fetchMedicalHistory = async () => {
    try {
      const response = await axios.get('user/medicalHistory', {
        headers: { Authorization: `Bearer ${userStore.token}` }
      });
      medicalHistoryData.value = response.data;
    } catch (error) {
      console.error('Failed to fetch medical history:', error);
    }
  };
  
  onMounted(() => {
    fetchMedicalHistory(); // Assuming you have a userId in your userStore
  });
  </script>
  