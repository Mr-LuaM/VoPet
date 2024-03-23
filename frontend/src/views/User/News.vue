<template>
  <v-card flat>
    <v-toolbar color="primary" extended style="height: 130px; outline-bottom: 4px solid #FE7839;" class="rounded-b">
      <v-toolbar-title class="font-weight-bold mt-6">Announcements</v-toolbar-title>
    </v-toolbar>

    <v-card class="mx-auto rounded-circle" max-width="130px" style="margin-top: -64px; height: 130px; display: flex; justify-content: center; align-items: center;">
      <v-avatar size="120">
        <v-img src="@/assets/images/pic12.png" alt="Profile Picture"></v-img>
      </v-avatar>
    </v-card>

    <v-col cols="12" sm="8" md="4" class="mx-auto ">

      <!-- Loop through grouped announcements -->
      <div v-for="(announcement, index) in groupedAnnouncements" :key="`announcement-group-${index}`">
        <!-- Date Subheader -->
        <v-subheader class="text-caption">
          {{ announcement.date }}
        </v-subheader>
        <!-- Announcement Cards -->
        
        <v-card v-for="(item, i) in announcement.items" :key="`item-${i}`" class="mt-3 " variant="flat">

          <v-card-title>
            <v-avatar size="40" class="mr-3 mt-2">
              <v-img :src="item.prependAvatar" alt="Profile Picture"></v-img>
            </v-avatar>
            {{ item.title }}
          </v-card-title>
          <v-card-subtitle class="text-primary">
           {{ item.author }}
          </v-card-subtitle>
          <v-card-text>
            <div>{{ item.content }}</div>
          </v-card-text>
        </v-card>
        <!-- Divider except after the last item -->
        <v-divider inset ></v-divider>
      </div>
   </v-col>
  </v-card>
</template>




<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useImageUrl } from '@/composables/useImageUrl';

const groupedAnnouncements = ref([]);

onMounted(async () => {
  try {
    const response = await axios.get('user/getAnnouncements');
    groupedAnnouncements.value = transformAndGroupAnnouncements(response.data.items);
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
});

function transformAndGroupAnnouncements(items) {
  // Group by date and map data
  const grouped = items.reduce((acc, item) => {
    const date = new Date(item.created_at).toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    if (!acc[date]) acc[date] = [];
    acc[date].push({
      title: item.title,
      content: item.content,
      author: `${item.fname} ${item.lname}`,
      prependAvatar: useImageUrl(item.picture_url).imageUrl,
    });
    return acc;
  }, {});

  // Transform into an array suitable for v-for
  return Object.entries(grouped).map(([date, items]) => ({ date, items }));
}
</script>
