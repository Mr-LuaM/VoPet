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
                src="../assets/images/pic2.png"
                class=" mx-auto"
              ></v-img>
              <v-card-text>
              
                <span class="text-h5  font-weight-bold" >
                Login
                </span>

                <br>
                <br>
                <v-form validate-on=" lazy" > <!-- Ensure you define the submit method -->
                    <v-text-field
                    v-model="email"
                      label="Email"
                      prepend-inner-icon="mdi-email"
                      variant="underlined"
                      :rules="emailRule" 
                    ></v-text-field>
                    <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                        <a></a>
                
                        <a
                          class="text-caption text-decoration-none text-primary"
                          href="#"
                          rel="noopener noreferrer"
                          target="_blank"
                        >
                          Forgot login password?</a>
                      </div>
                    <v-text-field
                    v-model="password"
                    label="Password"
        :append-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
        :type="visible ? 'text' : 'password'"
        placeholder="Enter your password"
        prepend-inner-icon="mdi-lock-outline"
        variant="underlined"
        @click:append="visible = !visible" 
      ></v-text-field>
      <br>
      <v-card
      class="mb-6"
      variant="tonal"
    >
      <v-card-text class="text-medium-emphasis text-caption">
        Warning: After 3 consecutive failed login attempts, you account will be temporarily locked for three hours. If you must login now, you can also click "Forgot login password?" below to reset the login password.
      </v-card-text>
    </v-card>
    <v-btn
    block
    class="mb-8"
    color="secondary"
    size="large"
   
  >
    Log In
  </v-btn>

  <v-card-text class="text-center">
    <a
      class="text-primary text-decoration-none text-caption"
      href="signup"
      rel="noopener noreferrer"
  
    >
      Sign up now <v-icon icon="mdi-chevron-right"></v-icon>
    </a>
  </v-card-text>
                  </v-form>
              </v-card-text>
  </div>
 

  
      </v-col>
    </v-row>
  </v-container>
  </template>
  <script setup>
  import { ref } from 'vue';
  import { emailRule, passwordRule } from '@/composables/validationRules'; // Directly import the rule
  
  const email = ref('');
  const password = ref('');
  const visible = ref(false)
  </script>
  