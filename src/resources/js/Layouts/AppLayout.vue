<script setup>
import { ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    title: String,
});

const title = ref(props.title);
const page = usePage();
const user = page.props.auth?.user || {};
const showDropdown = ref(false);

const logout = () => {
    router.post(route('logout'), {
        preserveScroll: true,
        onSuccess: () => router.visit('/login'),
    });
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-purple-100 text-gray-800">
    <Head :title="title">
      <meta name="csrf-token" :content="$page.props.csrf_token" />
    </Head>

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
      <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
        <!-- Logo Section -->
        <div class="flex items-center space-x-3">
          <img src="/asset/images/logo-rotate-black.png" alt="Logo" class="h-10 w-auto" />
          <span class="text-lg font-semibold hidden sm:inline">Rotate</span>
        </div>

        <!-- Right Icons -->
        <div class="flex items-center space-x-6 relative">
          <!-- Bell Icon -->
          <button>
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405M19 13V6a2 2 0 00-2-2H7a2 2 0 00-2 2v7m14 0l-1.405 1.405M4 6h16" />
            </svg>
          </button>

          <!-- Profile Dropdown -->
          <div class="relative">
            <button @click="showDropdown = !showDropdown" class="flex items-center space-x-2 focus:outline-none">
              <svg class="w-8 h-8 rounded-full bg-gray-300 text-white" fill="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
              </svg>
              <span class="text-sm font-medium text-gray-700 hidden sm:inline">
                {{ user?.name || 'User' }} | {{ user?.rank || 'User' }}
              </span>
              <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div v-if="showDropdown"
                 class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg z-50 py-2 ring-1 ring-black ring-opacity-5">
              <form @submit.prevent="logout">
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Logout
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="py-6 px-6 max-w-7xl mx-auto">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="text-center text-sm text-gray-500 py-4 border-t mt-8 bg-white">
      © {{ new Date().getFullYear() }} Rotate. All rights reserved.
    </footer>
  </div>
</template>