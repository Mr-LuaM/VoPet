<template>
    <div class="px-2 mt-n2">
    <v-list :items="items" item-props lines="three" >
        <template v-slot:subtitle="{ subtitle }">
          <div v-html="subtitle"></div>
        </template>
      </v-list>
      </div>
   
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
    Object.keys(groupedAnnouncements).forEach(date => {
      transformedItems.push({ type: 'subheader', title: formatDate(date) });

      // Add announcements for this date
      groupedAnnouncements[date].forEach(announcement => {
        const { imageUrl } = useImageUrl(announcement.picture_url);

        // Add announcement item
        transformedItems.push({
          title: announcement.title,
          subtitle: `<span class="text-primary">${announcement.fname} ${announcement.lname}</span> &mdash; ${announcement.content}`,
          prependAvatar: imageUrl,
        });
      });
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
