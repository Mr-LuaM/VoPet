<template>
  <v-card flat >
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
      @click=" router.push({ name: 'userProfile' });"
    >
  
      <v-icon>mdi-arrow-left</v-icon>
  </v-btn>
      <v-toolbar-title class="font-weight-bold mt-3">Adoption History</v-toolbar-title>
      </v-toolbar>
  
      <v-card
  class="mx-auto rounded-circle"
  max-width="130px"
  style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"

>
  <v-avatar size="120"> <!-- You can adjust the size as needed -->
      <v-img
      src="@/assets/images/pic15.png" alt="Profile Picture"
      ></v-img>
  </v-avatar>

 
</v-card>
<div class="text-center font-weight-bold text-h6 pt-3">Your Adoption History</div>
<v-col
cols="12"
sm="8"
md="4"
class="mx-auto pa-10"

>
<v-data-iterator
    :items="petsAdopted"
    :items-per-page="itemsPerPage"
  >
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

    <template v-slot:default="{ items }">
      <v-row>
        <v-col
          v-for="(item, i) in items"
          :key="i"
          cols="12"
          sm="6"
          xl="3"
        >
          <v-sheet border rounded>
            <v-img
              :gradient="`to top right, rgba(255, 255, 255, .1), rgba(${item.raw.color}, .15)`"
              :src="`${baseUrl}${'pets/'}${item.raw.photo}`"
              cover
              height="150"
            ></v-img>

            <v-list-item
              :title="item.raw.name"
              lines="two"
              density="comfortable"
              :subtitle="item.raw.status"
            >
              <template v-slot:title>
                <strong class="text-h6">
                  {{ item.raw.name }}
                </strong>
              </template>
            </v-list-item>

            <v-table density="compact" class="text-caption">
              <tbody>
                <tr align="right">
                  <th>Age</th>

                  <td>{{ item.raw.age }}</td>
                </tr>

                <tr align="right">
                  <th>Species:</th>

                  <td>{{ item.raw.species }}</td>
                </tr>

                <tr align="right">
                  <th>Breed:</th>

                  <td>{{ item.raw.breed }}</td>
                </tr>

                <tr align="right">
                  <th>Date:</th>

                  <td>{{ item.raw.created_at }}</td>
                </tr>

              </tbody>
            </v-table>
          </v-sheet>
        </v-col>
      </v-row>
    </template>

    <template v-slot:footer="{ page, pageCount }">
      <v-footer
        color="surface-variant"
        class="justify-space-between text-body-2 mt-4"
      >
        Total Adopted: {{ petsAdopted.length }}

        <div>
          Page {{ page }} of {{ pageCount }}
        </div>
      </v-footer>
    </template>
  </v-data-iterator>
  </v-col>

</v-card>

</template>
<script setup>
import { ref, onMounted, shallowRef } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
import axios from 'axios';
import { baseUrl } from '@/config/config.js';

const router = useRouter();
const userStore = useUserStore();

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


const petsAdopted = ref([]);
const fetchAdoptionHistory = async () => {
  try {
    const response = await axios.get('user/adoptionHistory', {
      headers: { Authorization: `Bearer ${userStore.token}` }
    }); // Replace with your API endpoint
    petsAdopted.value = response.data;
  } catch (error) {
    console.error('Failed to fetch adoption history:', error);
    // You can display an error message to users here
  } finally {
    // If you have isLoading, set it to false here
  }
};

onMounted(() => {
  fetchAdoptionHistory();
});

</script>
