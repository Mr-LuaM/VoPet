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
                  hint="e.g., 1y 2m for 1 year and 2 months"
                  persistent-hint
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
                  <v-combobox
        v-model="petSpecies"
        :items="['Cat', 'Dog']"
        label="Species"
        variant="underlined"
        :rules="[v => !!v || 'Species is required']"
        hint="Select from the list or type if not found"
        persistent-hint

      ></v-combobox>
                </v-col>
                <v-col cols="6">
                  <v-combobox
        v-model="petBreed"
        :items="breedOptions"
        label="Breed"
        variant="underlined"
        :rules="[v => !!v || 'Breed is required']"
        hint="Select from the list or type if not found"
        persistent-hint
        :disabled="!petSpecies || petSpecies === 'Other'"
      ></v-combobox>
                </v-col>
              </v-row>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="petColor" 
                    label="Color"
                    variant="underlined"
                    :rules="[v => !!v || 'Color is required']"
                  ></v-text-field>
                </v-col>
              </v-row>
              <v-row> <v-col cols="12">
              <v-textarea
              v-model="petDistinguishingMarks"
              label="Distinguishing Marks"
              variant="underlined"
              :rules="[v => !!v || 'Distinguishing marks are required']"
              multi-line
              rows="3"
            ></v-textarea>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
 
   <v-autocomplete
   v-model="selectedClinic"
   :items="clinics"
   item-title="name"
   item-value="name"
   label="Select Clinic"
   return-object
   :disabled="clinics.length === 0 || loading"
   variant="underlined"
   hint="Null if global pet"
   persistent-hint
 ></v-autocomplete>
 
 
</v-col>
</v-row>

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
  import { ref, watch, onMounted } from 'vue';
  import axios from 'axios';
  import { useUserStore } from '@/stores/userStore';
  import {  
  nameRule, profileRule, petAgeRule
   } from '@/composables/validationRules'; // Directly import the rule
   
  import { baseUrl } from '@/config/config.js';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import ConfirmationDialog from '@/components/dialogs/confirmationDialog.vue';
  
  const userStore = useUserStore();
  const dialog = ref(false);
  const loading = ref(false);
  const form = ref(null);
  
  
  // Pet attributes
  const petName = ref('');
  const petAge = ref('');
  const petGender = ref(null);
  const petSpecies = ref(null);
  const petBreed = ref('');
  const petColor = ref(''); // Correctly added color field
  const petDistinguishingMarks = ref(''); // Updated field
  const petImg = ref(null);
  const petId = ref('');
  
  // Dynamic breed options
  const breedOptions = ref([]);
  const dogBreeds = ['Labrador Retriever', 'German Shepherd', 'Golden Retriever'];
  const catBreeds = ['Persian', 'Maine Coon', 'Siamese'];
  
  // UI and Dialogs
  const dialogTitle = ref('Add New Pet');
  const snackbar = ref(null); // For displaying messages
  const confirmationDialog = ref(null); // For confirming actions like archive
  const currentPet = ref(null); // For operations like archiving
  const isEditMode = ref(false);
  const clinics = ref([]);


const selectedClinic = ref('');

