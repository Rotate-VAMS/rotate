<template>
    <div class="min-h-screen flex items-center justify-center" style="background-image: url('/asset/images/login_bg_default.jpg'); background-size: cover; background-position: center;">
      <div class="backdrop-blur-lg bg-white/10 p-8 rounded-xl shadow-2xl w-full max-w-md text-white">
        <div class="text-center mb-6">
          <h2 class="text-3xl font-bold">Register</h2>
          <p class="text-sm text-blue-100">Welcome! Enter your credentials</p>
        </div>
  
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium">Name</label>
            <input v-model="form.name" type="text" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="Your full name" required />
            <div v-if="errors.name" class="text-red-300 text-sm mt-1">{{ errors.name }}</div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium">Email</label>
            <input v-model="form.email" type="email" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="you@example.com" required />
            <div v-if="errors.email" class="text-red-300 text-sm mt-1">{{ errors.email }}</div>
          </div>
  
          <div class="mb-4">
            <label class="block text-sm font-medium">Username</label>
            <input v-model="form.username" type="text" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="Username" required />
            <div v-if="errors.username" class="text-red-300 text-sm mt-1">{{ errors.username }}</div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium">Password</label>
            <input v-model="form.password" type="password" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="••••••••" required />
            <div v-if="errors.password" class="text-red-300 text-sm mt-1">{{ errors.password }}</div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="••••••••" required />
            <div v-if="errors.password_confirmation" class="text-red-300 text-sm mt-1">{{ errors.password_confirmation }}</div>
          </div>
  
          <div class="text-right text-sm mb-4">
            <a href="/login" class="text-blue-200 hover:underline">Already have an account? Login</a>
          </div>
  
          <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition">
            Register
          </button>
        </form>
  
        <p class="mt-4 text-sm text-blue-100 text-center">
          Already have an account? <a href="/login" class="underline hover:text-white">Login</a>
        </p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { reactive } from 'vue'
  import { router } from '@inertiajs/vue3'
  import { inject } from 'vue'

  const showToast = inject('showToast');
  const props = defineProps({
    errors: Object,
  })
  
  const form = reactive({
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
    processing: false,
  })
  
  function submit() {
    form.processing = true
    router.post('/register', form, {
      onFinish: () => (form.processing = false),
      onError: (errors) => {
        showToast(errors.message || 'Error occurred', 'error')
      }
    })
  }
  </script>