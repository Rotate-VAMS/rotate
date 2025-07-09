<template>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">View Events</h1>
        <p class="text-sm text-gray-500">View and manage all events in your airline. Monitor performance, track progress, and oversee event operations.</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"><PlusIcon class="w-4 h-4" /> Create New Event</button>
      </div>
    </div>
</template>
<script setup>
import { PlusIcon } from 'lucide-vue-next';
</script> 