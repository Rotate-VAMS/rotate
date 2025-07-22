<template>
  <div class="bg-white rounded-xl shadow-md p-3 sm:p-4 glassmorphism">
    <div class="flex flex-row items-start xs:items-center justify-start mb-3 sm:mb-4 gap-2 xs:gap-4">
      <LineChartIcon class="h-8 w-8 sm:h-10 sm:w-10 text-indigo-600 bg-gradient-to-r from-indigo-200 to-purple-200 rounded-lg p-2" />
      <h2 class="text-lg sm:text-2xl font-semibold text-gray-800 mb-0">Recent Pilot Activity</h2>
    </div>
    <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">Latest PIREPs submitted by pilots</p>

    <div v-for="activity in activities" :key="activity.id" class="flex flex-row xs:flex-row xs:items-center justify-between mb-3 sm:mb-4 bg-white rounded-xl p-3 sm:p-4 gap-2 xs:gap-0">
      <div class="flex items-center space-x-2 sm:space-x-3">
        <div class="bg-indigo-500 text-white rounded-full h-8 w-8 sm:h-10 sm:w-10 flex items-center justify-center text-base sm:text-lg">
          {{ activity.initials }}
        </div>
        <div>
          <p class="font-medium text-xs sm:text-sm">{{ activity.name }}</p>
          <p class="text-xs text-gray-500">{{ activity.route }} · {{ activity.aircraft }}</p>
        </div>
      </div>
      <div class="text-xs text-right">
        <span :class="statusColor(activity.status)" class="px-2 py-1 rounded-full text-white">{{ activity.status }}</span>
        <p class="text-gray-400 text-xs mt-2 sm:mt-3">{{ activity.time_ago }}</p>
      </div>
    </div>

    <button class="w-full sm:w-auto text-xs sm:text-sm text-indigo-600 font-semibold py-2 mt-2 sm:mt-0">View All PIREPs</button>
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
</script>