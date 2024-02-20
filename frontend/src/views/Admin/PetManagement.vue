<template>
    <v-card class="ma-3 elevation-5" rounded >
      <v-card-title class="d-flex align-center pe-2 py-">
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          label="Find Pets"
          single-line
          flat
          hide-details
          variant="outlined"
        ></v-text-field>
        <v-btn variant="flat" class="ml-4" color="secondary"   @click="openAddPetDialog">Add New Pet</v-btn>
      </v-card-title>
      <v-divider></v-divider>
      <v-data-table
        v-model:search="search"
        :headers="headers"
        :items="pets"
        :search="search"
        class="elevation-1 pa-4"
      >
      <template v-slot:item.photo="{ item }">
      <div class="my-2 ">
          <v-img   :width="100"
            :src="item.photo ? `${baseUrl}/pets/${item.photo}` : 'https://via.placeholder.com/64'"
      class="rounded"
          ></v-img>
      </div>
      </template>

      <template v-slot:item.status="{ item }">
        <v-chip label color="success">
          {{ item.status }}
        </v-chip>
      </template>
      
      <template v-slot:item.actions="{ item }">
        <v-icon
          size="small"
          class="me-2"
          @click="editPet(item)"
        >
          mdi-pencil 
        </v-icon>
        
        <v-icon
        size="small"
        @click="showArchiveDialog(item.pet_id)"
      >
        mdi-delete
      </v-icon>
      </template>
      
      </v-data-table>

      <v-dialog
  transition="dialog-top-transition"
  width="auto"
  v-model="dialog"
  persistent
  scrollable
>
  <template v-slot:default="{ isActive }">
    <v-card width="900px">
      <v-toolbar color="secondary" >
        <v-toolbar-title>{{ dialogTitle }}</v-toolbar-title>
        <v-spacer></v-spacer>        <v-btn variant="plain" @click="dialog = false" icon="mdi-close"></v-btn>

      </v-toolbar>
      <div class="text-center mt-5">
        <v-img
        :width="150"
          aspect-ratio="1/1"
          src="@/assets/images/pic11.png" 
          class="mx-auto"
        ></v-img>
      </div>
      <v-card-text>
        <v-row align="center" justify="center" >
          <v-col cols="8">
            <v-form validate-on="blur" ref="form" @submit.prevent="addOrUpdatePet"> <!-- Change to addPet -->
              <v-row>
                <v-file-input
                v-model="petImg"
                label="Pet Picture"
                variant="solo-filled"
                prepend-inner-icon="mdi-camera"
                prepend-icon=""
                class="mt-4"
                center-affix
                show-size
                accept="image/*"
              ></v-file-input>
                <v-col cols="12">
                  <v-text-field
                    v-model="petName"
                    label="Pet Name"
                    variant="underlined"
                    :rules="nameRule"
                  ></v-text-field>
                </v-col>
              </v-row>
              
              <v-row>
                
                <v-col cols="6">
                  <v-text-field
                    v-model="petAge"
                    label="Age"
                    variant="underlined"
                    :rules="petAgeRule"

                  ></v-text-field>
                </v-col>
                <v-col cols="6">
                  <v-autocomplete
                    v-model="petGender"
                    label="Gender"
                    :items="['Male', 'Female']"
                    variant="underlined"
                    :rules="[v => !!v || 'Gender is required']"

                  ></v-autocomplete>
                </v-col>
              </v-row>
              
              <v-row>
                <v-col cols="6">
                  <v-text-field
                    v-model="petSpecies"
                    label="Species" 
                    variant="underlined"
                    :rules="[v => !!v || 'Species is required']"
                  ></v-text-field>
                </v-col>
                <v-col cols="6">
                  <v-text-field
                    v-model="petBreed"
                    label="Breed"
                    variant="underlined"
                  ></v-text-field>
                </v-col>
              </v-row>
              
              <v-textarea 
                v-model="petInfo"
                label="Info"
                variant="underlined"
                :rules="[v => !!v || 'Info is required']"
                multiline
                rows="3"
              ></v-textarea>

              <v-btn
                block
                class="mb-8 mt-2"
                color="secondary"
                size="large"
                @click="addOrUpdatePet" 
                :loading="loading"
              >
                Add
              </v-btn>
            </v-form>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="dialog = false">Close</v-btn>
      </v-card-actions>
    </v-card>
  </template>
</v-dialog>

<ConfirmationDialog ref="confirmationDialog" :title="dialog2Title" :message="dialogMessage" :color="color" @confirm="handleConfirm" @cancel="handleCancel" />
    <DynamicSnackbar ref="snackbar" />
    </v-card>


  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue'
  import axios from 'axios'
  import { useUserStore } from '@/stores/userStore'
  import { baseUrl } from '@/config/config.js';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import ConfirmationDialog from '@/components/dialogs/confirmationDialog.vue';

  import {  
  nameRule, profileRule, petAgeRule
   } from '@/composables/validationRules'; // Directly import the rule
   
   const form = ref(null);
   const snackbar = ref(null);
   const loading = ref(false);

const petName = ref('');
const petAge = ref('');
const petGender = ref(null);
const petSpecies = ref(null);
const petBreed = ref('');
const petInfo = ref('');
const petImg = ref(null);
const petId = ref('');

const isEditMode = ref(false);


