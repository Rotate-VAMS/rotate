<template>
    <div class="flex flex-wrap gap-4 mb-6">
      <template v-for="button in filteredButtons" :key="button.label">
        <button
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
  import { Plus, List, BookOpen, Map, CalendarPlus, CalendarDays, Route, MapPin, Users } from 'lucide-vue-next';
  
  const page = usePage();
  const user = page.props.auth.user;
  const props = defineProps({
    role: String,
  });
  
  const resolvedRole = props.role || user?.role || 'user';
  
  const buttons = [
    { label: 'PIREPs', icon: Plus, route: '/pireps', visibleTo: ['All'], isPrimary: true },
    { label: 'Pilots', icon: Users, route: '/pilots', visibleTo: ['Admin'], isPrimary: false },
    { label: 'Events', icon: List, route: '/events', visibleTo: ['Admin'], isPrimary: false },
    { label: 'Routes', icon: BookOpen, route: '/routes', visibleTo: ['All'], isPrimary: false },
    { label: 'Route Generator', icon: Route, route: '/routes/generator', visibleTo: ['All'], isPrimary: false },
    { label: 'Airport Charts', icon: MapPin, route: '/charts', visibleTo: ['All'], isPrimary: false },
  ];
  
  const filteredButtons = buttons.filter(button =>
    button.visibleTo.includes('All') || resolvedRole.toLowerCase() === 'admin'
  );
  
  const navigate = (route) => {
    window.location.href = route;
  };
  </script>