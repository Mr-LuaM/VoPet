// src/composables/validationRules.js

// Email validation rule
export const emailRule = [
  v => !!v || 'Email is required',
  v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
];

// Password validation rule
export const passwordRule = [
  v => !!v || 'Password is required',
  v => v.length >= 8 || 'Password must be at least 8 characters long',
  v => /[A-Z]/.test(v) || 'Password must contain at least one uppercase letter',
  v => /[a-z]/.test(v) || 'Password must contain at least one lowercase letter',
  v => /[0-9]/.test(v) || 'Password must contain at least one number',
  v => /[\W_]/.test(v) || 'Password must contain at least one symbol',
];

// Name validation rule (applied to both First Name and Last Name)
export const nameRule = [
  v => !!v || 'Required field',
  v => v.length >= 2 || 'Name must be at least 2 characters',
];

// Confirm Password validation rule
// Assuming you have a reactive reference for the original password to compare against
export const confirmPasswordRule = (originalPassword) => [
  v => !!v || 'Required Field',
  v => v === originalPassword.value || 'Passwords must match',
];

// Birthdate validation rule
export const birthdateRule = [
  v => !!v || 'Birthdate is required',
  // Additional rules can be added based on your requirements
];

// Sex selection rule
export const sexRule = [
  v => !!v || 'Sex is required',
];

// Contact Number validation rule
export const contactNumberRule = [
  v => !!v || 'Contact number is required',
  v => /^\d{10,}$/.test(v) || 'Contact number must be at least 10 digits',
];
