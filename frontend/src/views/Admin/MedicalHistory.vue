<template>
    <v-card class="ma-3 elevation-5" rounded >
        
      <v-card-title class="d-flex align-center pe-2 py-">
        <v-btn append-icon="mdi-export" variant="outlined" color="success">Export</v-btn>
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
        <v-btn variant="flat" class="ml-4" color="secondary"   @click="openAddMedicalTransaction">New Transaction</v-btn>

      </v-card-title>
      <v-divider></v-divider>
      <v-data-table
      v-model:search="search"
      :headers="headers"
      :items="transactions"
      :search="search"
      class="elevation-1 pa-4"
      height="35rem"
    >
      <template v-slot:item.photoUrl="{ item }">
        <div class="my-2">
          <v-img :src="item.photoUrl" :width="100" class="rounded"></v-img>
        </div>
      </template>
      
      
      <template v-slot:item.pet_name="{ item }">
        <span :class="getPetColorClass(item.pet_id)">{{ item.pet_name }}</span>
      </template>
      <template v-slot:item.pet_id="{ item }">
        <span :class="getPetColorClass(item.pet_id)">{{ item.pet_id }}</span>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon
       
        class="text-error text-center"
        @click="showMarkAsNotCorrectDialog(item.id)"
      >
        mdi-archive
      </v-icon>      </template>
      
      
      
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
      <v-toolbar color="secondary">
        <v-toolbar-title>{{ dialogTitle }}</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn variant="plain" @click="dialog = false" icon="mdi-close"></v-btn>
      </v-toolbar>
      <div class="text-center mt-5">
        <v-img
        :width="150"
          aspect-ratio="1/1"
          src="@/assets/images/pic16.png" 
          class="mx-auto"
        ></v-img>
      </div>
      <v-card-text>
        <v-form ref="form" @submit.prevent="addMedicalTransaction">
          <v-row>

            <v-col cols="12">
              <v-autocomplete
                v-model="selectedPetId"
                :items="petList"
                item-title="pet_name"
                item-value="pet_id"
                label="Select Pet"
                variant="outlined"
                :loading="loading"
              ></v-autocomplete>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="medicalCondition"
                label="Medical Condition"
                variant="underlined"
                :rules="[v => !!v || 'Medical Condition is required']"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="medication"
                label="Medication"
                variant="underlined"
                :rules="[v => !!v || 'Medication is required']"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="dosage"
                label="Dosage"
                variant="underlined"
                :rules="[v => !!v || 'Dosage is required']"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="vaccinationType"
                label="Vaccination Type"
                variant="underlined"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="vaccinationDate"
                label="Vaccination Date"
                variant="underlined"
                type="date"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="nextVaccinationDate"
                label="Next Vaccination Date"
                variant="underlined"
                type="date"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="surgicalProcedure"
                label="Surgical Procedure"
                variant="underlined"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="weight"
                label="Weight"
                variant="underlined"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="temperature"
                label="Temperature"
                variant="underlined"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols="6">
              <v-text-field
                v-model="heartRate"
                label="Heart Rate"
                variant="underlined"
              ></v-text-field>
            </v-col>
            <v-col cols="6">
              <v-text-field
                v-model="dietaryRestrictions"
                label="Dietary Restrictions"
                variant="underlined"
              ></v-text-field>
            </v-col>
          </v-row>
          <v-textarea
            v-model="behavioralNotes"
            label="Behavioral Notes"
            variant="underlined"
            rows="3"
          ></v-textarea>
          <v-btn
            block
            class="mt-8"
            color="secondary"
            @click="addMedicalTransaction"
            :loading="loading"
          >
            Add Medical Transaction
          </v-btn>
        </v-form>
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="dialog = false">Close</v-btn>
      </v-card-actions>
    </v-card>
  </template>
</v-dialog>


    <ConfirmationDialog ref="confirmationDialog" :title="dialogTitle" :message="dialogMessage" :color="color" @confirm="markTransactionAsNotCorrect" @cancel="handleCancel" />

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
  const dialogTitle = ref('Add Medical Transaction');
