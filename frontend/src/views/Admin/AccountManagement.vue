<template>
    <v-card class="ma-3 elevation-5" rounded >
      <v-card-title class="d-flex align-center pe-2 py-">
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
        <v-btn variant="flat" class="ml-4" color="primary" @click="clinicDialog = !clinicDialog">Add New Clinic</v-btn>

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
            :items="['admin', 'user', 'clinic']"
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
        <v-icon v-if="item.role != 'clinic'"
          size="small"
          class="me-2"
          @click="handleIconClick(item)"
        >
          {{ editItemId === item.user_id ? 'mdi-content-save' : 'mdi-pencil' }}
        </v-icon>
        <v-icon
          size="small"
          @click="showRemoveConfirmationDialog(item.user_id)" 
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
     scrollable
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
            <v-form validate-on="input lazy" ref="form" @submit.prevent="addUser" > 
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
 :items="['admin', 'user', 'clinic']"
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


    <v-dialog
    transition="dialog-top-transition"
    width="auto"
    v-model="clinicDialog"   persistent
   scrollable
    :location="right"
  >
    <template v-slot:default="{ isActive }">
      <v-card  width="900px">
        <v-toolbar
          color="secondary"
          title="Add new Clinic"
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
          <v-form validate-on="input lazy"  ref="clinicForm" @submit.prevent="addClinic" > 
              <v-row>
                <v-col cols="12" >
                  <v-text-field
                  v-model="clinicName"
                  label="Clinic Name"
                  :rules="[v => !!v || 'Clinic Name is required']"
                  variant="underlined"
                ></v-text-field>
                
                </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12" class="text-center">
                    Address
                    </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12" >
                    <v-autocomplete
                    label="Region"
                    v-model="selectedRegion" :items="regions" 
                    variant="underlined"
                    :rules="[v => !!v || 'Region is required']"
                  ></v-autocomplete>
                  </v-col>
                  <v-col cols="12" >
                    <v-autocomplete
                    label="Province"
                    v-model="selectedProvince" :items="provinces"  
                    variant="underlined"
                    :rules="[v => !!v || 'Province is required']"
                  ></v-autocomplete>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12" >
                    <v-autocomplete
                    label="Municipality"
                    v-model="selectedMunicipality" :items="municipalities"  
                    variant="underlined"
                    :rules="[v => !!v || 'Municipality is required']"
                  ></v-autocomplete>
                  </v-col>
                  <v-col cols="6" >
                    <v-autocomplete
                    label="Barangay"
                    v-model="selectedBarangay" :items="barangays"
                    variant="underlined"
                    :rules="[v => !!v || 'Barangay is required']"
                  ></v-autocomplete>
                  </v-col>
                  <v-col cols="6" >
                    <v-text-field
                    label="Zip Code"
                    v-model="selectedZipcode" 
                    variant="underlined"
                    :rules="zipCodeRule"  
                  ></v-text-field>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12" class="text-center">
                    User
                    </v-col>
                </v-row>
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
   hint="This will be auto validated so make sure to give the account securely"
  ></v-text-field>
             
  <v-text-field
                v-model="password"
                label="Password"
    :append-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
    :type="visible ? 'text' : 'password'"
  
    variant="underlined"
    @click:append="visible = !visible"
    hint= "Password must be at least 8 characters with a combination of 1 uppercase, 1 lowercase, 1 digit, and 1 special character"
    persistent-hint
    :rules="passwordRule" 
  ></v-text-field>
  
  <v-text-field
  v-model="confirmPassword"
  label="Confrim Password"
  :append-icon="visible2 ? 'mdi-eye-off' : 'mdi-eye'"
  :type="visible2 ? 'text' : 'password'"
  
  variant="underlined"
  @click:append="visible2 = !visible2"
  :rules="confirmPasswordRules" 
  ></v-text-field>
  <v-row>
  <v-col cols="6" >
  <v-text-field
  v-model="birthdate"
   label="Birthdate"
   variant="underlined"
   :rules="birthdateRule"  
   type="date"
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
  <v-text-field
  v-model="contactNumber"
   label="Contact Number"
   variant="underlined"
   :rules="contactNumberRule"  
  ></v-text-field>
               
