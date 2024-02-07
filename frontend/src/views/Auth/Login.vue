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
                src="@/assets/images/pic2.png"
                class=" mx-auto"
              ></v-img>
              <v-card-text>
              
                <span class="text-h5  font-weight-bold" >
                Login
                </span>

                <br>
                <br>
                <v-form validate-on="lazy submit"  ref="form" @submit.prevent="login">

                    <v-text-field
                    v-model="email"
                      label="Email"
                      prepend-inner-icon="mdi-email"
                      variant="underlined"
                      :rules="emailRule" 
                    ></v-text-field>
                    <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
                        <a></a>
                
                        <router-link
                        class="text-caption text-decoration-none text-primary"
                        to="/forgotpassword" 
                      >
                        Forgot login password?
                      </router-link>
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
        Warning: After 7 consecutive failed login attempts, you account will be temporarily locked for three hours. If you must login now, you can also click "Forgot login password?" below to reset the login password.
      </v-card-text>
    </v-card>
    <v-btn
    block
    class="mb-8"
    color="secondary"
    size="large"
    :loading="loading"
    @click="login" 
  >
    Log In
  </v-btn>
  

  <v-card-text class="text-center">
    <router-link
      class="text-primary text-decoration-none text-caption"
      to="/signup"
      rel="noopener noreferrer"
  
    >
      Sign up now <v-icon icon="mdi-chevron-right"></v-icon>
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
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { emailRule, passwordRule } from '@/composables/validationRules'; // Assuming passwordRule is not used here
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
import { useUserStore } from '@/stores/userStore';


// Define reactive references
const email = ref('');
const password = ref('');
const visible = ref(false);
const loading = ref(false);
const snackbar = ref(null); // Assuming you have a snackbar component ref
const form = ref(null);

const router = useRouter();

const login = async () => {
  const userStore = useUserStore();
  const { valid } = await form.value.validate();
  if (valid) {
  try {
    loading.value = true;
    const response = await axios.post('/auth/login', {
      email: email.value,
      password: password.value,
    });

    if (response.status === 200) {
        userStore.login(response.data.token); // Store the token
        await userStore.fetchUserDetails(); // Fetch and store user details

        // Redirect based on user role, assuming role is now stored in userStore
        console.log("Redirecting to role:", userStore.role);
        if (userStore.role === 'admin') {
          router.push({ name: 'adminDashboard' });
        } else if (userStore.role === 'user') {
          router.push({ name: 'userDashboard' });
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

onMounted(() => {
  // Check if redirected with a logout reason
  const reason = router.currentRoute.value.query.reason;
  if (reason) {
    if (reason === 'tokenExpired') {
      snackbar.value.openSnackbar('You have been logged out due to inactivity. Please log in again.', 'info');
    }
    // Handle other reasons if necessary
  }
});

</script>
