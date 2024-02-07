<template>
    <v-container
    class="fill-height"
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
                :width="200"
                aspect-ratio="1/1"
                src="@/assets/images/pic4.png"
                class=" mx-auto"
              ></v-img>
              <v-card-text>
              
                <span class="text-h5  font-weight-bold" >
                Register an account
                </span>

                <br>
                <br>
                <v-form validate-on="blur" ref="form"  @submit.prevent="signup"> 
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
    @click="signup"
    :loading="loading"
   
  >
    Sign Up
  </v-btn>

  <v-card-text class="text-center">
    <router-link
      class="text-primary text-decoration-none text-caption"
      to="/login"
      rel="noopener noreferrer"
  
    >
      Login <v-icon icon="mdi-chevron-right"></v-icon>
    </router-link>
  </v-card-text>
                  </v-form>
              </v-card-text>
  </div>
 

  
      </v-col>
    </v-row>


    <DynamicSnackbar ref="snackbar" />
  </v-container>


  

  
  </template>
  <script setup>
  import { ref } from 'vue';
  import {  emailRule,
  passwordRule,
  nameRule,
  birthdateRule,
  sexRule,
  contactNumberRule,
  confirmPasswordRule
   } from '@/composables/validationRules'; // Directly import the rule
   import axios from 'axios';
   import { useRouter } from 'vue-router';

   import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

   const form = ref(null);
   const snackbar = ref(null);
   const loading = ref(false)

  const fname = ref('');
const lname = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const birthdate = ref('');
const sex = ref('');
const contactNumber = ref('');
const visible = ref(false);
const visible2 = ref(false);

const confirmPasswordRules = confirmPasswordRule(password);
const router = useRouter();

// Function to submit form data
const signup = async () => {

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
    };
    loading.value =true;
    // Replace 'your_api_endpoint' with the actual endpoint URL
    const response = await axios.post('auth/signup', formData);
    
    //console.log(response.data);
    if (response.status === 201) {
      const userEmail = response.data.data.email; // Extract email from response
    router.push({ 
        name: 'verification', 
        params: { email: (userEmail) } 
    });
    console.log(userEmail);
      }
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
  