<v-btn
block
class="mb-8 mt-2"
color="secondary"
size="large"
@click="addClinic"
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
  




    <ConfirmationDialog ref="confirmationDialog" :title="removeDialogTitle" :message="removeDialogMessage" :color="color" @confirm="handleRemoveConfirm" @cancel="handleCancel" />    <DynamicSnackbar ref="snackbar" />
    </v-card>


  </template>
  
  <script setup>
  import { ref, onMounted, watch } from 'vue'
  import axios from 'axios'
  import { useUserStore } from '@/stores/userStore'
  import { useImageUrl } from '@/composables/useImageUrl';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import ConfirmationDialog from '@/components/dialogs/confirmationDialog.vue';
  import { zipCodeRule } from '@/composables/validationRules';
  import address from '@/assets/json/address.json';
  import zipcode from '@/assets/json/zipcode.json';
  

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


const clinicDialog = ref(false);
const clinicName = ref('');
// Address components
const selectedRegion = ref(null);
const selectedProvince = ref(null);
const selectedMunicipality = ref(null);
const selectedBarangay = ref(null);
const selectedZipcode = ref('');
const addressData = ref(address); 
// Placeholder refs for dropdown items, you might need to fetch these from an API or define them statically
const regions = ref([]); // Should be filled with actual data
const provinces = ref([]); // Should be filled with actual data based on selectedRegion
const municipalities = ref([]); // Should be filled with actual data based on selectedProvince
const barangays = ref([]); // Assuming address.json is correctly imported


watch(selectedRegion, (newValue) => {
  if (newValue) {
    // Adjusting to set the region name as the value instead of the code
    const selectedRegionKey = Object.keys(addressData.value).find(region => addressData.value[region].region_name === newValue);
    if (selectedRegionKey) {
      provinces.value = Object.keys(addressData.value[selectedRegionKey].province_list).map((province) => ({
        title: province,
        value: province
      }));
    }
    selectedProvince.value = null; // Reset
    selectedMunicipality.value = null;
    selectedBarangay.value = null;
  }
});


watch(selectedProvince, (newValue) => {
  if (newValue) {
    const selectedRegionData = addressData.value[Object.keys(addressData.value).find(region => addressData.value[region].region_name === selectedRegion.value)];
    const provinceData = selectedRegionData.province_list[newValue];
    municipalities.value = Object.keys(provinceData.municipality_list).map((municipality) => ({
      title: municipality,
      value: municipality
    }));
    selectedMunicipality.value = null; // Reset the selected municipality when a new province is selected
  }
});

watch(selectedMunicipality, (newValue) => {
  if (newValue && selectedProvince.value && selectedRegion.value) {
    const regionData = addressData.value[Object.keys(addressData.value).find(region => addressData.value[region].region_name === selectedRegion.value)];
    const provinceData = regionData.province_list[selectedProvince.value];
    const municipalityData = provinceData.municipality_list[newValue];
    barangays.value = municipalityData.barangay_list.map(barangay => ({
      title: barangay,
      value: barangay
    }));
    selectedBarangay.value = null; // Reset the selected barangay when a new municipality is selected
  }
});

watch(selectedBarangay, (newBarangay) => {
  const zipcodeData = ref(zipcode);
  if (newBarangay) {
    let foundZipCode = null;
    // Iterate through the zip code data to find a match for the selected barangay
    for (const [zipCode, locations] of Object.entries(zipcodeData.value)) {
      if (Array.isArray(locations)) {
        // If the locations are an array, check if the barangay is included
        if (locations.includes(newBarangay)) {
          foundZipCode = zipCode;
          break;
        }
      } else if (locations === newBarangay) {
        // Direct match with the barangay name
        foundZipCode = zipCode;
        break;
      }
    }
    selectedZipcode.value = foundZipCode || null;
  }
});

