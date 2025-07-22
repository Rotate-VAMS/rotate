<template>
    <div class="min-h-screen flex items-center justify-center" style="background-image: url('/asset/images/login_bg_default.jpg'); background-size: cover; background-position: center;">
      <div class="backdrop-blur-lg bg-white/10 p-8 rounded-xl shadow-2xl w-full max-w-md text-white">
        <div class="text-center mb-6">
          <h2 class="text-3xl font-bold">Login</h2>
          <p class="text-sm text-blue-100">Welcome back! Enter your credentials</p>
        </div>
  
        <form @submit.prevent="submit">
          <div class="mb-4">
            <label class="block text-sm font-medium">Email</label>
            <input v-model="form.email" type="email" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="you@example.com" required />
            <div v-if="form.errors.email" class="text-red-300 text-sm mt-1">{{ form.errors.email }}</div>
          </div>
  
          <div class="mb-4">
            <label class="block text-sm font-medium">Password</label>
            <input v-model="form.password" type="password" class="w-full px-4 py-2 mt-1 rounded bg-white/20 text-white placeholder-white focus:outline-none" placeholder="••••••••" required />
            <div v-if="form.errors.password" class="text-red-300 text-sm mt-1">{{ form.errors.password }}</div>
          </div>
  
          <div class="text-right text-sm mb-4">
            <a href="#" class="text-blue-200 hover:underline">Forgot Password?</a>
          </div>
  
          <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition">
            Sign in
          </button>
        </form>
  
        <p class="mt-4 text-sm text-blue-100 text-center">
          Don't have an account? <a href="/register" class="underline hover:text-white">Register for free</a>
        </p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { useForm } from '@inertiajs/vue3'
  
  const form = useForm({
    email: '',
    password: '',
    remember: false,
  })
  
  function submit() {
    form.transform(data => ({
      ...data,
      remember: form.remember ? 'on' : '',
    })).post(route('login'), {
      onFinish: () => form.reset('password'),
    })
  }
  </script>