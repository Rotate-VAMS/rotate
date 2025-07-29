<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Background with gradient and aviation elements -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-400 via-blue-500 to-blue-700"></div>
    
    <!-- Aviation-themed background elements -->
    <div class="absolute inset-0">
      <!-- Airplane outlines -->
      <div class="absolute left-10 top-20 w-16 h-8 border-2 border-white/20 rounded-full transform rotate-12"></div>
      <div class="absolute left-8 top-32 w-12 h-6 border-2 border-white/20 rounded-full transform -rotate-6"></div>
      
      <!-- Scattered dots -->
      <div class="absolute inset-0">
        <div class="absolute top-1/4 left-1/3 w-1 h-1 bg-white/30 rounded-full"></div>
        <div class="absolute top-1/3 right-1/4 w-1 h-1 bg-white/30 rounded-full"></div>
        <div class="absolute bottom-1/3 left-1/5 w-1 h-1 bg-white/30 rounded-full"></div>
        <div class="absolute bottom-1/4 right-1/3 w-1 h-1 bg-white/30 rounded-full"></div>
        <div class="absolute top-1/2 left-1/2 w-1 h-1 bg-white/30 rounded-full"></div>
      </div>
    </div>

    <!-- Main login card -->
    <div class="relative z-10 bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-4">
      <!-- Logo and header -->
      <div class="text-center mb-8">
        <div class="relative inline-block mb-4" v-if="logoDefault">
          <div class="shadow-2xl w-16 h-16 bg-gradient-to-br from-indigo-400 via-purple-600 to-indigo-400 border border-indigo-600 rounded-xl flex items-center justify-center relative">
            <span class="text-white text-2xl font-bold">{{ tenant.charAt(0).toUpperCase() }}</span>
            <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
              <PlaneIcon class="w-3 h-3 text-white" />
            </div>
          </div>
        </div>
        <div class="relative inline-block mb-4" v-else>
          <div class="w-20 h-20 bg-transparent border-none rounded-xl flex items-center justify-center relative">
            <img :src="logo" alt="Logo" class="w-full h-full object-contain">
          </div>
        </div>
        
        <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 to-indigo-600 bg-clip-text text-transparent mb-2 relative">Welcome to {{ tenant }}</h1>
        <p class="text-gray-600 text-lg mb-1">VA Management System</p>
        <p class="text-gray-500 text-sm">Sign in to access your flight operations</p>
      </div>

      <!-- Login form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Email field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
              </svg>
            </div>
            <input 
              v-model="form.email" 
              type="email" 
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
              placeholder="pilot@rotate.com" 
              required 
              @blur="validateEmail"
            />
          </div>
          <!-- <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div> -->
        </div>

        <!-- Password field -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <input 
              v-model="form.password" 
              type="password" 
              class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
              placeholder="Enter your password" 
              required 
              @blur="validatePassword"
            />
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
          </div>
          <!-- <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div> -->
        </div>

        <!-- Sign in button -->
        <button 
          type="submit" 
          :disabled="form.processing" 
          class="w-full bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-600 hover:from-indigo-700 hover:via-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center space-x-2"
        >
          <span>Sign In to Flight Deck</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </form>

      <!-- Footer status -->
      <div class="mt-8 text-center">
        <p class="text-xs text-gray-500">
          Secure flight operations management 
        </p>
      </div>
    </div>
  </div>
  <RotateToast
      v-if="toastActive"
      :message="toastMessage"
      :type="toastType"
      :active="toastActive"
      :overlay="true"
      @close="closeToast"
    />
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import RotateToast from '@/Components/RotateToast.vue'
import { PlaneIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'

// Get flash messages from Inertia props
const props = defineProps({
  flash: {
    type: Object,
    default: () => ({})
  },
  tenant: {
    type: String,
    default: 'Rotate'
  }
})

// Fetch logo
const logo = ref(null)
const logoDefault = ref(false);

onMounted(async () => {
    const response = await rotateDataService('/settings/jxFetchLogo');
    if (response.hasErrors) {
        showToast(response.message, 'error');
        return;
    }
    logo.value = response.data;
    logoDefault.value = response.default;
})


const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const toastActive = ref(false)
const toastMessage = ref('')
const toastType = ref('success')
let toastTimeout = null

function showToast(message, type = 'success') {
  toastMessage.value = message
  toastType.value = type
  toastActive.value = true
  if (toastTimeout) clearTimeout(toastTimeout)
  toastTimeout = setTimeout(() => {
    toastActive.value = false
  }, 5000)
}

function closeToast() {
  toastActive.value = false
  if (toastTimeout) {
    clearTimeout(toastTimeout)
    toastTimeout = null
  }
}

// Check for session flash messages on component mount
onMounted(() => {
  // Check for error flash message from middleware
  if (props.flash && props.flash.error) {
    showToast(props.flash.error, 'error')
  }
  
  // Check for success flash message
  if (props.flash && props.flash.success) {
    showToast(props.flash.success, 'success')
  }
  
  // Check for warning flash message
  if (props.flash && props.flash.warning) {
    showToast(props.flash.warning, 'alert')
  }
  
  // Check for info flash message
  if (props.flash && props.flash.info) {
    showToast(props.flash.info, 'alert')
  }
})

function submit() {
  // Clear any previous errors
  form.clearErrors()
  
  // Basic client-side validation
  if (!form.email) {
    showToast('Email address is required', 'error')
    return
  }
  
  if (!form.password) {
    showToast('Password is required', 'error')
    return
  }
  
  // Email format validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email)) {
    showToast('Please enter a valid email address', 'error')
    return
  }

  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onStart: () => {
      showToast('Signing in...', 'alert')
    },
    onFinish: () => {
      form.reset('password')
    },
    onSuccess: () => {
      showToast('Welcome back! Redirecting to dashboard...', 'success')
    },
    onError: (errors) => {
      // Handle specific error types
      if (errors.email) {
        showToast(errors.email, 'error')
      } else if (errors.password) {
        showToast(errors.password, 'error')
      } else if (errors.message) {
        showToast(errors.message, 'error')
      } else if (errors.auth) {
        showToast('Invalid email or password. Please try again.', 'error')
      } else {
        // Handle network or server errors
        showToast('An error occurred. Please check your connection and try again.', 'error')
      }
    },
    onCancel: () => {
      showToast('Login cancelled', 'alert')
    }
  })
}

// Handle form field validation on blur
function validateEmail() {
  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    showToast('Please enter a valid email address', 'error')
  }
}

function validatePassword() {
  if (form.password && form.password.length < 6) {
    showToast('Password must be at least 6 characters long', 'error')
  }
}
</script>