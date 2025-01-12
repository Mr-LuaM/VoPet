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
  <v-container  >
    <v-app style="height: 80vh; overflow-y: hidden;">
      <!-- Toolbar -->
      <v-app-bar app color="secondary" class="elevation-8 rounded">
        <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
        <v-toolbar-title>Chat Application</v-toolbar-title>
      </v-app-bar>

      <!-- Sidebar -->
<v-navigation-drawer v-model="drawer" permanent class="sidebar-drawer">
  <v-list lines="two" class="sidebar-list">
    <v-list-subheader class="mx-2">Users</v-list-subheader>
    <v-list-item v-for="user in users" :key="user?.user_id" :value="user" rounded class="mx-2" @click="fetchMessages(user)">
      <template v-slot:prepend>
        <v-avatar color="grey-lighten-1">
          <v-img :src="user?.picture_url ? user.picture_url : 'https://via.placeholder.com/64'" height="32" width="32"></v-img>
        </v-avatar>
      </template>
      <v-list-item-title>{{ user?.fname }} {{ user?.lname }}</v-list-item-title>
      <v-list-item-subtitle :class="getUserColorClass(user.status)">{{ user?.status }}</v-list-item-subtitle>
    </v-list-item>
  </v-list>
</v-navigation-drawer>

      <!-- Main Chat Area -->
      <v-main>
        <v-container>
          <v-card tile flat>
            <v-card-title class="text-h5 grey lighten-2">{{selectedUser.fname}} {{selectedUser.lname}}</v-card-title>
            <v-card-text class="chat-content pt-4"  style="height: 50vh; overflow-y: auto;">
              <div class="chat-messages" v-for="(message, index) in chatMessages" :key="index">
                <div  class="message-row" :class="{ 'sender': message.me, 'receiver': !message.me }">
                  <v-chip :color="message.me ? 'blue lighten-4' : 'green lighten-4'" class="message-chip">
                    {{ message.text }}
                    <v-icon small class="ml-2">mdi-clock-outline</v-icon>
                  </v-chip>

                </div>
                <div  class="message-row"  :class="{ 'sender': message.me, 'receiver': !message.me }">
                  <p class="timestamp">{{ message.time }}</p> <!-- Move timestamp to the next line -->
                  </div>
              </div>
            </v-card-text>
            <v-card-actions class="pa-4">
              <v-text-field v-model="newMessage" label="Type a message" variant="underlined"   class="flex-grow-1" @keyup.enter="sendMessage" autofocus persistent-hint hide-details ></v-text-field>
              <v-btn color="primary" @click="sendMessage" :disabled="!newMessage.trim()" :loading="loading">Send</v-btn>
              
            </v-card-actions>
          </v-card>
        </v-container>
      </v-main>
    </v-app>
  </v-container>
  </v-main>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useUserStore } from '@/stores/userStore'
import { useImageUrl } from '@/composables/useImageUrl'
import { jwtDecode as jwt_decode } from 'jwt-decode'

const drawer = ref(false)
const chatMessages = ref([])
const users = ref([])
const newMessage = ref('')
const userStore = useUserStore()
const loggedInUserId = jwt_decode(userStore.token).sub
const loading = ref(false)
const selectedUser = ref([null]) // Global variable to store the selected user

  import { useRouter } from 'vue-router';
  
  const router = useRouter();






const fetchUserData = async () => {
  try {
    const response = await axios.get('/admin/users', {
      headers: { Authorization: `Bearer ${userStore.token}` }
    })

    users.value = response.data.data.map(user => {
      const avatarData = useImageUrl(user.picture_url)
      return {
        ...user,
        name: `${user.fname} ${user.lname}`,
        picture_url: avatarData.imageUrl || 'https://via.placeholder.com/64'
      }
    })
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

const fetchMessages = async (user) => {
  if (!user) return; // Check if user is null
// console.log('Fetching messages:ssdd :' ,  user);
  try {
    selectedUser.value = user;
    const response = await axios.post('/admin/messages', {
      receiver_id: user.user_id
    }, {
      headers: { Authorization: `Bearer ${userStore.token}` }
    })
    
    const mappedMessages = response.data.map(message => ({
      text: message.content,
      me: message.sender_id === loggedInUserId,
      time: message.created_at
    }))
    
    chatMessages.value = mappedMessages
  } catch (error) {
    console.error('Failed to fetch messages:', error)
  }
}

const getUserColorClass = (status) => {
  const colorClasses = {
    active: 'text-success',
    suspended: 'text-error'
  }
  return colorClasses[status] || ''
}

const sendMessage = async () => {
  if (!selectedUser.value || newMessage.value.trim() === '') return;

  try {
    loading.value = true;
    const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const messageData = {
      content: newMessage.value,
      sender_id: loggedInUserId,
      created_at: new Date().toISOString(),
      receiver_id: selectedUser.value.user_id
    };
    await axios.post('/admin/sendMessages', messageData, {
      headers: { Authorization: `Bearer ${userStore.token}` }
    });
    await fetchMessages(selectedUser.value);
    newMessage.value = ''; // Clear the input field
  } catch (error) {
    console.error('Failed to send message:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await fetchUserData()
  if (users.value.length > 0) {
    selectedUser.value = users.value[0];
    await fetchMessages(selectedUser.value);
  }
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
</style>
