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
            <v-window v-model="step">
                <v-window-item :value="1">
                    <div class="text-center">
                    <v-img
                    :width="200"
                    aspect-ratio="1/1"
                    src="@/assets/images/pic2.png"
                    class=" mx-auto"
                  ></v-img>
                    </div>
                  <v-card-text>
                  
                    <span class="text-h5  font-weight-bold" >
                    Welcome
                    </span>

                    <br>
                    <br>
                    <span class="text-caption  text-primary">
                        Swipe left to navigate
                        </span>
                  </v-card-text>
                </v-window-item>
          
                <v-window-item :value="2">
                    <div class="text-center">
                        <v-img
                        :width="200"
                        aspect-ratio="1/1"
                        src="@/assets/images/pic3.png"
                        class=" mx-auto"
                      ></v-img>
                        </div>
                      <v-card-text>
                      
                        <span class="text-caption  " >
                    
The Veterinary Office Pet Adoption System with GIS Technology is a dynamic web and mobile application designed to facilitate pet adoption and management for the CSJDM City Veterinary Office in Bulacan and its local community. It streamlines the adoption process, enables efficient pet information and account management, and incorporates GIS technology for enhanced pet owner statistics visualization. This platform serves as a bridge between the veterinary office and Bulacaños, offering a comprehensive suite of features for both administrators and potential pet adopters to improve communication, manage transactions, and support the welfare of pets.
                        </span>
    
                
                      </v-card-text>
                </v-window-item>
          
                <v-window-item :value="3">
                    <div class="text-center">
                        <v-img
                        :width="200"
                        aspect-ratio="1/1"
                        src="@/assets/images/pic4.png"
                        class=" mx-auto"
                      ></v-img>
                        </div>
                      <v-card-text>
                      
                        <span class="text-caption  " >
                    
                            "Embracing pet ownership is now simpler and more affordable. Discover and adopt your perfect pet companion effortlessly with our Veterinary Office Pet Adoption Platform. Log in today and begin your journey toward finding a loving, cost-effective match."                        </span>
    <br>
    <br>
    
    <v-btn
    block
    class="text-none mb-4"
    color="secondary"
    size="large"
    variant="flat"
    to="/login"  
  >
    Continue to Login
  </v-btn>
  
                      </v-card-text>
                </v-window-item>
              </v-window>
  </div>
 

  <v-card-actions class="justify-center">
    
    
    <v-item-group v-model="step" class="text-center" mandatory>
      <v-item v-for="n in 3" :key="`btn-${n}`" :value="n">
        <v-btn size="small" :variant="step === n ? 'tonal' : 'text'" :color="step === n ? 'primary' : ''"  icon="mdi-record" @click="() => step = n"></v-btn>
      </v-item>
    </v-item-group>
  </v-card-actions>
      </v-col>
    </v-row>
  </v-container>
  </template>
  <script setup>
  import { ref } from 'vue'

  const step = ref(1)


</script>