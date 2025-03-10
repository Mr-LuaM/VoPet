// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import { jwtDecode as jwt_decode } from "jwt-decode";
import { useUserStore } from '@/stores/userStore';
import mobileNav from '@/layouts/mobileNav';
import webNav from '@/layouts/webNav';

// Auth routes
const authRoutes = [
  {
    path: '/',
    name: 'startup',
    component: () => import('../views/Auth/StartUp.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/Auth/Login.vue'),
  },
  {
    path: '/signup',
    name: 'signup',
    component: () => import('../views/Auth/Signup.vue'),
  },
  {
    path: '/verification/:email',
    name: 'verification',
    component: () => import('../views/Auth/Verification.vue'),
    props: true,
  },
  {
    path: '/forgotpassword',
    name: 'forgotpassword',
    component: () => import('../views/Auth/ForgotPassword.vue'),
  },
  {
    path: '/reset-password',
    name: 'resetpassword',
    component: () => import('../views/Auth/ResetPassword.vue'),
    props: (route) => ({
      token: route.query.token,
      email: route.query.email
    })
  },
];

// Shared routes accessible to both admin and clinic
const sharedAdminClinicRoutes = [
  {
    path: '/admin',
    component: webNav,
    meta: { requiresAuth: true, roles: ['admin', 'clinic'] },
    children: [
      {
        path: 'dashboard',
        name: 'adminDashboard', // Shared
        component: () => import('../views/Admin/Dashboard.vue'),
      },
      {
        path: 'map',
        name: 'mapDashboard', // Shared
        component: () => import('../views/Admin/Map.vue'),
      },
      {
        path: 'petManagement',
        name: 'petManagement', // Shared
        component: () => import('../views/Admin/PetManagement.vue'),
      },
      {
        path: 'medicalHistory',
        name: 'medicalHistory', // Shared
        component: () => import('../views/Admin/MedicalHistory.vue'),
      },
      {
        path: 'transaction',
        name: 'transaction', // Shared
        component: () => import('../views/Admin/Transaction.vue'),
      },
      {
        path: 'history',
        name: 'history', // Shared
        component: () => import('../views/Admin/History.vue'),
      },
      {
        path: 'message',
        name: 'message', // Shared
        component: () => import('../views/Admin/Message.vue'),
      },
      {
        path: 'profile',
        name: 'profile', // Shared
        component: () => import('../views/Admin/Profile.vue'),
      },
      {
        path: 'edit/profile', // Path: /user/edit/profile
        name: 'admin-edit-profile', // Shared
        component: () => import('../views/Admin/EditProfile.vue'),
      },
      {
        path: 'edit/contacts', // Path: /user/edit/contacts
        name: 'admin-edit-contacts', // Shared
        component: () => import('../views/Admin/EditContacts.vue'),
      },
      {
        path: 'edit/address', // Path: /user/edit/address
        name: 'admin-edit-address', // Shared
        component: () => import('../views/Admin/EditAddress.vue'),
      },
      {
        path: 'edit/security', // Path: /user/edit/security
        name: 'admin-security', // Shared
        component: () => import('../views/Admin/EditSecurity.vue'),
      },
      {
        path: 'settings', // Path: /user/edit/security
        name: 'settings', // Shared
        component: () => import('../views/Admin/Settings.vue'),
      },
    ],
  },
];

// Admin routes
const adminRoutes = [
  {
    path: '/admin',
    component: webNav,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      ...sharedAdminClinicRoutes, // Include shared routes accessible to both admin and clinic
      // Admin-only routes
      {
        path: 'accountManagement',
        name: 'accountManagement',
        component: () => import('../views/Admin/AccountManagement.vue'),
      },
      {
        path: 'rescue',
        name: 'rescue',
        component: () => import('../views/Admin/Rescue.vue'),
      },
      {
        path: 'announcements',
        name: 'announcements',
        component: () => import('../views/Admin/Announcement.vue'),
      },
      {
        path: 'edit/vet-info', // Path: /user/edit/security
        name: 'admin-edit-vet-info',
        component: () => import('../views/Admin/EditVetInfo.vue'),
      },
      {
        path: 'edit/app-name', // Path: /user/edit/security
        name: 'admin-edit-app-name',
        component: () => import('../views/Admin/EditAppName.vue'),
      },
    ],
  },
];

