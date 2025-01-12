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
            :width="200"
            aspect-ratio="1/1"
            src="@/assets/images/pic6.png"
            class=" mx-auto"
          ></v-img>
          </div>
        <v-card
          class="py-8 px-6 text-center mx-auto ma-4 bg-background rounded-lg"
       
      flat
          max-width="400"
          width="100%"
        >
          <h3 class="text-h6 mb-4">Forgot Password? </h3>
            <div class="text-body-2">
                Enter your email and we'll send you instructions to reset your password
              </div>
              <v-sheet color="background">
                <v-form ref="form" validate-on="submit lazy" submit.prevent="forgotpassword">
                    <v-text-field
                    v-model="email"
                      label="Email"
                      prepend-inner-icon="mdi-email"
                      variant="underlined"
                      :rules="emailRule" 
                    ></v-text-field>
                    <v-btn
                    block
                    class="mb-8"
                    color="secondary"
                    size="large"
                    :loading="loading"
                    @click="forgotpassword"
                   
                  >
                  Submit
                  </v-btn>
            </v-form>

            <div class="text-body-2 text-success" >
              {{ message }}
            </div>
            <v-card-text class="text-center">
              <router-link
                class="text-primary text-decoration-none text-caption"
                to="/login"
                rel="noopener noreferrer"
            
              >
               Back to Login <v-icon icon="mdi-chevron-right"></v-icon>
              </router-link>
            </v-card-text>
              </v-sheet>
   
            </v-card>

       
          


  




   
</v-col>
</v-row>
<DynamicSnackbar ref="snackbar" />
</v-container>
  </template>
  <script setup>

import { ref } from 'vue'
  import { emailRule } from '@/composables/validationRules';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import axios from 'axios';
  const form = ref(null)
  const loading = ref(false)
  const email = ref('')
  const snackbar = ref(null)
  const message = ref('');




  const forgotpassword = async () => {
  const { valid } = await form.value.validate();
  if (valid) {
  try {
    loading.value = true;
    const response = await axios.post('/auth/forgotpassword', {
      email: email.value,
    });

    if (response.status === 200) {
         message.value = response.data.message
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
}
</script>