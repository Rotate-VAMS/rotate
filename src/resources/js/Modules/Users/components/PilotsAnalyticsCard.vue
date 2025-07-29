<template>
    <div class="rounded-xl p-4 flex items-center justify-between glassmorphism">
      <div class="flex-1 min-w-0 mr-3">
        <p class="text-sm text-gray-500 break-words whitespace-normal leading-tight">{{ title }}</p>
        <h2 class="text-lg sm:text-xl lg:text-2xl xl:text-3xl font-semibold text-gray-800 break-words whitespace-normal leading-tight">{{ value }} {{ unit }}</h2>
      </div>
      <div class="text-2xl sm:text-3xl text-indigo-500 rounded-md p-2 bg-indigo-100 flex-shrink-0">
        <component :is="icon" />
      </div>
    </div>
  </template>
  
<script setup>
import { Users } from 'lucide-vue-next';

  const props = defineProps({
    title: String,
    value: [String, Number],
    icon: {
        type: Object,
        default: Users,
    },
    unit: String,
  });
</script>