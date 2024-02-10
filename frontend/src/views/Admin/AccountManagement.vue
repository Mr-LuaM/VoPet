<template>
    <v-card class="ma-3 elevation-5" rounded >
      <v-card-title class="d-flex align-center pe-2 py-">
        <v-btn append-icon="mdi-export" variant="outlined" color="success">Export</v-btn>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          density="compact"
          label="Search User"
          single-line
          flat
          hide-details
          variant="outlined"
        ></v-text-field>
        <v-btn variant="flat" class="ml-4" color="secondary"   @click="dialog = !dialog">Add New User</v-btn>
      </v-card-title>
      <v-divider></v-divider>
      <v-data-table
        v-model:search="search"
        :headers="headers"
        :items="items"
        :search="search"
        class="elevation-1 pa-4"
      >
        <template v-slot:item.picture_url="{ item }">
          <v-avatar size="32" class="my-2">
            <v-img
              :src="item.picture_url ? item.picture_url : 'https://via.placeholder.com/64'"
              height="32"
              width="32"
            ></v-img>
          </v-avatar>
        </template>
        <template v-slot:item.status="{ item }">
            <v-chip label :color="getColor(item.status)">
              {{ item.status }}
            </v-chip>
          </template>

    <!-- Role Column -->
    <template v-slot:item.role="{ item }">
        <div v-if="editItemId === item.user_id" class="pt-7"> <!-- Use user_id or another unique identifier -->
          <v-autocomplete
            v-model="item.role"
            :items="['admin', 'user']"
            variant="outlined"
            density="compact"
        
          ></v-autocomplete>
        </div>
        <div v-else>
          {{ item.role }}
        </div>
      </template>
  
      <!-- Action Column -->
      <template v-slot:item.action="{ item }">
        <v-icon
        size="small"
        class="me-2"
        @click="handleIconClick(item)"
      >
        {{ editItemId === item.user_id ? 'mdi-content-save' : 'mdi-pencil' }}
      </v-icon>
        <v-icon
          size="small"
          @click="removeAccount(item.user_id)"
        >
        mdi-account-remove
        </v-icon>
      </template>
      </v-data-table>

      <v-dialog
      transition="dialog-top-transition"
      width="auto"
      v-model="dialog"
     persistent
      :location="right"
    >
      <template v-slot:default="{ isActive }">
        <v-card  width="900px">
          <v-toolbar
            color="secondary"
            title="Add new User"
          ></v-toolbar>
          <div class="text-center mt-5">
            <v-img
            :width="200"
            aspect-ratio="1/1"
            src="@/assets/images/pic4.png"
            class=" mx-auto"
          ></v-img>
          </div>
          <v-card-text>
            <v-row
            align="center"
            justify="center"
          >
            <v-col
            cols="8"
   
          >
            <v-form validate-on="blur" ref="form" @submit.prevent="addUser" > 
                <v-row>
                  <v-col cols="6" >
                    <v-text-field
                      v-model="fname"
                      label="First Name"
                      variant="underlined"
                      :rules="nameRule" 
                    ></v-text-field>
                  </v-col>
                  <v-col cols="6" >
                    <v-text-field
                      v-model="lname"
                      label="Last Name"
                      variant="underlined"
                      :rules="nameRule" 
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-text-field
  v-model="email"
   label="Email"
   variant="underlined"
   :rules="emailRule"  
 ></v-text-field>
             
  <v-text-field
                v-model="password"
                label="Password"
    :append-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
    :type="visible ? 'text' : 'password'"
    :rules="passwordRule" 
    variant="underlined"
    @click:append="visible = !visible"
    hint= "Password must be at least 8 characters with a combination of 1 uppercase, 1 lowercase, 1 digit, and 1 special character"
    persistent-hint
  ></v-text-field>
  
 <v-text-field
 v-model="confirmPassword"
 label="Confrim Password"
:append-icon="visible2 ? 'mdi-eye-off' : 'mdi-eye'"
:type="visible2 ? 'text' : 'password'"
:rules="confirmPasswordRules" 
variant="underlined"
@click:append="visible2 = !visible2"
></v-text-field>
<v-row>
<v-col cols="6" >
  <v-text-field
  v-model="birthdate"
   label="Birthdate"
   variant="underlined"
   type="date"
   :rules="birthdateRule"  
 ></v-text-field>
</v-col>
<v-col cols="6" >
  <v-autocomplete
    v-model="sex"
    label="Sex"
    :items="['Male', 'Female', 'Prefer not to say']"
    variant="underlined"
    :rules="sexRule"  
  ></v-autocomplete>
</v-col>
</v-row>
<v-row>
<v-col cols="6" >
<v-text-field
  v-model="contactNumber"
   label="Contact Number"
   variant="underlined"
   :rules="contactNumberRule"  
 ></v-text-field>
 </v-col>
 <v-col cols="6" >
 <v-autocomplete
 v-model="role"
 label="Role"
 :items="['admin', 'user']"
 variant="underlined"
 :rules="[v => !!v || 'Role is required']"></v-autocomplete>   
</v-col>  
</v-row>
  <v-btn
  block
  class="mb-8 mt-2"
  color="secondary"
  size="large"
  @click="addUser"
  :loading="loading"
 
