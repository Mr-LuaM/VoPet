<template>
  <div class="px-2 mt-n2">
    <v-card-title class="d-flex align-center pe-2 py-">
<a></a>      <v-spacer></v-spacer>

      <v-btn variant="flat" class="ml-4" color="secondary"   @click="openAddAnnouncementDialog">Add New Announcement</v-btn>
    </v-card-title>
    <v-list :items="items" item-props lines="three">
      <template v-slot:subtitle="{ subtitle }">
        <div v-html="subtitle"></div>
      </template>
      <template v-slot:append="{ item }">
        <v-list-item-action start>
          <v-icon @click="editAnnouncement(item)" class="mr-2">mdi-pencil</v-icon>
          <v-icon @click="showDeleteConfirmation(item.id)" class="mr-2">mdi-delete</v-icon>
        </v-list-item-action>
      </template>
    </v-list>
    <ConfirmationDialog ref="confirmationDialog" :title="dialogTitle" :message="dialogMessage" :color="color" @confirm="handleConfirm" @cancel="handleCancel" />
    <DynamicSnackbar ref="snackbar" />





    <v-dialog
    transition="dialog-top-transition"
    width="auto"
    v-model="dialog"
    persistent
    scrollable
  >
    <template v-slot:default="{ isActive }">
      <v-card width="900px">
        <v-toolbar color="secondary">
          <v-toolbar-title>{{ dialogTitle }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn variant="plain" @click="dialog = false" icon="mdi-close"></v-btn>
        </v-toolbar>
        <div class="text-center mt-5">
          <v-img
          :width="150"
            aspect-ratio="1/1"
            src="@/assets/images/pic13.png" 
            class="mx-auto"
          ></v-img>
        </div>
        <v-card-text>
          <v-row align="center" justify="center">
            <v-col cols="8">
              <v-form validate-on="blur" ref="form" @submit.prevent="addOrUpdateAnnouncement">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="announcementTitle"
                      label="Title"
                      variant="underlined"
                      :rules="[v => !!v || 'Title is required']"
                    ></v-text-field>
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12">
                    <v-textarea
                      v-model="announcementContent"
                      label="Content"
                      variant="underlined"
                      :rules="[v => !!v || 'Content is required']"
                    ></v-textarea>
                  </v-col>
                </v-row>
                <v-btn
                  block
                  class="mb-8 mt-2"
                  color="secondary"
                  size="large"
                  @click="addOrUpdateAnnouncement"
                  :loading="loading"
                >
                  {{ isEditMode ? 'Update' : 'Add' }}
                </v-btn>
              </v-form>
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="dialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </template>
  </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useImageUrl } from '@/composables/useImageUrl'; // Ensure the path matches where you've defined this composable
  import ConfirmationDialog from '@/components/dialogs/confirmationDialog.vue';
  import DynamicSnackbar from '@/components/snackbars/dynamicSnack.vue';
  import { useUserStore } from '@/stores/userStore'; // Adjust the path if necessary
  const userStore = useUserStore();

const confirmationDialog = ref(null); // Define a ref for the confirmation dialog
const currentAnnouncement = ref(null); // Define a ref to store the current announcement to be deleted
const dialogTitle = ref('Confirm Delete');
const dialogMessage = ref('Are you sure you want to delete this announcement?');
const color = ref('error');
const snackbar = ref(null);

const items = ref([]);


const isEditMode = ref(false);
const dialog = ref(false)


const announcementContent = ref(null);
const announcementTitle = ref(null);
const announcementId = ref(null);


const form = ref(null);
   const loading = ref(false);

   const getNews =  async () => {
  try {
    const response = await axios.get('admin/getAnnouncements');
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
          id: announcement.announcement_id, // Add ID for identifying items
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
}

onMounted(getNews);

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

// Function to handle edit action
function editItem(item) {
  // Implement edit logic here, e.g., open a modal for editing
  console.log('Editing item:', item);
}

// Function to handle delete action and show confirmation dialog
function showDeleteConfirmation(itemId) {
  // Find the announcement with the provided itemId
  console.log(itemId)
  const announcement = items.value.find(a => a.id === itemId);
  if (announcement) {
    currentAnnouncement.value = announcement;
    confirmationDialog.value.openDialog(); // Open the confirmation dialog
  }
}

// Function to confirm deletion
function handleConfirm() {
  if (currentAnnouncement.value && currentAnnouncement.value.id) {
    // Perform deletion action here using currentAnnouncement.value.id
    deleteAnnouncement(currentAnnouncement.value.id);
    confirmationDialog.value.closeDialog(); // Close the confirmation dialog
    currentAnnouncement.value = null; // Reset currentAnnouncement after deletion
  } else {
    console.error('Announcement ID is missing');
  }
}

// Function to handle cancellation of deletion
function handleCancel() {
  // Reset currentAnnouncement if the user cancels
  currentAnnouncement.value = null;
}

// Function to delete an announcement
async function deleteAnnouncement(announcementId) {
  try {
    const response = await axios.post(`admin/deleteAnnouncement`, { announcementId }); // Use POST request with announcementId in the request body
    if (response.status === 200) {
      items.value = items.value.filter(item => item.id !== announcementId);
      snackbar.value?.openSnackbar('Announcement deleted successfully', 'success'); // Show success snackbar
    } else {
      throw new Error('Failed to delete announcement');
    }
  } catch (error) {
    console.error('Error deleting announcement:', error);
    snackbar.value?.openSnackbar('Failed to delete announcement. Please try again.', 'error'); // Show error snackbar
  }
}

const resetFormFields = () => {
  announcementTitle.value = '';
  announcementContent.value = '';
  announcementId.value = ''; // Clear announcement ID if any
  isEditMode.value = false;
  dialogTitle.value = 'Add New Announcement';
};

const editAnnouncement = (announcement) => {
  const subtitle = announcement.subtitle;
  const contentRegex = /<span class="text-primary">.*<\/span> &mdash; (.*)/;
  const match = contentRegex.exec(subtitle);
  console.log(announcement);
  announcementId.value = announcement.id
  announcementTitle.value = announcement.title;
  if (match && match.length > 1) {
    const content = match[1];
    console.log(content); // This will log the content part
    // Assign the content to the appropriate variable or field
    announcementContent.value = content;
  }
  // Add more fields if needed
  isEditMode.value = true;
  dialogTitle.value = 'Edit Announcement';
  dialog.value = true;
};

const addOrUpdateAnnouncement = async () => {
  // Validation logic if needed

  loading.value = true;
  try {
    const formData = {
      title: announcementTitle.value,
      content: announcementContent.value,
      announcement_id: announcementId.value, // Update to match the backend naming
      // Add more fields if needed
    };

    let response;
    if (isEditMode.value) {
      // Update existing announcement
      response = await axios.post('/admin/updateAnnouncement', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` }, // Include authorization token
      });
    } else {
      // Add new announcement
      response = await axios.post('/admin/addAnnouncement', formData, {
        headers: { Authorization: `Bearer ${userStore.token}` }, // Include authorization token
      });
    }

    if (response.status === 201 || response.status === 200) {
      const successMessage = isEditMode.value ? 'Announcement updated successfully' : 'Announcement added successfully';
        snackbar.value?.openSnackbar(successMessage, 'success');
        await getNews();
      resetFormFields();
      dialog.value = false;
    }
  } catch (error) {
    let errorMessage = 'An unknown error occurred. Please try again.';
      if (error.response && error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      }
  } finally {
    loading.value = false;
  }
};

function openAddAnnouncementDialog() {
  resetFormFields();
  dialog.value = true; // Open the dialog
}


</script>
