<template>
  <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 glassmorphism">
    <div class="flex flex-row items-start xs:items-center justify-start mb-3 sm:mb-4 gap-2 xs:gap-4">
      <LineChartIcon class="h-8 w-8 sm:h-10 sm:w-10 text-indigo-600 bg-gradient-to-r from-indigo-200 to-purple-200 rounded-lg p-2" />
      <h2 class="text-lg sm:text-2xl font-semibold text-gray-800 mb-0">Recent Pilot Activity</h2>
    </div>
    <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">Latest PIREPs submitted by pilots</p>

    <div v-for="activity in activities" :key="activity.id" class="flex flex-row xs:flex-row xs:items-center justify-between mb-3 sm:mb-4 bg-white rounded-xl p-3 sm:p-4 gap-2 xs:gap-0">
      <div class="flex items-center space-x-2 sm:space-x-3">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-full h-8 w-8 sm:h-10 sm:w-10 flex items-center justify-center text-base sm:text-lg">
          {{ activity.pilot_name.charAt(0) }}
        </div>
        <div>
          <p class="font-bold text-xs sm:text-sm">{{ activity.pilot_name }}</p>
          <p class="text-sm text-gray-500"
            :class="{ 'shadow-md rounded-full text-xs text-gray-800 font-semibold bg-gradient-to-br from-yellow-200 via-yellow-400 to-yellow-200 border border-yellow-400 px-2 py-1 mt-1': !activity.flight_number }"
          >
            {{ activity.origin }} → {{ activity.destination }} · {{ activity.flight_number ? activity.flight_number : activity.event_name }}
          </p>
        </div>
      </div>
      <div class="text-xs text-right">
        <span class="px-2 py-1 rounded-full font-bold text-green-700 bg-gradient-to-r from-green-100 to-green-200">{{ activity.distance }} NM</span>
        <p class="text-gray-400 text-xs mt-2 sm:mt-3">{{ activity.time_ago }}</p>
      </div>
    </div>

    <button class="w-full sm:w-auto text-xs sm:text-sm text-indigo-600 font-semibold py-2 mt-2 sm:mt-0" @click="goToPireps">View All PIREPs</button>
  </div>
</template>
  
<script setup>
  import { LineChartIcon } from 'lucide-vue-next';
  const props = defineProps({
    activities: Array
  });
  
  const statusColor = (status) => {
    return status === 'completed' ? 'bg-green-500' : 'bg-yellow-500';
  };

  const goToPireps = () => {
    window.location.href = '/pireps';
  }
</script>