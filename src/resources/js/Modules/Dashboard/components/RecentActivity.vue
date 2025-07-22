<template>
    <div class="bg-white rounded-xl shadow-md p-4 glassmorphism">
      <div class="flex items-center justify-start mb-4">
        <LineChartIcon class="h-10 w-10 text-indigo-600 bg-gradient-to-r from-indigo-200 to-purple-200 rounded-lg p-2 mr-4" />
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Recent Pilot Activity</h2>
      </div>
      <p class="text-sm text-gray-500 mb-4">Latest PIREPs submitted by pilots</p>
  
      <div v-for="activity in activities" :key="activity.id" class="flex items-center justify-between mb-4 bg-white rounded-xl p-4">
        <div class="flex items-center space-x-3">
          <div class="bg-indigo-500 text-white rounded-full h-10 w-10 flex items-center justify-center">
            {{ activity.initials }}
          </div>
          <div>
            <p class="font-medium text-sm">{{ activity.name }}</p>
            <p class="text-xs text-gray-500">{{ activity.route }} · {{ activity.aircraft }}</p>
          </div>
        </div>
        <div class="text-xs text-right">
          <span :class="statusColor(activity.status)" class="px-2 py-1 rounded-full text-white">{{ activity.status }}</span>
          <p class="text-gray-400 text-xs mt-3">{{ activity.time_ago }}</p>
        </div>
      </div>
  
      <button class="text-sm text-indigo-600 font-semibold">View All PIREPs</button>
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