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
        <v-toolbar-title class="font-weight-bold mt-3">Contact Information</v-toolbar-title>
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
  <div class="text-center font-weight-bold text-h6 pt-3">Change Contacts</div>
  <v-col
  cols="12"
  sm="8"
  md="4"
  class="mx-auto pa-10"
  
>
  <v-form validate-on="blur" ref="form"  @submit.prevent="changeContact"> 
    <v-text-field
    v-model="email"
     label="Email"
     variant="underlined"
     :rules="emailRule"  
     disabled
   ></v-text-field>
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
@click="changeContact"
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
import { emailRule, contactNumberRule } from '@/composables/validationRules';
import axios from 'axios';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

const router = useRouter();
const userStore = useUserStore();

const form = ref(null);
const snackbar = ref(null);
const loading = ref(false);

const email = ref('');
const contactNumber = ref('');

onMounted(() => {
  if (userStore.userDetails) {
    email.value = userStore.userDetails.email || '';
    contactNumber.value = userStore.userDetails.contact_number || '';
  }
});
// Function to submit form data
const changeContact = async () => {
  const { valid } = await form.value.validate();
  if (valid) {
    try {
      const formData = {
        contact_number: contactNumber.value,
      };
      loading.value = true;

      const response = await axios.post('user/changeContact', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` },
      });

      if (response.status === 200) {
        userStore.userDetails.contact_number = contactNumber.value;
        const successMessage = 'Contacts changed successfully';
        if (snackbar.value) {
          snackbar.value.openSnackbar(successMessage, 'success');
        }
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
    }
  }
};
</script>
