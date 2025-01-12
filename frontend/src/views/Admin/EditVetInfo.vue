<template>
    <v-card flat >
        <v-toolbar
          color="background"
          extended
          height="500px"
          style="height: 130px; outline-bottom: 4px solid #FE7839;"
          class="rounded-b"
        >
        <v-btn
        icon
        class="hidden-xs-only mt-3"
        @click=" router.push({ name: 'settings' });"
      >
    
        <v-icon>mdi-arrow-left</v-icon>
    </v-btn>
        <v-toolbar-title class="font-weight-bold mt-3">Veterinary Information</v-toolbar-title>
        </v-toolbar>
    
        <v-card
    class="mx-auto rounded-circle"
    max-width="130px"
    style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"
  
  >
    <v-avatar size="120"> <!-- You can adjust the size as needed -->
      <v-img
      src="@/assets/images/pic11.png" alt="Profile Picture"
      ></v-img>
    </v-avatar>

   
  </v-card>
  <div class="text-center font-weight-bold text-h6 pt-3">Change Vet Info</div>
  <v-col
  cols="12"
  sm="8"
  md="4"
  class="mx-auto pa-10"
  
>
  <v-form validate-on="blur" ref="form"  @submit.prevent="changevetinfo"> 
    <v-text-field
    v-model="email"
     label="Vet Email"
     variant="underlined"
     :rules="emailRule"  
   ></v-text-field>
   <v-text-field
   v-model="contactNumber"
    label="Vet Contact Number"
    variant="underlined"
    :rules="contactNumberRule"  
  ></v-text-field>
  <v-text-field
  v-model="address"
   label="Vet Address"
   variant="underlined"
 ></v-text-field>
 <v-text-field
 v-model="socialMediaLink"
 label="Vet Social Media Link"
 variant="underlined"
 hint="e.g., https://www.instagram.com/yourvetclinic"
 :rules="socialmediaLinkRule"  
></v-text-field>

                 
<v-btn
block
class="mb-8 mt-2"
color="secondary"
size="large"
@click="changevetinfo"
:loading="loading"

>
Update Contacts
</v-btn>

    </v-form>
   
    </v-col>
    <DynamicSnackbar ref="snackbar" />
  </v-card>
  
  </template>
  <script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
import { emailRule, contactNumberRule,socialmediaLinkRule  } from '@/composables/validationRules';
import axios from 'axios';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

const router = useRouter();
const userStore = useUserStore();

const form = ref(null);
const snackbar = ref(null);
const loading = ref(false);

// Define your refs for form fields
const email = ref('');
const contactNumber = ref('');
const address = ref('');
const socialMediaLink = ref(''); // Add this for the social media link


// Assuming you have a ref for storing vet info
const vetInfo = ref([]);

onMounted(() => {
  const getVetInfo = async () => {
    try {
      const response = await axios.get('/admin/vet-info');
      const data = response.data;
      // Example of initializing form fields with fetched data
      const emailInfo = data.find(info => info.type === 'email');
      const phoneInfo = data.find(info => info.type === 'phone');
      const addressInfo = data.find(info => info.type === 'address');
      const socialMediaInfo = data.find(info => info.type === 'social media');

      if (emailInfo) email.value = emailInfo.value;
      if (phoneInfo) contactNumber.value = phoneInfo.value;
      if (addressInfo) address.value = addressInfo.value;
      if (socialMediaInfo) socialMediaLink.value = socialMediaInfo.value;
    } catch (error) {
      console.error('Failed to fetch vet info:', error);
    }
  };

  getVetInfo();
});

const changevetinfo = async () => {
  const { valid } = await form.value.validate();
  if (valid) {
    try {
      // Prepare the formData with all the vet info fields
      const formData = {
        email: email.value,
        contact_number: contactNumber.value,
        address: address.value,
        social_media_link: socialMediaLink.value, // Ensure you have this ref defined as mentioned earlier
      };
      loading.value = true;

      // Update the API endpoint to the one handling vet info updates
      const response = await axios.post('/admin/updateVetInfo', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` },
      });

      if (response.status === 200) {
        // Handle success scenario, e.g., updating local state, showing success message
        const successMessage = 'Vet info updated successfully';
        if (snackbar.value) {
          snackbar.value.openSnackbar(successMessage, 'success');
        }
      }
    } catch (error) {
      // Handle errors, e.g., showing error message
      let errorMessage = 'An unknown error occurred. Please try again.';
      if (error.response && error.response.data && error.response.data.messages) {
        errorMessage = Object.values(error.response.data.messages).join(' ');
      }
      if (snackbar.value) {
        snackbar.value.openSnackbar(errorMessage, 'error');
      }
    } finally {
      loading.value = false;
    }
  }
};

</script>
