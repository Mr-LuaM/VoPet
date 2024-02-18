<template>
    <v-app class="">
      <!-- Toolbar -->
      <v-app-bar app color="primary" class=" rounded">
        <v-btn icon @click="router.push({ name: 'usercontacts' });">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <v-toolbar-title>Chat Vet</v-toolbar-title>
      </v-app-bar>
  
      <!-- Main Chat Area -->
      <v-main>
        <v-container>
          <v-row justify="center" align="center" class="d-flex flex-column" style="height: 100%;">
            <v-col cols="12" sm="10" md="8" class="flex-grow-1">
              <v-card tile flat class="chat-window">
                <v-card-title class="text-h5 grey lighten-2">Vet Admin</v-card-title>
                <v-card-text class="chat-content pt-4" style="height: 100%; overflow-y: auto;">
                  <div v-for="(message, index) in chatMessages" :key="index">
                    <div class="message-row" :class="{ 'sender': message.me }">
                      <v-chip :color="message.me ? 'blue lighten-4' : 'green lighten-4'" class="message-chip">
                        <span class="message-text">{{ message.text }}</span> <!-- Wrap message text in a span -->
                      </v-chip>
                    </div>
                    <div class="message-row" :class="{ 'sender': message.me }">
                      <p class="timestamp">{{ message.time }}</p> <!-- Move timestamp to the next line -->
                    </div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            <v-col cols="12" sm="10" md="8">
              <v-card-actions class="pa-4">
                <v-text-field v-model="newMessage" label="Type a message" variant="underlined" hide-details class="flex-grow-1" @keyup.enter="sendMessage" autofocus></v-text-field>
                <v-btn color="primary" @click="sendMessage" :disabled="!newMessage.trim()" :loading="loading">Send</v-btn>
              </v-card-actions>
            </v-col>
          </v-row>
        </v-container>
      </v-main>
      
    </v-app>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import axios from 'axios'
  import { useUserStore } from '@/stores/userStore'
  import { jwtDecode as jwt_decode } from 'jwt-decode'
  import { useRouter } from 'vue-router';
  
  const router = useRouter();
  const chatMessages = ref([])
  const newMessage = ref('')
  const userStore = useUserStore()
  const loggedInUserId = jwt_decode(userStore.token).sub

  const loading = ref(false)
  
  const fetchMessages = async () => {
    try {
      const response = await axios.get('/user/messages', {
        headers: { Authorization: `Bearer ${userStore.token}` }
      })
      
      const mappedMessages = response.data.map(message => ({
        text: message.content,
        me: message.receiver_id === loggedInUserId,
        time: message.created_at
      }))
      
      chatMessages.value = mappedMessages
    } catch (error) {
      console.error('Failed to fetch messages:', error)
    }
  }
  
  const sendMessage = async () => {
    if (newMessage.value.trim() === '') return;
  
    try {
      loading.value = true;
      const messageData = {
        content: newMessage.value,
        created_at: new Date().toISOString(),
      };
      await axios.post('/user/sendMessages', messageData, {
        headers: { Authorization: `Bearer ${userStore.token}` }
      });
      await fetchMessages();
      newMessage.value = ''; // Clear the input field
    } catch (error) {
      console.error('Failed to send message:', error);
    } finally {
      loading.value = false;
    }
  };
  
  onMounted(async () => {
    await fetchMessages()
  })
  </script>
  
  <style scoped>
  .app-background {
    background-color: #f5f5f5;
  }
  
  .sidebar-drawer {
    height: 100%;
    overflow-y: auto;
  }
  
  .sidebar-list {
    padding-bottom: 64px; /* Adjust as needed */
  }
  
  .main-content {
    height: 100%;
    overflow-y: auto;
  }
  
  .chat-window {
    max-height: calc(100vh - 64px);
    overflow-y: auto;
  }
  
  .chat-content {
    background-color: #ffffff;
    overflow-y: auto;
  }
  
  .chat-messages {
    display: flex;
    flex-direction: column;
  }
  
  .message-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 8px;
  }
  
  .message-row.sender {
    justify-content: flex-start;
  }
  
  .message-chip {
    display: flex;
    align-items: center;
  }
  
  .timestamp {
    font-size: 0.75rem;
    margin-left: 4px;
  }
  </style>
  