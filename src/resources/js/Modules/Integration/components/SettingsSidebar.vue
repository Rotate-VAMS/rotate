<template>
  <div class="md:w-1/5 lg:w-1/6 bg-white rounded-xl shadow-sm p-4">
    <ul class="space-y-2 text-sm font-medium">
      <li v-for="item in menu" :key="item.key">
        <button
          @click="$emit('navigate', item.key)"
          :class="[
            'flex items-center w-full px-4 py-2 rounded-lg transition-colors',
            active === item.key
              ? 'bg-gray-100 text-blue-600 font-semibold'
              : 'hover:bg-gray-50 text-gray-700'
          ]"
        >
          <component :is="item.icon" class="w-4 h-4 mr-2" />
          {{ item.label }}
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { Settings, Hash, Shield } from 'lucide-vue-next'
const props = defineProps({
  active: String
})
const menu = [
  { key: 'customFields', label: 'Custom Fields', icon: Hash },
  { key: 'discord', label: 'Discord Integration', icon: Settings },
  { key: 'access', label: 'Access Control', icon: Shield },
]
</script>