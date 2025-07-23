<template>
    <div class="flex flex-wrap gap-4 mb-6">
      <template v-for="button in buttons" :key="button.label">
        <button
          v-if="button.visible"
          @click="navigate(button.route)"
          class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm text-bold shadow-sm"
          :class="button.isPrimary ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white' : 'bg-white border text-gray-800'"
        >
          <component :is="button.icon" class="w-4 h-4" />
          {{ button.label }}
        </button>
      </template>
    </div>
  </template>
  
  <script setup>
  import { usePage } from '@inertiajs/vue3';
  import { List, BookOpen, Map, CalendarPlus, CalendarDays, Route, MapPin, Users, PlaneIcon } from 'lucide-vue-next';
  
  const page = usePage();
  const user = page.props.auth.user;
  
  const buttons = [
    { label: 'PIREPs', icon: PlaneIcon, route: '/pireps', visible: user.permissions.includes('view-pirep'), isPrimary: true },
    { label: 'Pilots', icon: Users, route: '/pilots', visible: user.permissions.includes('view-user'), isPrimary: false },
    { label: 'Events', icon: List, route: '/events', visible: user.permissions.includes('view-event'), isPrimary: false },
    { label: 'Routes', icon: BookOpen, route: '/routes', visible: user.permissions.includes('view-route'), isPrimary: false },
    { label: 'Route Generator', icon: Route, route: '/routes/generator', visible: true, isPrimary: false },
    { label: 'Airport Charts', icon: MapPin, route: '/charts', visible: true, isPrimary: false },
  ];
  
  const navigate = (route) => {
    window.location.href = route;
  };
  </script>