const dialogMessage = ref('');
const color = ref('');
const currentTransaction = ref(null);
const currentAction = ref('');

const petList = ref([]);

const selectedPetId = ref(null);
const medicalCondition = ref('');
const medication = ref('');
const dosage = ref('');
const vaccinationType = ref('');
const vaccinationDate = ref('');
const nextVaccinationDate = ref('');
const surgicalProcedure = ref('');
const weight = ref('');
const temperature = ref('');
const heartRate = ref('');
const dietaryRestrictions = ref('');
const behavioralNotes = ref('');

  const userStore = useUserStore()
  const search = ref('')
// Define the headers for the data table
const headers = [
  {
    title: 'ID', value: 'id', sortable: true
  },
  {
    title: 'Pet Details',
    align: 'center',
    children: [
        { title: 'PID', value: 'pet_id', sortable: false },

      { title: 'Pet Photo', value: 'photoUrl', sortable: false },
      { title: 'Pet Name', value: 'pet_name', sortable: true },
      { title: 'Age', value: 'age', sortable: true },
      { title: 'Species', value: 'species', sortable: true },
      { title: 'Breed', value: 'breed', sortable: true }
    ]
  },
  {
    title: 'Medical Details',
    align: 'center',
    children: [
      { title: 'Medical Condition', value: 'medical_condition', sortable: true },
      { title: 'Medication', value: 'medication', sortable: true },
      { title: 'Dosage', value: 'dosage', sortable: true },
      { title: 'Vaccination Type', value: 'vaccination_type', sortable: true },
      { title: 'Vaccination Date', value: 'vaccination_date', sortable: true },
      { title: 'Next Vaccination Date', value: 'next_vaccination_date', sortable: true },
      { title: 'Surgical Procedure', value: 'surgical_procedure', sortable: true },
      { title: 'Weight', value: 'weight', sortable: true },
      { title: 'Temperature', value: 'temperature', sortable: true },
      { title: 'Heart Rate', value: 'heart_rate', sortable: true },
      { title: 'Dietary Restrictions', value: 'dietary_restrictions', sortable: true },
      { title: 'Behavioral Notes', value: 'behavioral_notes', sortable: true }
    ]
  },
  {
    title: 'Actions', value: 'actions', sortable: false, align: 'center'
  },
];

const openAddMedicalTransaction = () => {
  // Set the dialog to true to open it
  dialog.value = true;
  // Reset all form fields to their initial values
  resetFormFields();
};

const resetFormFields = () => {
  // Reset all form fields to their initial values
  medicalCondition.value = '';
  medication.value = '';
  dosage.value = '';
  vaccinationType.value = '';
  vaccinationDate.value = null;
  nextVaccinationDate.value = null;
  surgicalProcedure.value = '';
  weight.value = '';
  temperature.value = '';
  heartRate.value = '';
  dietaryRestrictions.value = '';
  behavioralNotes.value = '';
};

// Fetch transactions function
const getTransactions = async () => {
  try {
    const response = await axios.get('/admin/medical-history');
    if (response.data && response.data.status === 'success') {
      transactions.value = response.data.data.map((transaction) => {
        const photoUrl = transaction.photo ? `${baseUrl}pets/${transaction.photo}` : 'https://via.placeholder.com/64';
        return {
          ...transaction,
          photoUrl: photoUrl
        };
      });
    } else {
      console.error('Failed to fetch medical history:', response.data);
      transactions.value = [];
    }
  } catch (error) {
    console.error('Failed to fetch medical history:', error);
    transactions.value = [];
  }
};



