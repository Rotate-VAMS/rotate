<template>
    <div class="bg-white rounded-xl shadow-md p-4 glassmorphism">
      <div class="flex items-center justify-start mb-4">
        <CalendarIcon class="h-10 w-10 text-green-600 bg-gradient-to-r from-green-200 to-green-300 rounded-lg p-2 mr-4" />
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Upcoming Events</h2>
      </div>
      <div v-for="event in events" :key="event.id" class="mb-3 bg-gradient-to-r from-green-100 to-green-200 rounded-lg p-2">
        <div class="flex flex-row justify-between">
          <PlaneIcon class="h-8 w-8 text-green-600 bg-gradient-to-r from-green-200 to-green-300 border border-green-500 rounded-lg mt-2 p-2 ml-2" />
          <div class="flex flex-col">
            <p class="text-sm font-bold mb-1">{{ event.event_name }}</p>
            <p class="text-xs text-gray-500 mb-1">{{ formatDateTime(event.event_date_time) }} </p>
          </div>
          <div class="flex flex-col">
            <p class="text-xs font-semibold mt-3">
              <span class="rounded-full bg-gray-100 border border-gray-300 text-gray-600 px-2 py-1">{{ event.participants }} participants</span>
            </p>
          </div>
        </div>
      </div>
    </div>
</template>
  
<script setup>
  import { CalendarIcon, PlaneIcon } from 'lucide-vue-next';
  const props = defineProps({
    events: Array
  });

  const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime*1000).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>