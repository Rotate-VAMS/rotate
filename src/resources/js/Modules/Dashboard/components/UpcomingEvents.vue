<template>
    <div class="bg-white rounded-xl shadow-md p-4 glassmorphism">
      <div class="flex items-center justify-start mb-4">
        <CalendarIcon class="h-10 w-10 text-green-600 bg-gradient-to-r from-green-200 to-green-300 rounded-lg p-2 mr-4" />
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Upcoming Events</h2>
      </div>
      <div v-for="event in events" :key="event.id" class="mb-3">
        <p class="text-sm font-medium">{{ event.name }}</p>
        <p class="text-xs text-gray-500">{{ event.date }}<br /><span class="text-indigo-600">{{ event.participants }} participants</span></p>
      </div>
    </div>
</template>
  
<script setup>
  import { CalendarIcon } from 'lucide-vue-next';
  const props = defineProps({
    events: Array
  });
</script>