const dialogTitle = ref('Add New Pet');
  const dialog = ref(false)
  const userStore = useUserStore()
  const search = ref('')
  const headers = [
    { title: 'PID', value: 'pet_id', sortable: true, },

  { title: 'Photo', value: 'photo', sortable: false, },
  { title: 'Name', value: 'name', sortable: true, },
  { title: 'Age', value: 'age', sortable: true },
  { title: 'Species', value: 'species', sortable: true },
  { title: 'Breed', value: 'breed', sortable: true },
  { title: 'Gender', value: 'gender', sortable: true },
  { title: 'Status', value: 'status', sortable: true },
  { title: 'Action', value: 'actions', sortable: false },
];

  const pets = ref([])


  const confirmationDialog = ref(null); // Define a ref for the confirmation dialog
    const currentPet = ref(null); // Define a ref to store the current pet to be archived
    const dialog2Title = ref('Confirm Archive');
    const dialogMessage = ref('Are you sure you want to archive this pet?');
    const color = ref('warning');

    const showArchiveDialog = (petId) => {
  // Find the pet with the provided petId
  const pet = pets.value.find(p => p.pet_id === petId); // Ensure pet_id matches the property name
  if (pet) {
    currentPet.value = pet;
    dialogMessage.value = `Are you sure you want to archive ${pet.name}?`; // Store the current pet to be archived
    confirmationDialog.value.openDialog(); 
    console,console.log(petId);// Open the confirmation dialog
  }
};


const handleConfirm = async () => {
  if (currentPet.value && currentPet.value.pet_id) {
    // Perform archiving action here using currentPet.value.petId
    await archivePet(currentPet.value.pet_id);
    confirmationDialog.value.closeDialog(); // Close the confirmation dialog
    currentPet.value = null; // Reset currentPet after archiving
  } else {
    console.error('Pet ID is missing');
  }
};


    const handleCancel = () => {
      // Reset currentPet if the user cancels
      currentPet.value = null;
    };


  function editPet(pet) {
  // Populate form fields with selected pet's details
  petName.value = pet.name;
  petAge.value = pet.age;
  petSpecies.value = pet.species;
  petBreed.value = pet.breed;
  petGender.value = pet.gender;
  petInfo.value = pet.info;
  petId.value = pet.pet_id;
  // Assuming petImg is handled separately, as files cannot be directly assigned

  isEditMode.value = true;
  dialogTitle.value = 'Edit Pet Details';
  dialog.value = true; // Open the dialog
}

function openAddPetDialog() {
    resetFormFields(); // Clear any existing form data
  dialog.value = true; // Open the dialog
}


const addOrUpdatePet = async () => {
  if (await form.value.validate()) {
    loading.value = true;

    const formData = new FormData();
    formData.append('name', petName.value);
    formData.append('age', petAge.value);
    formData.append('species', petSpecies.value);
    formData.append('breed', petBreed.value);
    formData.append('gender', petGender.value);
    formData.append('info', petInfo.value);
    if (petImg.value) {
      formData.append('photo', petImg.value[0]); // Append the file to FormData
    }

    // Include petId if editing
    if (petId.value) {
      formData.append('pet_id', petId.value);
    }

    const config = {
      headers: { 'Content-Type': 'multipart/form-data' },
    };

    try {
      let response;
      // Determine if adding a new pet or updating an existing one
      if (petId.value) {
        response = await axios.post(`/admin/updatePet`, formData, config);
      } else {
        response = await axios.post('/admin/addPet', formData, config);
      }

      if (response.status === 201 || response.status === 200) {
        const successMessage = petId.value ? 'Pet updated successfully' : 'Pet added successfully';
        snackbar.value?.openSnackbar(successMessage, 'success');
        await getPets();
        dialog.value = false;
        resetFormFields();
      }
    } catch (error) {
      let errorMessage = 'An unknown error occurred. Please try again.';
      if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      }
      snackbar.value?.openSnackbar(errorMessage, 'error');
    } finally {
      loading.value = false;
    }
  }
};



  
  const getPets = async () => {
    try {
      const response = await axios.get('/admin/pets');
  
      // Adjust this path if your pet data is nested within the response object
      pets.value = response.data.map((pet) => ({
        // Spread the rest of the pet's attributes
        ...pet,
      }));
    } catch (error) {
      console.error('Failed to fetch pets:', error);
      // Optionally, handle error states (e.g., updating UI or showing an error message)
    }
};

  const getColor = (status) => {
  if (status === 'active') {
    return 'green';
  } else if (status === 'inactive') {
    return 'amber';
  } else if (status === 'suspended') {
    return 'red';
  } else {
    return 'grey'; // A default color if the status is something else
  }
};
  onMounted(getPets); // Simplified

  



  const archivePet = async (petId) => {
  try {
    const response = await axios.post(`/admin/archivePet`, { pet_id: petId });
    if (response.status === 200) {
      snackbar.value?.openSnackbar('Pet archived successfully', 'success');
      await getPets(); // Refresh the list of pets to reflect the change
    } else {
      throw new Error('Failed to archive pet');
    }
  } catch (error) {
    console.error('Error archiving pet:', error);
    snackbar.value?.openSnackbar('Failed to archive pet. Please try again.', 'error');
  }
};


 // Function to reset form fields
function resetFormFields() {
  petName.value = '';
  petAge.value = '';
  petSpecies.value = null;
  petBreed.value = '';
  petGender.value = null;
  petInfo.value = '';
  petImg.value = null;
  petId.value = ''; 
  isEditMode.value = false;
  dialogTitle.value = 'Add New Pet'; // Assuming this resets the file input, might need additional handling based on your UI
} 
  </script>
  