async function fetchClinics() {
  loading.value = true;
  try {
    const response = await axios.get('/form/clinics');
    // Assuming the response.data.data directly contains the array of clinics
    clinics.value = response.data.data; // Update clinics with actual data
  } catch (error) {
    console.error('Error fetching clinics:', error);
    clinics.value = []; // Keep or reset to empty array on error
  } finally {
    loading.value = false;
  }
}




 
  // Data and Methods for Handling Pets
  const pets = ref([]);
  const search = ref('')
  const headers = [
  { title: 'PID', value: 'pet_id', sortable: true },
  { title: 'Photo', value: 'photo', sortable: false },
  { title: 'Name', value: 'name', sortable: true },
  { title: 'Age', value: 'age', sortable: true },
  { title: 'Species', value: 'species', sortable: true },
  { title: 'Breed', value: 'breed', sortable: true },
  { title: 'Color', value: 'color', sortable: true }, // Added color
  { title: 'Distinguishing Marks', value: 'distinguishing_marks', sortable: false }, // Added distinguishing_marks
  { title: 'Gender', value: 'gender', sortable: true },
  { title: 'Status', value: 'status', sortable: true },
  { title: 'Clinic Name', value: 'clinic_name', sortable: true },
  { title: 'Action', value: 'actions', sortable: false },
];

  // Initialization and Utility Methods
  onMounted(() => {
  getPets();
  fetchClinics();
});
  
  watch(petSpecies, (newSpecies) => {
    if (newSpecies === 'Dog') {
      breedOptions.value = dogBreeds;
    } else if (newSpecies === 'Cat') {
      breedOptions.value = catBreeds;
    } else {
      breedOptions.value = [];
    }
    petBreed.value = null; // Reset breed when species changes
  });
  


    const dialog2Title = ref('Confirm Archive');
    const dialogMessage = ref('Are you sure you want to archive this pet?');
    const color = ref('warning');

    function editPet(pet) {
    // Populate form fields with selected pet's details
    petName.value = pet.name;
    petAge.value = pet.age; // Consider the format of age (e.g., "1y 3m")
    petSpecies.value = pet.species;
    petBreed.value = pet.breed;
    petColor.value = pet.color; // Assuming you have a default or empty state for color
    petGender.value = pet.gender;
    petDistinguishingMarks.value = pet.distinguishing_marks || ''; // Use distinguishing marks instead of info
    petId.value = pet.pet_id;

    // Handle the pet image separately if necessary
    // petImg.value should be managed according to how you handle file inputs in Vue
    // For example, you might want to show the current image and replace it if a new one is uploaded

    isEditMode.value = true;
    dialogTitle.value = 'Edit Pet Details';
    dialog.value = true; // Open the dialog
}



function openAddPetDialog() {
    resetFormFields();
    dialog.value = true; // Open the dialog for a new pet
    dialogTitle.value = 'Add New Pet';
    isEditMode.value = false;
}



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

  // Pet Operations: Add, Update, Fetch, and Archive
  async function addOrUpdatePet() {
    const { valid } = await form.value.validate();
  if (valid) {
    loading.value = true;
   
  
    const formData = new FormData();
    formData.append('name', petName.value);
    formData.append('age', petAge.value);
    formData.append('species', petSpecies.value);
    formData.append('breed', petBreed.value);
    formData.append('color', petColor.value);
    formData.append('gender', petGender.value);

  const clinicId = parseInt(selectedClinic.value.id, 10); // Convert to integer
  formData.append('clinic_id', clinicId);

    formData.append('distinguishing_marks', petDistinguishingMarks.value);
    if (petImg.value) {
      formData.append('photo', petImg.value[0]);
    }
    if (petId.value) {
      formData.append('pet_id', petId.value);
    }
  
    try {
      const response = petId.value ?
        await axios.post(`/admin/updatePet`, formData) :
        await axios.post(`/admin/addPet`, formData);
  
      const successMessage = petId.value ? 'Pet updated successfully' : 'Pet added successfully';
       snackbar.value?.openSnackbar(successMessage, 'success');
      dialog.value = false;
      resetFormFields();
      getPets();
    } catch (error) {
      const errorMessage = error.response?.data?.message || 'An unknown error occurred. Please try again.';
       snackbar.value?.openSnackbar(errorMessage, 'error');
    } finally {
      loading.value = false;
    }
  }
  }
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
  async function getPets() {
    try {
      const response = await axios.get(`/admin/pets`);
      pets.value = response.data;
    } catch (error) {
      console.error('Failed to fetch pets:', error);
       snackbar.value?.openSnackbar('Failed to load pets.', 'error');
    }
  }
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
function resetFormFields() {
  petName.value = '';
  petAge.value = '';
  petSpecies.value = null;
  petBreed.value = '';
  petGender.value = null;
  petDistinguishingMarks.value = ''; // Clear distinguishing marks
  petColor.value = ''; // Clear color
  petImg.value = null; // Handle as needed for your UI
  petId.value = ''; // Clear pet ID
  selectedClinic.value = ''; // Clear clinic name
}

  
  // Methods related to edit and archive operations here
  </script>
  