<template>
    <v-container
    class="fill-height  "
    fluid
  >
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        cols="12"
        sm="8"
        md="4"
      >
      <div class="text-center">
        <v-img
        :width="230"
        aspect-ratio="1/1"
        src="@/assets/images/pic7.png"
        class=" mx-auto"
      ></v-img>
      </div>
    <v-card
      class="py-8 px-6 text-center mx-auto ma-4 bg-background rounded-lg"
   
  flat
      max-width="400"
      width="100%"
    >
      <h3 class="text-h6 mb-4">Reset Password!</h3>
  
      <div class="text-body-2">
        Input your new password
      </div>
  
      <v-sheet color="background">
        <v-form ref="form" validate-on="submit lazy" submit.prevent="resetpassword">
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
label="Confirm Password"
:append-icon="visible2 ? 'mdi-eye-off' : 'mdi-eye'"
:type="visible2 ? 'text' : 'password'"
variant="underlined"
@click:append="visible2 = !visible2"
:rules="confirmPasswordRules" 
></v-text-field>


<v-btn
block
class="mb-8"
color="secondary"
size="large"
:loading="loading"
@click="resetpassword"
>Confirm</v-btn>

</v-form>
      </v-sheet>
  
  

      <v-card-text class="text-center">
        <router-link
          class="text-primary text-decoration-none text-caption"
          to="/login"
          rel="noopener noreferrer"
      
        >
         Back to Login <v-icon icon="mdi-chevron-right"></v-icon>
        </router-link>
      </v-card-text>
      
    </v-card>
</v-col>
</v-row>
<DynamicSnackbar ref="snackbar" />
</v-container>
  </template>
  <script setup>
  import { ref } from 'vue'
  import { passwordRule, confirmPasswordRule } from '@/composables/validationRules';
  import { useRoute } from 'vue-router'
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import axios from 'axios';
import router from '@/router';
  const visible = ref(false)
  const visible2 = ref(false)
  const password= ref('')
  const confirmPassword= ref('')
  const form= ref(null)
  const loading = ref(false)
  const snackbar = ref(null);

  const route = useRoute()
const email = route.query.email
const token = route.query.token

  const confirmPasswordRules = confirmPasswordRule(password);

  const resetpassword = async () => {

const { valid } = await form.value.validate();
if (valid) {
try {

  loading.value =true;
  // Replace 'your_api_endpoint' with the actual endpoint URL
  const response = await axios.post('auth/resetpassword', {
        token,
        email,
        password: password.value,
        confirmPassword: confirmPassword.value,
      })
  //console.log(response.data);
  if (response.status === 200) {
    router.push({ name: 'login' });
  };
    
} catch (error) {
  let errorMessage = 'An unknown error occurred. Please try again.';
  if (error.response && error.response.data && error.response.data.messages) {
    // Joining all error messages into a single string, separated by spaces.
    errorMessage = Object.values(error.response.data.messages).join(' ');
  }
  // Use the snackbar to display the error
  if (snackbar.value) {
    snackbar.value.openSnackbar(errorMessage, 'error');
  }
  loading.value =false;
}
}
  };
</script>