>
 Add 
</v-btn>

                </v-form>
                </v-col>
                </v-row>
          </v-card-text>
          <v-card-actions class="justify-end">
            <v-btn
              variant="text"
              @click="isActive.value = false"
            >Close</v-btn>
          </v-card-actions>
        </v-card>
      </template>
    </v-dialog>

    <DynamicSnackbar ref="snackbar" />
    </v-card>


  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue'
  import axios from 'axios'
  import { useUserStore } from '@/stores/userStore'
  import { useImageUrl } from '@/composables/useImageUrl';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

  import {  emailRule,
  passwordRule,
  nameRule,
  birthdateRule,
  sexRule,
  contactNumberRule,
  confirmPasswordRule
   } from '@/composables/validationRules'; // Directly import the rule
   
   const form = ref(null);
   const snackbar = ref(null);
   const loading = ref(false);

const fname = ref('');
const lname = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const birthdate = ref('');
const sex = ref(null);
const contactNumber = ref('');
const role = ref('');
const visible = ref(false);
const visible2 = ref(false);

const editItemId = ref(null);

const confirmPasswordRules = confirmPasswordRule(password);


  const dialog = ref(false)
  const userStore = useUserStore()
  const search = ref('')
  const headers = ref([
    { title: 'Avatar', value: 'picture_url', sortable: false,  },
    { title: 'User', value: 'name', sortable: true },
    { title: 'Email', value: 'email',  sortable: true,  width: '100px' },
    { title: 'Role', value: 'role', sortable: true, width: '300px'},
    { title: 'Status', value: 'status',  sortable: true},
    { title: 'Action', value: 'action', sortable: false},
    // { title: 'Last Login', value: 'last_login_at' },
    // Add other headers as needed
  ])
  
  const addUser = async () => {
  const { valid } = await form.value.validate();

  if (valid) {
    try {
      const formData = {
        fname: fname.value,
        lname: lname.value,
        email: email.value,
        password: password.value, // Ensure you handle passwords securely!
        birthdate: birthdate.value,
        sex: sex.value,
        contact_number: contactNumber.value,
        role: role.value,
      };

      loading.value = true;

      // Replace 'admin/addUser' with the actual endpoint URL
      const response = await axios.post('admin/addUser', formData);

      // Handle response
      if (response.status === 201) {
        // Assuming getUsers is a function to refresh the user list
        await getUsers();
        // Assuming dialog is a reactive reference controlling the visibility of a dialog/form
        dialog.value = false;
        const successMessage = 'User added successfully';
      snackbar.value?.openSnackbar(successMessage, 'success');
      }
    } catch (error) {
      let errorMessage = 'An unknown error occurred. Please try again.';
      
      if (error.response && error.response.data && error.response.data.messages) {
        // Joining all error messages into a single string, separated by spaces.
        errorMessage = Object.values(error.response.data.messages).join(' ');
      }

      // Assuming snackbar is a method or a reference to a snackbar component to show errors
      if (snackbar.value) {
        snackbar.value.openSnackbar(errorMessage, 'error');
      }
    } finally {
      // Reset loading state in both success and error scenarios
      loading.value = false;
    }
  }
};


  const items = ref([])
  
  const getUsers = async () => {
    try {
      const response = await axios.get('/admin/users', {
        headers: { Authorization: `Bearer ${userStore.token}` }
      });
  
      // Process each user to add the full URL for the picture and combine names
      items.value = response.data.data.map(user => {
        const avatarData = useImageUrl(user.picture_url); // Use the composable
        return {
          ...user,
          name: user.fname + ' ' + user.lname,
          picture_url: avatarData.imageUrl || 'https://via.placeholder.com/64', // Use the imageUrl from the composable
        };
      });
    } catch (error) {
      console.error('Failed to fetch users:', error);
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
  onMounted(getUsers); // Simplified

  const handleIconClick = (item) => {
  if (editItemId.value === item.user_id) {
    // Save the changes as the save icon is clicked
    saveRole(item.user_id, item.role);
    editItemId.value = null; // Exit edit mode after save
  } else {
    // Enter edit mode
    editItemId.value = item.user_id;
  }
};

  const saveRole = async (userId, newRole) => {
  try {

    const response = await axios.post(`/admin/updateUserRole`, {
      role: newRole,
      user_id: userId,
    }, {
    });

    if (response.status === 200) {
        const successMessage = 'Role updated successfully';
      snackbar.value?.openSnackbar(successMessage, 'success');
      await getUsers();
     editItemId.value = (null);
    } 
  } catch (error) {
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    snackbar.value?.openSnackbar(errorMessage, 'error');
  }
};


const removeAccount = async (userId) => {
  try {

    const response = await axios.post(`/admin/removeUserAccount`, {
      user_id: userId,
    }, {
    });

    if (response.status === 200) {
        const successMessage = 'Account deactivated successfully';
      snackbar.value?.openSnackbar(successMessage, 'success');
      await getUsers();
     editItemId.value = (null);
    } 
  } catch (error) {
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    snackbar.value?.openSnackbar(errorMessage, 'error');
  }
};

  
  </script>
  