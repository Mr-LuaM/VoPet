<template>
    <v-card flat>
      <v-toolbar
        color="primary"
        extended
        height="500px"
        style="height: 130px; outline-bottom: 4px solid #FE7839;"
        class="rounded-b"
      >
        <v-btn icon class="hidden-xs-only mt-3" @click="router.push({ name: 'userProfile' })">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <v-toolbar-title class="font-weight-bold mt-3">Pet Saved History</v-toolbar-title>
      </v-toolbar>
  
      <v-card
        class="mx-auto rounded-circle"
        max-width="130px"
        style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"
      >
        <v-avatar size="120">
          <v-img src="@/assets/images/pic15.png" alt="Profile Picture"></v-img>
        </v-avatar>
      </v-card>
      <div class="text-center font-weight-bold text-h6 pt-3">Your Pet Saved History</div>
      <v-col cols="12" sm="8" md="4" class="mx-auto pa-10">
        <v-data-iterator :items="petsSaved" :items-per-page="itemsPerPage">
          <!-- Template for the iterator content -->
          <template v-slot:header="{ page, pageCount, prevPage, nextPage }">
            <h1 class="text-h4 font-weight-bold d-flex justify-space-between mb-4 align-center">
              <div class="text-truncate">
                
              </div>
      
              <div class="d-flex align-center">
                <v-btn
                  class="me-8"
                  variant="text"
                  @click="onClickSeeAll"
                >
                  <span class="text-decoration-underline text-none">See all</span>
                </v-btn>
      
                <div class="d-inline-flex">
                  <v-btn
                    :disabled="page === 1"
                    icon="mdi-arrow-left"
                    size="small"
                    variant="tonal"
                    class="me-2"
                    @click="prevPage"
                  ></v-btn>
      
                  <v-btn
                    :disabled="page === pageCount"
                    icon="mdi-arrow-right"
                    size="small"
                    variant="tonal"
                    @click="nextPage"
                  ></v-btn>
                </div>
              </div>
            </h1>
          </template>
          <!-- Adjust the details to match the Pet Saved context -->
          <template v-slot:default="{ items }">
            <v-row>
              <v-col v-for="(item, i) in items" :key="i" cols="12" sm="6" xl="6">
                <v-sheet border rounded>
                  <v-img :src="`${baseUrl}${item.raw.pet_picture}`" cover height="150"></v-img>
  
               <v-list-item :title="item.address" density="comfortable">
  <template v-slot:title>
    <div class="text-caption font-weight-bold text-secondary" style="white-space: normal;">{{ item.raw.address }}</div>
  </template>
</v-list-item>

  
                  <v-table density="compact" class="text-caption">
                    <tbody>
                      <tr align="right">
                        <th>Latitude</th>
                        <td>{{ item.raw.latitude }}</td>
                      </tr>
  
                      <tr align="right">
                        <th>Longitude:</th>
                        <td>{{ item.raw.longitude }}</td>
                      </tr>
  
                      <tr align="right">
                        <th>Date Saved:</th>
                        <td>{{ item.raw.created_at }}</td>
                      </tr>
  
                      <tr align="right">
                        <th>Rescue Status:</th>
                        <td>{{ item.raw.is_rescued ? 'Yes' : 'No' }}</td>
                      </tr>
                    </tbody>
                  </v-table>
                </v-sheet>
              </v-col>
            </v-row>
          </template>
        </v-data-iterator>
      </v-col>
    </v-card>
  </template>
  
  <script setup>
  
  import { ref, onMounted, shallowRef } from 'vue';
  import { useRouter } from 'vue-router';
  import axios from 'axios';
  import { baseUrl } from '@/config/config.js'; // Ensure you have the correct path
 import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary

 const itemsPerPage = shallowRef(1);
const seeAll=ref(false);

function onClickSeeAll() {
  seeAll.value = !seeAll.value; // Toggle seeAll value

  if (seeAll.value === true) {
    itemsPerPage.value = petsAdopted.value.length; // Show all items
  } else {
    itemsPerPage.value = 1; // Show limited items per page
  }
}

  const router = useRouter();
  const petsSaved = ref([]);
const userStore = useUserStore();
  const fetchPetSavedHistory = async () => {
    try {
        const response = await axios.get('user/petSavedHistory', {
      headers: { Authorization: `Bearer ${userStore.token}` }
    }); // Replace with your API endpoint
      petsSaved.value = response.data;
    } catch (error) {
      console.error('Failed to fetch pet saved history:', error);
      // Handle error appropriately
    }
  };
  
  onMounted(fetchPetSavedHistory);
  </script>
  