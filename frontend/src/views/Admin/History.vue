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
      </v-card-title>
      <v-divider></v-divider>
      <v-data-table
      v-model:search="search"
      :headers="headers"
      :items="transactions"
      :search="search"
      class="elevation-1 pa-4"
    >
      <template v-slot:item.photoUrl="{ item }">
        <div class="my-2">
          <v-img :src="item.photoUrl" :width="100" class="rounded"></v-img>
        </div>
      </template>
    
      <template v-slot:item.userPictureUrl="{ item }">

            <v-avatar size="32">
              <img :src="item.userPictureUrl" alt="User Avatar"          height="32"
              width="32">
            </v-avatar>
          
      </template>
      <template v-slot:item.status="{ item }">
        <v-chip :color="getStatusColor(item.status)" label>
          {{ item.status }}
        </v-chip>
      </template>
      
      
    
      <template v-slot:item.actions="{ item }">
        <!-- Only show actions if status is 'approved' -->
        <div v-if="item.status === 'approved'">
          <!-- Action to mark as completed -->
          <v-icon
            size="small"
            class="me-2 text-success"
            @click="showMarkAsCompletedDialog(item.transaction_id)"
          >
            mdi-check-all
          </v-icon>
      
          <!-- Action to mark as unclaimed -->
          <v-icon
            size="small"
            class="text-warning"
            @click="showMarkAsUnclaimedDialog(item.transaction_id)"
          >
            mdi-alert
          </v-icon>
        </div>
      </template>
      
      
    </v-data-table>
    

    

    <ConfirmationDialog ref="confirmationDialog" :title="dialogTitle" :message="dialogMessage" :color="color" @confirm="handleConfirm" @cancel="handleCancel" />

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
    // Actions might not necessarily belong to a group, or they could form their own group.
    title: 'TID', value: 'transaction_id', sortable: true
  },
  {
    
    title: 'Pet Details',
    align: 'center',
    children: [
      { title: 'Pet Photo', value: 'photoUrl', sortable: false },
      { title: 'Pet Name', value: 'pet_name', sortable: true },
      { title: 'Age', value: 'age', sortable: true },
      { title: 'Species', value: 'species', sortable: true },
      { title: 'Breed', value: 'breed', sortable: true },
      { title: 'Gender', value: 'gender', sortable: true },    ]
  },
  {
    title: 'User Details',
    align: 'center',
    children: [
        { title: 'User', value: 'userPictureUrl', sortable: false },

      { title: 'User Name', value: 'userName', sortable: true },
      { title: 'User Email', value: 'email', sortable: true, },
      { title: 'Contact Number', value: 'contact_number', sortable: true },
    ]
  },
  {
    // As "Transaction Status" and "Actions" don't fall under "Pet Details" or "User Details", 
    // they are not nested within a group. Adjust according to your design if needed.
    title: 'Transaction Status', value: 'status', sortable: true
  },
  {
    // Actions might not necessarily belong to a group, or they could form their own group.
    title: 'Actions', value: 'actions', sortable: false
  }
];

const transactions = ref([]);


  





  
const getTransactions = async () => {
  try {
    const response = await axios.get('/admin/transactionsHistory');
    // Check if response.data.data exists and is an array before mapping
    if (response.data && Array.isArray(response.data.data)) {
      transactions.value = response.data.data.map((transaction) => {
        const userPictureUrl = transaction.picture_url ? `${baseUrl}${transaction.picture_url}` : 'https://via.placeholder.com/64';
        const photoUrl = transaction.photo ? `${baseUrl}pets/${transaction.photo}` : 'https://via.placeholder.com/64';
        
        return {
          ...transaction,
          photoUrl: photoUrl,
          userPictureUrl: userPictureUrl,
          userName: `${transaction.fname} ${transaction.lname}`,
        };
      });
    } else {
      // Handle case where data is missing or not in expected format
      console.error('Transactions data is missing or not in the expected format:', response.data);
      transactions.value = []; // Set to empty array as a fallback
    }
  } catch (error) {
    console.error('Failed to fetch transactions:', error);
    transactions.value = []; // Set to empty array in case of error
  }
};


onMounted(() => {
      getTransactions(); // Fetch transactions initially

      const intervalId = setInterval(() => {
        getTransactions(); // Fetch transactions at every interval
      }, 5000); // Refresh every 5000 milliseconds (5 seconds)

      // Cleanup on component unmount to avoid memory leaks
      onUnmounted(() => {
        clearInterval(intervalId);
      });
    }); // Simplified

  


    const showMarkAsCompletedDialog = (transactionId) => {
  const transaction = transactions.value.find(t => t.transaction_id === transactionId);
  if (transaction) {
    currentTransaction.value = transaction;
    currentAction.value = 'complete';
    dialogTitle.value = "Mark Transaction as Completed";
    dialogMessage.value = `Are you sure you want to mark the transaction for ${transaction.pet_name} as completed?`;
    color.value = 'success';
    confirmationDialog.value.openDialog();
  }
};

const handleConfirm = async () => {
  if (currentTransaction.value && currentTransaction.value.transaction_id) {
    if (currentAction.value === 'complete') {
      await markTransactionAsCompleted(currentTransaction.value.transaction_id);
    } else if (currentAction.value === 'unclaimed') {
      await markTransactionAsUnclaimed(currentTransaction.value.transaction_id);
    }
    confirmationDialog.value.closeDialog();
    currentAction.value = ''; // Reset the action type
  } else {
    console.error('Transaction ID is missing');
  }
};




const markTransactionAsCompleted  = async (transactionId) => {
  try {
    // Prepare the request payload if needed, for example:
    const payload = {
      transaction_id: transactionId,
      status: 'completed', // Assuming 'approved' is the status your backend expects
    };

    // Send the request to the backend endpoint responsible for updating the transaction status
    const response = await axios.post(`/admin/markTransactionAsCompleted`, payload);

    if (response.status === 200) {
      // Show a success message
      snackbar.value?.openSnackbar('Transaction Completed', 'success');
      
      // Refresh the list of transactions to reflect the changes
      await getTransactions();
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


// Function to show dialog for marking a transaction as unclaimed
const showMarkAsUnclaimedDialog = (transactionId) => {
  const transaction = transactions.value.find(t => t.transaction_id === transactionId);
  if (transaction) {
    currentTransaction.value = transaction;
    currentAction.value = 'unclaimed';
    dialogTitle.value = "Mark Transaction as Unclaimed";
    dialogMessage.value = `Are you sure you want to mark the transaction for ${transaction.pet_name} as unclaimed?`;
    color.value = 'warning';
    confirmationDialog.value.openDialog();
  }
};


const markTransactionAsUnclaimed  = async (transactionId) => {
  try {
    const payload = { transaction_id: transactionId, status: 'unclaimed' };
    const response = await axios.post(`/admin/markTransactionAsUnclaimed`, payload);

    if (response.status === 200) {
      snackbar.value?.openSnackbar('Unclaimed', 'error');
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

const getStatusColor = (status) => {
  switch (status) {
    case 'approved':
      return 'green';
    case 'denied':
      return 'red';
    case 'completed':
      return 'blue';
    case 'unclaimed':
      return 'orange';
    default:
      return 'grey';
  }
};

  </script>
  