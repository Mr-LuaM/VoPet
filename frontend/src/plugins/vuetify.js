// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Vuetify
import { createVuetify } from 'vuetify'
import * as components from "vuetify/components";
import * as labsComponents from "vuetify/labs/components";


const light = {
  dark: false,
  colors: {
    background: '#FFFFFF',
    surface: '#FAFAFA',
    'surface-bright': '#FFFFFF',
    'surface-light': '#F5F5F5',
    'surface-variant': '#E0E0E0',
    'on-surface-variant': '#424242', // For contrast on lighter surfaces
    primary: '#FC238E',
    'primary-darken-1': '#DB1F78', // Slightly darkened primary color
    secondary: '#FE7839',
    'secondary-darken-1': '#E56B34', // Slightly darkened secondary color
    error: '#B00020',
    info: '#2196F3',
    success: '#4CAF50',
    warning: '#FB8C00',
  },
};

const dark = {
  dark: true, // Ensure this is set to true for dark theme
  colors: {
    background: '#121212',
    surface: '#1E1E1E',
    'surface-bright': '#2E2E2E', // A bit brighter surface color for differentiation
    'surface-light': '#232323', // Slightly lighter than the base surface
    'surface-variant': '#424242',
    'on-surface-variant': '#E0E0E0', // Light text on dark surfaces
    primary: '#FC238E',
    'primary-darken-1': '#DB1F78', // Maintaining the same darkened primary color
    secondary: '#FE7839',
    'secondary-darken-1': '#E56B34', // Maintaining the same darkened secondary color
    error: '#CF6679', // Error color adjusted for dark theme visibility
    info: '#64B5F6', // Info color adjusted for dark theme
    success: '#81C784', // Success color adjusted for dark theme
    warning: '#FFB74D', // Warning color adjusted for dark theme
  },
};




export default createVuetify({
  // https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
  components: {
    ...components,
    ...labsComponents,
  },
  icons: {
    defaultSet: "mdi",
  },
  theme: {
    themes: {
      dark,
      light,
    },
  },
});
