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
        <v-toolbar-title class="font-weight-bold mt-3">Account Security</v-toolbar-title>
        </v-toolbar>
    
        <v-card
    class="mx-auto rounded-circle"
    max-width="130px"
    style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"
  
  >
    <v-avatar size="120"> <!-- You can adjust the size as needed -->
      <v-img
      src="@/assets/images/pic6.png" alt="Profile Picture"
      ></v-img>
    </v-avatar>

   
  </v-card>
  <div class="text-center font-weight-bold text-h6 pt-3">Change Password</div>
  <v-col
  cols="12"
  sm="8"
  md="4"
  class="mx-auto pa-10"
  
>
  <v-form validate-on="blur" ref="form"  @submit.prevent="changepassword"> 
    <v-text-field
    v-model="currentPassword"
    label=" Current Password"
:append-icon="visible3 ? 'mdi-eye-off' : 'mdi-eye'"
:type="visible3 ? 'text' : 'password'"

variant="underlined"
@click:append="visible3 = !visible3"

></v-text-field>
 
<v-text-field
    v-model="password"
    label="New Password"
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
label="Confirm Password"
:append-icon="visible2 ? 'mdi-eye-off' : 'mdi-eye'"
:type="visible2 ? 'text' : 'password'"

variant="underlined"
@click:append="visible2 = !visible2"
:rules="confirmPasswordRules" 
></v-text-field>
    
<v-btn
block
class="mb-8 mt-2"
color="secondary"
size="large"
@click="changepassword"
:loading="loading"

>
change password
</v-btn>

    </v-form>
    <div class="text-subtitle-1 text-medium-emphasis  text-center">
     

        <router-link
        class="text-caption text-decoration-none text-primary"
        to="/forgotpassword" 
      >
        Forgot password?
      </router-link>
      </div>
    </v-col>
    <DynamicSnackbar ref="snackbar" />
  </v-card>
  
  </template>
  <script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
import { passwordRule, confirmPasswordRule } from '@/composables/validationRules';
import axios from 'axios';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

const router = useRouter();
const userStore = useUserStore();

const form = ref(null);
const snackbar = ref(null);
const loading = ref(false);

const currentPassword = ref('');
const password = ref('');
const confirmPassword = ref('');
const visible = ref(false);
const visible2 = ref(false);
const visible3 = ref(false);

const confirmPasswordRules = confirmPasswordRule(password);

// Function to submit form data
const changepassword = async () => {
  const { valid } = await form.value.validate();
  if (valid) {
    try {
      const formData = {
        currentPassword: currentPassword.value,
        confirmPassword: confirmPassword.value,
        password: password.value,
      };
      loading.value = true;

      const response = await axios.post('user/changePassword', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` },
      });

      if (response.status === 200) {
        const successMessage = 'Password changed successfully';
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
