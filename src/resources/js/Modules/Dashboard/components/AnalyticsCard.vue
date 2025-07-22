<template>
    <div class="rounded-xl p-5 shadow-md w-full glassmorphism">
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
    type: String
  });
  
  const iconMap = {
    pilots: UserIcon,
    routes: PlaneIcon,
    flights: ActivityIcon,
    events: CalendarIcon,
  };
  
  const bgClassMap = {
    pilots: 'bg-indigo-500',
    routes: 'bg-green-500',
    flights: 'bg-purple-500',
    events: 'bg-orange-500',
  };
  
  const icon = iconMap[props.type] || UserIcon;
  const bgClass = bgClassMap[props.type] || 'bg-gray-500';
</script>