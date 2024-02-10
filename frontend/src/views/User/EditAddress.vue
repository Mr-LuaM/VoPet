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
        <v-toolbar-title class="font-weight-bold mt-3">Edit Address</v-toolbar-title>
        </v-toolbar>
    
        <v-card
    class="mx-auto rounded-circle"
    max-width="130px"
    style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;"
  
  >
    <v-avatar size="120"> <!-- You can adjust the size as needed -->
      <v-img
      src="@/assets/images/pic14.png" alt="Profile Picture">
      </v-img>
    </v-avatar>

   
  </v-card>
  <div class="text-center font-weight-bold text-h6 pt-3">Change Address</div>
  <div class="text-center font-weight-bold text-caption pt-3 text-primary">{{userStore.userDetails.address}}</div>

  <v-col
  cols="12"
  sm="8"
  md="4"
  class="mx-auto pa-10"
  
>
  <v-form validate-on="blur" ref="form"  @submit.prevent="changeaddress"> 
    <v-row>
      <v-col cols="12" >
        <v-autocomplete
        label="Region"
        v-model="selectedRegion" :items="regions" 
        variant="underlined"
        :rules="[v => !!v || 'Region is required']"
      ></v-autocomplete>
      </v-col>
      <v-col cols="12" >
        <v-autocomplete
        label="Province"
        v-model="selectedProvince" :items="provinces"  
        variant="underlined"
        :rules="[v => !!v || 'Province is required']"
      ></v-autocomplete>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" >
        <v-autocomplete
        label="Municipality"
        v-model="selectedMunicipality" :items="municipalities"  
        variant="underlined"
        :rules="[v => !!v || 'Municipality is required']"
      ></v-autocomplete>
      </v-col>
      <v-col cols="6" >
        <v-autocomplete
        label="Barangay"
        v-model="selectedBarangay" :items="barangays"
        variant="underlined"
        :rules="[v => !!v || 'Barangay is required']"
      ></v-autocomplete>
      </v-col>
      <v-col cols="6" >
        <v-text-field
        label="Zip Code"
        v-model="selectedZipcode" 
        variant="underlined"
        :rules="zipCodeRule"  
      ></v-text-field>
      </v-col>
    </v-row>
    
<v-btn
block
class="mb-8 mt-2"
color="secondary"
size="large"
@click="changeaddress"
:loading="loading"

>
change Address
</v-btn>

    </v-form>

    </v-col>
    <DynamicSnackbar ref="snackbar" />
  </v-card>
  
  </template>
  <script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
import { zipCodeRule } from '@/composables/validationRules';
import axios from 'axios';
import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
import address from '@/assets/json/address.json';
import zipcode from '@/assets/json/zipcode.json';

const router = useRouter();
const userStore = useUserStore();

const form = ref(null);
const snackbar = ref(null);
const loading = ref(false);

const selectedRegion = ref(null);
const regions = ref([]);
const selectedProvince = ref(null);
const provinces = ref([]);
const selectedMunicipality = ref(null);
const municipalities = ref([]);
const selectedBarangay = ref(null);
const barangays = ref([]);
const selectedZipcode = ref([]);

const addressData = ref(address); // Assuming address.json is correctly imported



watch(selectedRegion, (newValue) => {
  if (newValue) {
    // Adjusting to set the region name as the value instead of the code
    const selectedRegionKey = Object.keys(addressData.value).find(region => addressData.value[region].region_name === newValue);
    if (selectedRegionKey) {
      provinces.value = Object.keys(addressData.value[selectedRegionKey].province_list).map((province) => ({
        title: province,
        value: province
      }));
    }
    selectedProvince.value = null; // Reset
    selectedMunicipality.value = null;
    selectedBarangay.value = null;
  }
});


watch(selectedProvince, (newValue) => {
  if (newValue) {
    const selectedRegionData = addressData.value[Object.keys(addressData.value).find(region => addressData.value[region].region_name === selectedRegion.value)];
    const provinceData = selectedRegionData.province_list[newValue];
    municipalities.value = Object.keys(provinceData.municipality_list).map((municipality) => ({
      title: municipality,
      value: municipality
    }));
    selectedMunicipality.value = null; // Reset the selected municipality when a new province is selected
  }
});

watch(selectedMunicipality, (newValue) => {
  if (newValue && selectedProvince.value && selectedRegion.value) {
    const regionData = addressData.value[Object.keys(addressData.value).find(region => addressData.value[region].region_name === selectedRegion.value)];
    const provinceData = regionData.province_list[selectedProvince.value];
    const municipalityData = provinceData.municipality_list[newValue];
    barangays.value = municipalityData.barangay_list.map(barangay => ({
      title: barangay,
      value: barangay
    }));
    selectedBarangay.value = null; // Reset the selected barangay when a new municipality is selected
  }
});

watch(selectedBarangay, (newBarangay) => {
  const zipcodeData = ref(zipcode);
  if (newBarangay) {
    let foundZipCode = null;
    // Iterate through the zip code data to find a match for the selected barangay
    for (const [zipCode, locations] of Object.entries(zipcodeData.value)) {
      if (Array.isArray(locations)) {
        // If the locations are an array, check if the barangay is included
        if (locations.includes(newBarangay)) {
          foundZipCode = zipCode;
          break;
        }
      } else if (locations === newBarangay) {
        // Direct match with the barangay name
        foundZipCode = zipCode;
        break;
      }
    }
    selectedZipcode.value = foundZipCode || null;
  }
});

regions.value = Object.values(addressData.value).map(region => ({
  title: region.region_name,
  value: region.region_name // Set the region name as the value
}));


// Function to submit form data
const changeaddress = async () => {
  const { valid } = await form.value.validate();
  if (valid) {
    try {
      const formData = {
        region: selectedRegion.value,
        province: selectedProvince.value,
        municipality: selectedMunicipality.value,
        barangay: selectedBarangay.value,
        zipcode: selectedZipcode.value,
      };
      loading.value = true;

      const response = await axios.post('user/changeAddress', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` },
      });

      if (response.status === 200) {
        const successMessage = 'Address changed successfully';
        if (snackbar.value) {
          snackbar.value.openSnackbar(successMessage, 'success');
        }
        await userStore.fetchUserDetails();
        window.location.reload();
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
