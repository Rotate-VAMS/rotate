<template>
    <div class="p-6 bg-white rounded-xl shadow-sm">
      <div class="flex flex-row justify-between items-center">
          <h2 class="text-lg font-semibold mb-4">Custom Field Configuration</h2>
          <button class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"><PlusIcon class="w-4 h-4" /> Create New Field</button>
      </div>
      <p>Manage your custom fields and their configurations.</p>
      <slot />
    </div>
</template>

<script setup>
import { PlusIcon } from 'lucide-vue-next';
</script>