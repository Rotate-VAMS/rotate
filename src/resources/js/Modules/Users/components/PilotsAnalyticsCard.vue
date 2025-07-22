<template>
    <div class="rounded-xl p-4 flex items-center justify-between glassmorphism">
      <div>
        <p class="text-sm text-gray-500">{{ title }}</p>
        <h2 class="text-3xl font-semibold text-gray-800">{{ value }} {{ unit }}</h2>
      </div>
      <div class="text-3xl text-indigo-500 rounded-md p-2 bg-indigo-100">
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