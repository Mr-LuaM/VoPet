<template>
  <v-card class="fill-height" flat>
    <!-- Header Toolbar -->
    <v-toolbar class="bg-primary">
      <v-toolbar-title class="font-weight-black text-h6">
        <!-- Use router-link to navigate back -->
        <router-link :to="{ name: 'userDashboard' }">
          <v-icon class="mt-n1" color="white">mdi-chevron-left</v-icon>
        </router-link>
        Discover
      </v-toolbar-title>
      <v-spacer></v-spacer>
    </v-toolbar>

    <!-- Filter Section -->
    <v-col cols="12" sm="8" md="4" class="mx-auto pb-0 mb-0 mt-9">
      <v-card-text>
        <h2 class="text-h6 mb-2 mt-n9">
          <v-icon size="small">mdi-filter</v-icon>Filter
        </h2>
        <v-row>
          <v-col cols="5">
            <p>Species</p>
            <v-chip-group v-model="species" column selected-class="text-secondary" multiple>
              <v-chip filter value="dog">Dog</v-chip>
              <v-chip filter value="cat">Cat</v-chip>
            </v-chip-group>
          </v-col>
          <v-col cols="6">
            <p>Gender</p>
            <v-chip-group v-model="gender" column selected-class="text-secondary" multiple>
              <v-chip filter value="male">Male</v-chip>
              <v-chip filter value="female">Female</v-chip>
            </v-chip-group>
          </v-col>
        </v-row>
        <p>Age</p>
        <v-chip-group v-model="age" column selected-class="text-secondary" multiple>
          <v-chip filter value="young">Young</v-chip>
          <v-chip filter value="adult">Adult</v-chip>
        </v-chip-group>
      </v-card-text>

      <!-- Carousel Section -->
      <v-card elevation="24" max-width="444" class="mx-auto">
        <!-- Display a loading indicator while loading -->
        <v-overlay :value="isLoading" color="white">
          <v-progress-circular indeterminate size="64"></v-progress-circular>
        </v-overlay>

        <!-- Carousel is displayed when not loading -->
        <v-carousel v-if="!isLoading" flat cycle hide-delimiters touch show-arrows="hover" progress="secondary" height="600"  v-model="petIndex">
          <template v-if="filteredRecentPets.length > 0">
            <v-carousel-item v-for="(pet, i) in filteredRecentPets" :key="i" :value="pet.pet_id">
              <!-- Display pet details in a v-card -->
              <v-img :src="pet.imageUrl" cover height="350"></v-img>
              <v-card-text>
                <!-- Display pet details here -->
                <div class="text-caption font-weight-medium text-grey">Pet ID: {{ pet.pet_id }}</div>
                <div class="text-caption font-weight-medium text-grey">Name: <span class="text-primary">{{ pet.name }}</span></div>
                <div class="text-caption font-weight-medium text-grey">Gender: {{ pet.gender }}</div>

                <div class="text-caption font-weight-medium text-grey">Age: {{ pet.age }}</div>
                <div class="text-caption font-weight-medium text-grey">Species: {{ pet.species }}</div>
                <div class="text-caption font-weight-medium text-grey">Breed: {{ pet.breed }}</div>
                <div class="text-caption font-weight-medium text-grey">Color: {{ pet.color }}</div>

                <div class="text-caption font-weight-medium text-grey">Status: {{ pet.status }}</div>
                <div class="text-caption font-weight-medium text-grey">Info: {{ pet.distinguishing_marks }}</div>
                <div class="text-caption font-weight-medium text-grey">Rescued At: {{ pet.created_at }}</div>
              </v-card-text>
            </v-carousel-item>
          </template>
          <template v-else>
            <v-carousel-item>
              <div class="text-center mt-8">No pets found with the selected categories.</div>
            </v-carousel-item>
          </template>
        </v-carousel>
      </v-card>

      <!-- Button to Change Profile Picture -->
      <v-btn block class="mb-8 mt-2 rounded-pill" color="secondary" size="large" @click="adoptPet" :loading="loading">
       Adopt Pet
      </v-btn>
    </v-col>
    <DynamicSnackbar ref="snackbar" />
  </v-card>

</template>

<script setup>
import { useUserStore } from '@/stores/userStore'; // Adjust the path to your store as needed
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Import useRouter
import { baseUrl } from '@/config/config.js';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';


const userStore = useUserStore();
const router = useRouter(); // Access the router instance

const recentPets = ref([]); // Define the recentPets ref
const isLoading = ref(true); // Define a loading state
const loading = ref(false); // Define a loading state
const isloading = ref(true); // Define a loading state

const snackbar = ref(null);


const species = ref([]);
const gender = ref([]);
const age = ref([]);

const fetchRecentPets = async () => {
  try {
    const response = await axios.get('user/recent-pets'); // Replace with your API endpoint
    recentPets.value = response.data.map((pet) => ({
      imageUrl: pet.photo ? `${baseUrl}${'pets/'}${pet.photo}` : `${baseUrl}default.jpg`, // Use pet.photo if available, else use a default image
      ...pet, // Include all pet details in the pet object
    }));
  } catch (error) {
    console.error('Failed to fetch recent pets:', error);
  } finally {
    isLoading.value = false; // Set loading state to false when loading is complete
  }
};

onMounted(() => {
  fetchRecentPets();
});
const petIndex = ref();
// Computed property to filter recent pets based on selected filters
const filteredRecentPets = computed(() => {
  return recentPets.value.filter(pet => {
    // Check if pet meets all selected filter criteria
    return (
      (!species.value.length || species.value.includes(pet.species.toLowerCase())) &&
      (!gender.value.length || gender.value.includes(pet.gender.toLowerCase())) &&
      (!age.value.length || ageMatches(pet.age))
    );
  });
});

// Function to check if the pet's age matches the selected age categories
function ageMatches(petAge) {
  // If no age categories are selected, all pets are considered to match
  if (!age.value.length) return true;

  // Check if pet's age matches the selected age categories
  for (const category of age.value) {
    if (category === 'young' && petAge < 10) {
      return true; // Pet is young
    } else if (category === 'adult' && petAge >= 10) {
      return true; // Pet is adult
    }
  }
  return false; // Pet does not match any selected age category
}
const adoptPet = async () => {
  if (filteredRecentPets.value.length && petIndex.value !== undefined) {
    try {
      loading.value = true;
      isloading.value = true;
      const response = await axios.post('user/adopt', { pet_id: petIndex.value }, {
        headers: { Authorization: `Bearer ${userStore.token}` },
      });
      if (response.status === 200) {
        const successMessage = response.data.message || 'Pet adoption requested successfully';
        if (snackbar.value) {
          snackbar.value.openSnackbar(successMessage, 'success');
        }
        fetchRecentPets();
        petIndex.value=(null);
      } else {
        throw new Error('Failed to adopt pet');
      }
    } catch (error) {
      let errorMessage = 'An unknown error occurred. Please try again.';
      if (error.response && error.response.data && error.response.data.messages) {
        errorMessage = Object.values(error.response.data.messages).join(' ');
      }
      if (snackbar.value) {
        snackbar.value.openSnackbar(errorMessage, 'error');
      }
    } finally {
      loading.value = false;
      isloading.value = false;
    }
  }
};
</script>
