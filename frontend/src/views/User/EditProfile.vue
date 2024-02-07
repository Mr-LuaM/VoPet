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
        <v-toolbar-title class="font-weight-bold mt-3">Personal Information</v-toolbar-title>
        </v-toolbar>
    
        <v-card
    class="mx-auto rounded-circle"
    max-width="130px"
    style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"
  
  >
    <v-avatar size="120"> <!-- You can adjust the size as needed -->
        <v-img
        :src="avatarUrl" alt="Profile Picture"
        ></v-img>
    </v-avatar>

   
  </v-card>
  <div class="text-center font-weight-bold text-h6 pt-3">Change Personal Information</div>
  <v-col
  cols="12"
  sm="8"
  md="4"
  class="mx-auto pa-10"
  
>
<v-form validate-on="blur" ref="form1"  @submit.prevent="changeProfilePicture"> 
    <v-file-input
    v-model="profile_url"
    label="Profile Picture"
    variant="solo-filled"
    prepend-inner-icon="mdi-camera"
    prepend-icon=""
    class="mb-4"
    center-affix
    show-size
    accept="image/*"
    :rules="profileRule"
  ></v-file-input>
  <v-btn
block
class="mb-8 mt-2"
color="secondary"
size="large"
@click="changeProfilePicture"
:loading="loading1"

>
Update Profile Picture
</v-btn>
  </v-form>
<v-form validate-on="submit lazy" ref="form"  @submit.prevent="changePersonalInformation"> 
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
<v-btn
block
class="mb-8 mt-2"
color="secondary"
size="large"
@click="changePersonalInformation"
:loading="loading"

>
Update Information
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
import { nameRule, birthdateRule,sexRule,profileRule } from '@/composables/validationRules';
import axios from 'axios';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';

const router = useRouter();
const userStore = useUserStore();

const form = ref(null);
const snackbar = ref(null);
const loading = ref(false);
const form1 = ref(null);;
const loading1 = ref(false);

const fname = ref('');
const lname = ref('');
const birthdate = ref('');
const sex = ref('');
const profile_url = ref(null);
const { imageUrl: avatarUrl } = useImageUrl(userStore.userDetails.picture_url);
import { useImageUrl } from '@/composables/useImageUrl';
onMounted(() => {
  if (userStore.userDetails) {
    fname.value = userStore.userDetails.fname || '';
    lname.value = userStore.userDetails.lname || '';
    birthdate.value = userStore.userDetails.birthdate || '';
    sex.value = userStore.userDetails.sex || '';
  }
});
// Function to submit form data
const changePersonalInformation = async () => {
  const { valid } = await form.value.validate();
  if (valid) {
    try {
      const formData = {
        fname: fname.value,
        lname: lname.value,
        birthdate: birthdate.value,
        sex: sex.value,
      };
      loading.value = true;

      const response = await axios.post('user/changePersonalInformation', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` },
      });

      if (response.status === 200) {
        userStore.userDetails.fname = fname.value;
        userStore.userDetails.lname = lname.value;
        userStore.userDetails.birthdate = birthdate.value;
        userStore.userDetails.sex = sex.value;
        const successMessage = 'Profile changed successfully';
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



const changeProfilePicture = async () => {
  // Assuming there's a ref for the file input named profilePicture
  const { valid } = await form1.value.validate();
  if (valid) {

  // Initialize FormData object for file upload
  let formData = new FormData();
// To something like this, assuming profile_url is the actual File object
if (profile_url.value && profile_url.value.length > 0) {
  // If profile_url is an array of files, take the first one
  formData.append('profile_url', profile_url.value[0]);
} else {
  // Handle the case where no file is selected
  console.error("No file selected.");
  return;
} // Assuming the file input is stored in profilePicture ref

  try {
    loading1.value = true; // Start loading indicator

    const response = await axios.post('user/changeProfilePicture', formData, {
      headers: {
        'Authorization': `Bearer ${userStore.token}`,
        'Content-Type': 'multipart/form-data',
      },
    });

    // Handle successful response
    if (response.status === 200) {
      // Optionally, update the userStore with new profile picture info if needed
      // e.g., userStore.userDetails.profile_picture = URL/path returned by server
      const successMessage = 'Profile picture updated successfully';
      snackbar.value?.openSnackbar(successMessage, 'success');
      await userStore.fetchUserDetails();
      window.location.reload();
    }
  } catch (error) {
    // Handle errors
    let errorMessage = 'An unknown error occurred. Please try again.';
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    snackbar.value?.openSnackbar(errorMessage, 'error');
  } finally {
    loading1.value = false; // Stop loading indicator
  }
}
};

</script>