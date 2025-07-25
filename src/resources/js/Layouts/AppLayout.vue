<script setup>
import { ref, provide } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import RotateToast from '@/Components/RotateToast.vue';
import rotateDataService from '@/rotate.js'
import { UserIcon, SettingsIcon, PaletteIcon, LogOutIcon } from 'lucide-vue-next';

const props = defineProps({
    title: String,
});

const title = ref(props.title);
const page = usePage();
const user = page.props.auth?.user || {};
const showDropdown = ref(false);
const logo = ref('');
const logoDefault = ref(false);

const logout = () => {
    router.post(route('logout'), {
        preserveScroll: true,
        onSuccess: () => router.visit('/login'),
    });
};

const fetchLogo = async () => {
    const response = await rotateDataService('/settings/jxFetchLogo');
    if (response.hasErrors) {
        showToast(response.message, 'error');
        return;
    }
    logo.value = response.data;
    logoDefault.value = response.default;
}

fetchLogo();

provide('logo', logo);
provide('logoDefault', logoDefault);
// --- Toast logic ---
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
  if (toastTimeout) clearTimeout(toastTimeout)
}
// Provide showToast globally
provide('showToast', showToast)
// --- End Toast logic ---

// Usage: In any child component, use:
//   const showToast = inject('showToast')
//   showToast('Message', 'success'|'alert'|'error')
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-purple-100 text-gray-800">
    <Head :title="title">
      <meta name="csrf-token" :content="$page.props.csrf_token" />
    </Head>

    <!-- Toast Overlay -->
    <RotateToast
      v-if="toastActive"
      :message="toastMessage"
      :type="toastType"
      :active="toastActive"
      :overlay="true"
      @close="closeToast"
    />

    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between px-4 sm:px-6 py-3 sm:py-4 gap-2 sm:gap-0">
        <!-- Logo Section -->
        <div class="flex items-center space-x-2 sm:space-x-3 w-full sm:w-auto justify-center sm:justify-start">
          <img :src="logo" alt="Logo" class="h-8 w-auto sm:h-10" />
          <span class="text-base sm:text-lg font-semibold hidden sm:inline">Rotate</span>
        </div>

        <!-- Right Icons -->
        <div class="flex items-center space-x-4 sm:space-x-6 relative w-full sm:w-auto justify-center sm:justify-end mt-2 sm:mt-0">
          <!-- Profile Dropdown -->
          <div class="relative">
            <button @click="showDropdown = !showDropdown" class="flex items-center space-x-3 focus:outline-none bg-white rounded-2xl px-4 py-2 shadow-sm border border-gray-200">
              <!-- Avatar with status indicator -->
              <div class="relative">
                <div class="bg-gradient-to-br from-indigo-500 via-purple-500 to-indigo-500 text-white shadow-md rounded-xl h-10 w-10 flex items-center justify-center text-lg font-bold">
                  {{ user?.name.charAt(0) }}
                </div>
                <!-- Green status indicator -->
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
              </div>
              <div class="flex flex-col text-left">
                <span class="text-sm font-bold text-gray-900">{{ user?.name || 'User' }}</span>
                <span class="text-xs text-gray-500">{{ user?.rank || 'User' }} Rank</span>
              </div>
              <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div v-if="showDropdown"
                 class="absolute z-50 mt-2 py-3 ring-1 ring-black ring-opacity-5 flex flex-col gap-1 min-w-[14rem] sm:min-w-[16rem] bg-white rounded-2xl shadow-xl
                   w-[98vw] max-w-xs overflow-x-hidden
                   left-1/2 -translate-x-1/2 right-auto
                   sm:left-auto sm:translate-x-0 sm:right-0 sm:w-72 sm:max-w-sm">
              <div class="flex items-center gap-3 px-3 sm:px-5 pb-3">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl h-10 w-10 sm:h-12 sm:w-12 flex items-center justify-center text-lg sm:text-xl font-bold">
                  {{ user?.name.charAt(0) }}
                </div>
                <div class="flex flex-col gap-0.5 min-w-0">
                  <span class="text-sm sm:text-base font-semibold text-gray-900 truncate">{{ user?.name || 'User' }}</span>
                  <span class="text-xs sm:text-sm text-indigo-700 font-medium truncate">{{ user?.email || 'user@email.com' }}</span>
                  <span class="inline-flex items-center bg-gray-100 text-gray-700 text-[10px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 rounded-full mt-0.5 w-fit">{{ user?.rank || 'Cadet' }}</span>
                </div>
              </div>
              <div class="border-t border-gray-200 my-1"></div>
              <button class="flex items-center gap-2 px-3 sm:px-5 py-2 text-gray-900 hover:bg-gray-50 text-sm sm:text-base font-medium transition-colors w-full text-left"
                @click="() => router.visit('/pilots/manage-profile')">
                <UserIcon class="w-5 h-5" />
                <span class="truncate">Profile Settings</span>
              </button>
              <div class="border-t border-gray-200 my-1"></div>
              <form @submit.prevent="logout">
                <button type="submit" class="flex items-center gap-2 px-3 sm:px-5 py-2 text-red-600 hover:bg-red-50 text-sm sm:text-base font-semibold w-full transition-colors text-left">
                  <LogOutIcon class="w-5 h-5" />
                  <span class="truncate">Sign Out</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="py-4 px-2 sm:py-6 sm:px-6 max-w-7xl mx-auto w-full">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="text-center text-xs sm:text-sm text-gray-500 py-3 sm:py-4 border-t mt-8 bg-white px-2">
      © {{ new Date().getFullYear() }} Rotate. All rights reserved.
    </footer>
  </div>
</template>