// User routes
const userRoutes = [
  {
    path: '/user',
    component: mobileNav,
    meta: { requiresAuth: true, role: 'user' },
    children: [
      {
        path: 'dashboard', // Path: /user/dashboard
        name: 'userDashboard',
        component: () => import('../views/User/Dashboard.vue'),
      },
      {
        path: 'announcements', // Path: /user/announcements
        name: 'userannouncements',
        component: () => import('../views/User/News.vue'),
      },
      {
        path: 'gis-map', // Path: /user/GIS
        name: 'userGIS',
        component: () => import('../views/User/GIS.vue'),
      },
      {
        path: 'contact', // Path: /user/contacts
        name: 'usercontacts',
        component: () => import('../views/User/Contacts.vue'),
      },
      {
        path: 'profile', // Path: /user/profile
        name: 'userProfile',
        component: () => import('../views/User/Profile.vue'),
      },
     
    ],
  },
];

const userRoutesnoNAv = [
  {
    path: '/user',
    meta: { requiresAuth: true, role: 'user' },
    children: [
      {
        path: 'chat', // Path: /user/edit/adoptionHistory
        name: 'chat',
        component: () => import('../views/User/Chat.vue'),
      },
      {
        path: 'petGallery', // Path: /user/edit/adoptionHistory
        name: 'petGallery',
        component: () => import('../views/User/PetGallery.vue'),
      },
    ],
  },
];

// UserEdit routes
const userEditRoutes = [
   {
    path: '/user/edit',
    meta: { requiresAuth: true, role: 'user' },
    children: [
      {
        path: 'profile', // Path: /user/edit/profile
        name: 'edit-profile',
        component: () => import('../views/User/EditProfile.vue'),
      },
      {
        path: 'contacts', // Path: /user/edit/contacts
        name: 'edit-contacts',
        component: () => import('../views/User/EditContacts.vue'),
      },
      {
        path: 'address', // Path: /user/edit/address
        name: 'edit-address',
        component: () => import('../views/User/EditAddress.vue'),
      },
      {
        path: 'security', // Path: /user/edit/security
        name: 'security',
        component: () => import('../views/User/EditSecurity.vue'),
      },
      {
        path: 'adoptionHistory', // Path: /user/edit/adoptionHistory
        name: 'adoption-history',
        component: () => import('../views/User/EditAdoptionHistory.vue'),
      },
      {
        path: 'medicalHisotry', // Path: /user/edit/adoptionHistory
        name: 'medical-history',
        component: () => import('../views/User/EditMedicalHistory.vue'),
      },
      {
        path: 'petSavedHistory', // Path: /user/edit/adoptionHistory
        name: 'pet-saved-history',
        component: () => import('../views/User/EditPetHistory.vue'),
      },
     
      
    ],
  },
];


// Catch-all route for 404 NotFound
const errorRoute = [
  {
    path: '/:catchAll(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
  },
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: () => import('../views/Unauthorized.vue'),
  },
  
];

const routes = [...authRoutes, ...adminRoutes,...sharedAdminClinicRoutes, ...userRoutes,...userRoutesnoNAv, ...userEditRoutes, ...errorRoute ];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore();

  // Check for first-time visit to the startup page
  if (to.name === 'startup') {
    if (localStorage.getItem('hasVisitedStartup')) {
      // If the user has visited the startup page before, redirect to login (or another appropriate page)
      next({ name: 'login' });
      return; // Prevent further execution
    } else {
      // Mark that the user has now visited the startup page
      localStorage.setItem('hasVisitedStartup', 'true');
    }
  }

  const isAuthenticated = userStore.isAuthenticated; 
  const userRole = userStore.role;

  if (to.meta.requiresAuth && !isAuthenticated) {
    await userStore.logout();
    next({ name: 'login' }); // Redirect to login if not authenticated
  } else if (to.meta.requiresAuth && to.meta.role && to.meta.role !== userRole) {
    next({ name: 'Unauthorized' }); // Redirect to an unauthorized page if roles don't match
  } else {
    next(); // Proceed as normal if authenticated or no specific auth requirements
  }
});


export default router;