<template>
    <v-card class="ma-3 elevation-5" rounded >
      <v-card-title class="d-flex align-center pe-2 py-">
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          label="Search Transactions"
          single-line
          flat
          hide-details
          variant="outlined"
        ></v-text-field>
      </v-card-title>
      <v-divider></v-divider>
      <v-data-table
      v-model:search="search"
      :headers="headers"
      :items="petLocations"
      :search="search"
      class="elevation-1 pa-4"
      height="30rem"
    >
    <template v-slot:item.pet_picture="{ item }">
        <div class="my-2 ">
            <v-img   :width="100"
              :src="item.pet_picture ? `${baseUrl}${item.pet_picture}` : 'https://via.placeholder.com/64'"
        class="rounded"
            ></v-img>
        </div>
        </template>
      
      <template v-slot:item.is_rescued="{ item }">
        <v-checkbox
        v-model="item.is_rescued"
        readonly
        color="success"
      ></v-checkbox>
      </template>
      
      
    
      <template v-slot:item.actions="{ item }">
        <div v-if="item.is_rescued === false">
          <!-- Action to mark as rescued -->
          <v-icon
            class="me-2 text-success"
            @click="showMarkAsRescuedDialog(item.location_id)"
          >
            mdi-check
          </v-icon>
        </div>
      </template>
      
      
      
    </v-data-table>
    

    

    <ConfirmationDialog ref="confirmationDialog" :title="dialogTitle" :message="dialogMessage" :color="color" @confirm="markPetAsRescued " @cancel="handleCancel" />

    <DynamicSnackbar ref="snackbar" />
    </v-card>


  </template>
  
  <script setup>
  import { ref, onMounted, watch, onUnmounted } from 'vue'
  import axios from 'axios'
  import { useUserStore } from '@/stores/userStore'
  import { baseUrl, useImageUrl } from '@/config/config.js';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import ConfirmationDialog from '@/components/dialogs/confirmationDialog.vue';

   const form = ref(null);
   const snackbar = ref(null);
   const loading = ref(false);
   const confirmationDialog = ref(null);



   const dialog = ref(false)
  const dialogTitle = ref('');
const dialogMessage = ref('');
const color = ref('');
const currentTransaction = ref(null);
const currentAction = ref('');


  const userStore = useUserStore()
  const search = ref('')
  const headers = [
  {
    title: 'Pet Photo',
    value: 'pet_picture',
    sortable: false,
  },
  {
    title: 'Address Seen',
    value: 'address',
    sortable: true,
  },

  {
    title: 'Date',
    value: 'created_at',
    sortable: true,
  },
  {
    title: 'Is Rescued',
    value: 'is_rescued',
    sortable: true,
  },
  {
    title: 'Actions',
    value: 'actions',
    sortable: true,
  },
];

const petLocations = ref([]);


  





  
const getPetLocations = async () => {
  try {
    const response = await axios.get('/admin/petLocations');
    if (response.data && Array.isArray(response.data)) {
      petLocations.value = response.data.map(location => ({
        ...location,
        // Explicitly convert is_rescued from "1" or "0" to boolean true/false
        is_rescued: location.is_rescued === "1" ? true : false
      }));
    } else {
      console.error('Pet locations data is missing or not in the expected format:', response.data);
      petLocations.value = [];
    }
  } catch (error) {
    console.error('Failed to fetch pet locations:', error);
    petLocations.value = [];
  }
};




onMounted(() => {
    getPetLocations(); // Fetch transactions initially

    }); // Simplified

  

    const showMarkAsRescuedDialog = (locationId) => {
  const location = petLocations.value.find(l => l.location_id === locationId);
  if (location) {
    currentTransaction.value = location; // Consider renaming this to currentLocation for clarity
    currentAction.value = 'rescue';
    dialogTitle.value = "Mark as Rescued";
    dialogMessage.value = `Are you sure you want to mark the pet as rescued?\n Location: ${location.address}`;
    color.value = 'success';
    confirmationDialog.value.openDialog();
  }
};






const markPetAsRescued = async (locationId) => {
  try {
    // Prepare the request payload, changing it to fit the pet location context:
    const payload = {
      location_id: currentTransaction.value.location_id,
      is_rescued: true, // Assuming your backend expects a boolean for the 'is_rescued' field
    };

    // Send the request to the backend endpoint responsible for updating the pet's rescue status
    const response = await axios.post(`/admin/markPetAsRescued`, payload);

    if (response.status === 200) {
      // Show a success message indicating the pet has been marked as rescued
      snackbar.value?.openSnackbar('Pet marked as rescued successfully', 'success');
      
      // Refresh the list of pet locations to reflect the changes
      await getPetLocations();
    } 
  } catch (error) {
    // Handle errors, such as showing an error message
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    snackbar.value?.openSnackbar(errorMessage, 'error');
  }
};





  </script>
  