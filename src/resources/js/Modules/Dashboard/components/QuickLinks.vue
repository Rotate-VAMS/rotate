<template>
    <div class="bg-white rounded-xl shadow-md p-4 glassmorphism">
      <div class="flex items-center justify-start mb-4">
        <LinkIcon class="h-10 w-10 text-red-600 bg-gradient-to-r from-red-300 to-yellow-200 rounded-lg p-2 mr-4" />
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Quick Links</h2>
      </div>
      <ul class="space-y-3">
        <li v-for="link in links" :key="link.label" class="flex items-center space-x-3 text-sm">
          <component :is="link.icon" v-if="link.visible" class="w-5 h-5 text-gray-600" />
          <a :href="link.url" v-if="link.visible" class="text-gray-700 hover:text-indigo-600">{{ link.label }}</a>
        </li>
      </ul>
    </div>
</template>
  
<script setup>
  import { LinkIcon, RouteIcon, MapPinIcon, UsersIcon, SettingsIcon } from 'lucide-vue-next';
  import { usePage } from '@inertiajs/vue3';

  const props = defineProps({});
  const user = usePage().props.auth.user;

  const links = [
    { label: 'Browse Routes', url: '/routes', icon: RouteIcon, visible: user.permissions.includes('view-route') },
    // { label: 'Airport Charts', url: '/charts', icon: MapPinIcon, visible: true },
    { label: 'Pilot Management', url: '/users', icon: UsersIcon, visible: user.permissions.includes('view-user') },
    { label: 'Settings', url: '/settings', icon: SettingsIcon, visible: user.permissions.includes('access-settings') },
  ];
</script>