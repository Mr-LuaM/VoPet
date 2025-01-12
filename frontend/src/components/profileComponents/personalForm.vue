<template>
    <v-col
    cols="12"
    sm="8"
    md="4"
    class="mx-auto pa-10"
    
  >
    <v-form validate-on="blur" ref="form"  @submit.prevent="signup"> 
     
      
            <v-text-field
              v-model="fname"
              label="First Name"
              variant="underlined"
              :rules="nameRule" 
            ></v-text-field>
       
    
            <v-text-field
              v-model="lname"
              label="Last Name"
              variant="underlined"
              :rules="nameRule" 
            ></v-text-field>
      
   
        
     



<v-text-field
v-model="birthdate"
label="Birthdate"
variant="underlined"
:rules="birthdateRule"  
type="date"
></v-text-field>

<v-autocomplete
v-model="sex"
label="Sex"
:items="['Male', 'Female', 'Prefer not to say']"
variant="underlined"
:rules="sexRule"  
>
</v-autocomplete>

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
Update
</v-btn>


        </v-form>
        </v-col>
</template>
<script setup>
import { ref } from 'vue';
import { 
nameRule,
birthdateRule,
sexRule,
contactNumberRule,
 } from '@/composables/validationRules'; // Directly import the rule
 import axios from 'axios';
 import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

 const form = ref(null);
 const snackbar = ref(null);
 const loading = ref(false)

const fname = ref('');
const lname = ref('');
const birthdate = ref('');
const sex = ref('');
const contactNumber = ref('');


// Function to submit form data
const signup = async () => {

const { valid } = await form.value.validate();
if (valid) {
try {
  const formData = {
    fname: fname.value,
    lname: lname.value,
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