regions.value = Object.values(addressData.value).map(region => ({
  title: region.region_name,
  value: region.region_name // Set the region name as the value
}));

const addClinic = async () => {
  loading.value = true; // Indicate the start of an async operation

  // Assemble the address from individual components
  const clinicAddress = [
    selectedBarangay.value,
    selectedMunicipality.value,
    selectedProvince.value,
    selectedRegion.value,
    selectedZipcode.value
  ].filter(Boolean).join(", "); // Ensure empty values are not included in the final address string

  // Prepare the data for submission
  const clinicData = {
    name: clinicName.value,
    address: clinicAddress,
    fname: fname.value,
        lname: lname.value,
        email: email.value,
        password: password.value, // Ensure you handle passwords securely!
        birthdate: birthdate.value,
        sex: sex.value,
        contact_number: contactNumber.value,
        role: role.value,
  };

  try {
    // Adjust the endpoint URL to match your API's endpoint for adding a clinic
    
    const response = await axios.post('admin/addNewClinic', clinicData);

    if (response.status === 200 || response.status === 201) {
      // Optionally, refresh the list of clinics or handle the successful addition
      // e.g., await fetchClinics();
      clinicDialog.value = false; // Close the dialog
      // Optionally, reset the form fields
      await getUsers();
      resetClinicForm();


      // Assuming snackbar is a method or a reference to a snackbar component to show success messages
      snackbar.value?.openSnackbar('Clinic added successfully', 'success');
    }
  } catch (error) {
    let errorMessage = 'An unknown error occurred. Please try again.';

    if (error.response && error.response.data) {
      errorMessage = error.response.data.message || Object.values(error.response.data).join(' ');
    }

    snackbar.value?.openSnackbar(errorMessage, 'error');
  } finally {
    loading.value = false; // Reset the loading state
  }
};


function resetClinicForm() {
  clinicName.value = '';
  selectedRegion.value = null;
  selectedProvince.value = null;
  selectedMunicipality.value = null;
  selectedBarangay.value = null;
  selectedZipcode.value = '';
  // Reset other fields as necessary
}

  const dialog = ref(false)
  const userStore = useUserStore()
  const search = ref('')
  const headers = ref([
    { title: 'Avatar', value: 'picture_url', sortable: false,  },
    { title: 'User', value: 'name', sortable: true },
    { title: 'Email', value: 'email',  sortable: true,  },
    { title: 'Role', value: 'role', sortable: true, width: '300px'},
    { title: 'Status', value: 'status',  sortable: true},
    { title: 'Clinic ID', value: 'clinic_id', sortable: true },
  { title: 'Clinic Name', value: 'clinic_name', sortable: true },
  { title: 'Clinic Creation Date', value: 'clinic_created_at', sortable: true },
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

const confirmationDialog = ref(null);
const currentUser = ref(null); // Assuming you have a ref for the current user
    const removeDialogMessage = ref(''); // Ref for the remove dialog message
    const removeDialogTitle = 'Remove User'; // Title for the remove dialog
    const color = ref('warning'); // Color for the dialog

 const showRemoveConfirmationDialog = (userId) => {
      // Find the user with the provided userId
      const user = items.value.find(u => u.user_id === userId); // Changed from users to items
      if (user) {
        currentUser.value = user;
        removeDialogMessage.value = `Are you sure you want to remove ${user.name}?`;
        confirmationDialog.value.openDialog();
      }
    };

    const handleRemoveConfirm = async () => {
      if (currentUser.value && currentUser.value.user_id) {
        // Perform remove account action using currentUser.value.user_id
        await removeAccount(currentUser.value.user_id);
        confirmationDialog.value.closeDialog();
        currentUser.value = null;
      } else {
        console.error('User ID is missing');
      }
    };

    const handleCancel = () => {
      // Reset currentUser if the user cancels
      currentUser.value = null;
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
  