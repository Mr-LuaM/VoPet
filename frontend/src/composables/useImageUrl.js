// src/composables/useImageUrl.js

import { baseUrl } from '@/config/config.js';
// Import the default profile picture
import defaultPic from '@/assets/images/pic9.png';

export function useImageUrl(picture_url = '') {
  // Define the base URL for profile images
  // Computed image URL based on the availability of profileUrl
  const imageUrl = picture_url ? `${baseUrl}${picture_url}` : defaultPic;

  return { imageUrl };
}


