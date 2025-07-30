<template>
    <div v-if="visible" class="rounded-xl p-5 shadow-md w-full glassmorphism">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">{{ title }}</p>
          <p class="text-2xl font-bold text-gray-800">{{ value }}</p>
        </div>
        <div class="p-2 rounded-full" :class="bgClass">
          <component :is="icon" class="w-6 h-6 text-white" />
        </div>
      </div>
    </div>
</template>
  
<script setup>
  import { defineProps } from 'vue';
  import { UserIcon, PlaneIcon, CalendarIcon, ActivityIcon } from 'lucide-vue-next';
  
  const props = defineProps({
    title: String,
    value: [Number, String],
    type: String,
    caption: String,
    visible: Boolean
  });
  
  const iconMap = {
    flights: UserIcon,
    routes: PlaneIcon,
    ranks: ActivityIcon,
    events: CalendarIcon,
  };
  
  const bgClassMap = {
    flights: 'bg-gradient-to-r from-indigo-600 via-blue-500 to-blue-500 shadow-lg border border-indigo-400',
    routes: 'bg-gradient-to-r from-green-600 via-lime-500 to-lime-500 shadow-lg border border-green-400',
    ranks: 'bg-gradient-to-r from-purple-600 via-pink-500 to-pink-500 shadow-lg border border-purple-400',
    events: 'bg-gradient-to-r from-orange-600 via-yellow-500 to-yellow-500 shadow-lg border border-yellow-400',
  };
  
  const icon = iconMap[props.type] || UserIcon;
  const bgClass = bgClassMap[props.type] || 'bg-gray-500';
</script>