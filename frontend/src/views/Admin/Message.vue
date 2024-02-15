<template>
    <v-app class="app-background">
      <!-- Sidebar -->
      <v-navigation-drawer permanent class="sidebar-color">
        <v-list lines="two">
            <v-list-subheader>REPORTS</v-list-subheader>
      
            <v-list-item
              v-for="(item, i) in users"
              :key="i"
              :value="item"
              color="primary"
              rounded="shaped"
            >
              <template v-slot:prepend>
                <v-avatar color="grey-lighten-1">
                    <v-icon color="white">mdi-folder</v-icon>
                  </v-avatar>
              </template>
      
              <v-list-item-title v-text="item.text"></v-list-item-title>
              <v-list-item-subtitle>hi</v-list-item-subtitle>

            </v-list-item>
          </v-list>
      </v-navigation-drawer>
  
      <!-- Main Chat Area -->
      <v-main>
        <v-container>
          <v-card tile flat class="chat-window">
            <v-card-title class="text-h5 grey lighten-2">ChatGPT Conversation</v-card-title>
            <v-card-text class="chat-content">
              <div class="chat-messages">
                <div v-for="(message, index) in chatMessages" :key="index" class="message-row" :class="{ 'sender': message.me, 'receiver': !message.me }">
                  <v-chip :color="message.me ? 'blue lighten-4' : 'green lighten-4'" class="message-chip">
                    {{ message.text }}
                    <v-icon small class="ml-2">mdi-clock-outline</v-icon>
                    <span class="timestamp">{{ message.time }}</span>
                  </v-chip>
                </div>
              </div>
            </v-card-text>
            <v-card-actions class="pa-4">
              <v-text-field v-model="newMessage" label="Type a message" solo flat hide-details class="flex-grow-1" @keyup.enter="sendMessage" autofocus></v-text-field>
              <v-btn color="primary" @click="sendMessage">Send</v-btn>
            </v-card-actions>
          </v-card>
        </v-container>
      </v-main>
    </v-app>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const items = ref([
    { title: 'Chat Message 1' },
    { title: 'Chat Message 2' },
    { title: 'Chat Message 3' },
  ]);
  
  const chatMessages = ref([
    { text: "Hello, how can I assist you today?", me: false, time: "10:00 AM" },
    { text: "I have a question about my account.", me: true, time: "10:01 AM" },
    { text: "Sure, I'd be happy to help with that!", me: false, time: "10:02 AM" },
  ]);
  const users = [
    { text: 'Real-Time', icon: 'mdi-clock' },
    { text: 'Audience', icon: 'mdi-account' },
    { text: 'Conversions', icon: 'mdi-flag' },
  ]
  const newMessage = ref('');
  
  function sendMessage() {
    if (newMessage.value.trim() !== '') {
      const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      chatMessages.value.push({ text: newMessage.value, me: true, time: currentTime });
      newMessage.value = ''; // Clear the input field
    }
  }
  </script>
  
  <style scoped>
  .app-background {
    background-color: #f5f5f5;
  }
  
  .sidebar-color {
    color: #ffffff;
    background-color: #333333;
  }
  
  .sidebar-card {
    max-height: calc(100vh - 48px);
    overflow-y: auto;
  }
  
  .chat-window {
    max-height: calc(100vh - 96px);
    overflow-y: auto;
  }
  
  .chat-content {
    background-color: #ffffff;
    height: 500px;
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
  
  .message-row.receiver {
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
  
  .float-right {
    float: right;
    clear: both;
  }
  </style>
  