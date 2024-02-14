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

    <v-col cols="12" sm="8" md="4" class="mx-auto pa-0">
      <v-list :items="items" item-props lines="three">
        <template v-slot:subtitle="{ subtitle }">
          <div v-html="subtitle"></div>
        </template>


      </v-list>
    </v-col>
  </v-card>
</template>


<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useImageUrl } from '@/composables/useImageUrl'; // Ensure the path matches where you've defined this composable

const items = ref([]);

onMounted(async () => {
  try {
    const response = await axios.get('user/getAnnouncements');
    let transformedItems = [];

    // Group announcements by date
    const groupedAnnouncements = groupByDate(response.data.items);

    // Iterate through each group and add a subheader for each date
    Object.keys(groupedAnnouncements).forEach((date, index, array) => {
      transformedItems.push({ type: 'subheader', title: formatDate(date) });

      // Add announcements for this date
      groupedAnnouncements[date].forEach((announcement, announcementIndex, announcementArray) => {
        const { imageUrl } = useImageUrl(announcement.picture_url);

        // Add announcement item
        transformedItems.push({
          title: announcement.title,
          subtitle: `<span class="text-primary">${announcement.fname} ${announcement.lname}</span> &mdash; ${announcement.content}`,
          prependAvatar: imageUrl,
        });

        // Add a divider after each announcement except the last one in each group
        if (announcementIndex < announcementArray.length - 1) {
          transformedItems.push({ type: 'divider', inset: true });
        }
      });

      // Optionally, add a divider between dates except after the last date group
      if (index < array.length - 1) {
        transformedItems.push({ type: 'divider' });
      }
    });

    items.value = transformedItems;
  } catch (error) {
    console.error('Error fetching announcements:', error);
  }
});


// Function to group announcements by date
function groupByDate(announcements) {
  const grouped = {};
  announcements.forEach(announcement => {
    const date = new Date(announcement.created_at).toDateString();
    if (!grouped[date]) {
      grouped[date] = [];
    }
    grouped[date].push(announcement);
  });
  return grouped;
}

// Function to format date in a more human-readable format
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}
</script>