const transactions = ref([]);


  
const getPetColorClass = (petId) => {
  // Define a function to generate a color class based on the pet ID
  // You can use any method to generate color classes, such as assigning specific colors to specific IDs or using a hashing algorithm
  // For simplicity, let's use a basic approach where we assign colors based on the last digit of the pet ID
  const colorMap = {
    '0': 'text-primary',
    '1': 'text-secondary',
    '2': 'text-success',
    '3': 'text-danger',
    '4': 'text-warning',
    '5': 'text-info',
    '6': 'text-dark',
    '7': 'text-primary',
    '8': 'text-secondary',
    '9': 'text-success',
  };

  // Get the last digit of the pet ID
  const lastDigit = petId.slice(-1);

  // Use the color map to determine the color class
  return colorMap[lastDigit];
};







onMounted(async () => {
  try {
  loading.value = true;
  const response = await axios.get('form/pets');
  // Transform the data into the expected format
  petList.value = response.data.map(pet => ({
    pet_id: pet.pet_id,
    pet_name: pet.name // Assuming 'name' corresponds to the pet's name
  }));
} catch (error) {
  console.error('Failed to fetch pet list:', error);
} finally {
  loading.value = false;
}
  // Fetch transactions initially
  getTransactions();

  // // Set interval to fetch transactions periodically
  // const intervalId = setInterval(() => {
  //   getTransactions();
  // }, 5000); // Refresh every 5000 milliseconds (5 seconds)

  // // Cleanup on component unmount to avoid memory leaks
  // onUnmounted(() => {
  //   clearInterval(intervalId);
  // });
});










const showMarkAsNotCorrectDialog = (transactionId) => {
  const transaction = transactions.value.find(t => t.id === transactionId);
  if (transaction) {
    currentTransaction.value = transaction;
    currentAction.value = 'notCorrect'; // Updated action type
    dialogTitle.value = "Mark as Not Correct";
    dialogMessage.value = `Are you sure you want to mark the transaction for ${transaction.pet_name} as not correct?`;
    color.value = 'warning';
    confirmationDialog.value.openDialog();
  }
};




const markTransactionAsNotCorrect = async (transactionId) => {
  try {
const payload = { id: currentTransaction.value.id, is_correct: false }; // Adjusted payload
const response = await axios.post(`/admin/markTransactionAsNotCorrect`, payload);


    if (response.status === 200) {
      snackbar.value?.openSnackbar('Marked as Not Correct', 'error');
      await getTransactions();
    }
  } catch (error) {
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    snackbar.value?.openSnackbar(errorMessage, 'error');
  }
};

const addMedicalTransaction = async () => {
  try {
    // Validate the form fields
    if (await form.value.validate()) {
      // Prepare the data object to send to the backend
      const data = {
        pet_id: selectedPetId.value, // Add the pet ID
        medical_condition: medicalCondition.value,
        medication: medication.value,
        dosage: dosage.value,
        vaccination_type: vaccinationType.value,
        vaccination_date: vaccinationDate.value,
        next_vaccination_date: nextVaccinationDate.value,
        surgical_procedure: surgicalProcedure.value,
        weight: weight.value,
        temperature: temperature.value,
        heart_rate: heartRate.value,
        dietary_restrictions: dietaryRestrictions.value,
        behavioral_notes: behavioralNotes.value
      };

      // Send a POST request to your backend CI4 endpoint
      const response = await axios.post('/admin/addMedicalHistory', data);

      // Check if the request was successful (status code 201 or 200)
      if (response.status === 201 || response.status === 200) {
        // Display a success snackbar
        snackbar.value?.openSnackbar('Medical transaction added successfully', 'success');
        // Optionally, perform additional actions like closing the dialog and resetting form fields
        dialog.value = false;
        resetFormFields();
      } else {
        // Display an error snackbar
        snackbar.value?.openSnackbar('Failed to add medical transaction. Please try again.', 'error');
      }
    }
  } catch (error) {
    // Display an error snackbar with the error message
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    snackbar.value?.openSnackbar(errorMessage, 'error');
  } finally {
    loading.value = false; // Ensure loading indicator is turned off even if an error occurs
  }
};


  </script>
  