<template>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">View Pilots</h1>
        <p class="text-sm text-gray-500">View and manage all pilots in your airline. Monitor performance, track progress, and oversee pilot operations.</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn-primary bg-indigo-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"><PlusIcon class="w-4 h-4" /> Add Pilot</button>
        <button class="btn-white bg-white text-gray-800 text-bold rounded-md px-4 py-2 flex items-center gap-2"><ImportIcon class="w-4 h-4" /> Import</button>
        <button class="btn-white bg-white text-gray-800 text-bold rounded-md px-4 py-2 flex items-center gap-2"><UploadIcon class="w-4 h-4" /> Export</button> 
      </div>
    </div>
</template>
<script setup>
import { ImportIcon, UploadIcon, PlusIcon } from 'lucide-vue-next';
</script> 