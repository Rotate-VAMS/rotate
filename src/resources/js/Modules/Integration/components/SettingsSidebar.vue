<template>
  <div class="md:w-1/5 lg:w-1/6 bg-white rounded-xl shadow-sm p-4 h-fit">
    <ul class="space-y-2 text-sm font-medium">
      <li v-for="item in menu" :key="item.key">
        <button
          @click="$emit('navigate', item.key)"
          :class="[
            'flex items-center text-left w-full px-4 py-2 rounded-lg transition-colors',
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
import { Settings, Hash, Shield, Users, ShieldCheck, PlaneIcon, PlaneTakeoff, UsersIcon, ImageIcon, Trophy } from 'lucide-vue-next'
const props = defineProps({
  active: String
})
const menu = [
  { key: 'roles', label: 'Roles & Permissions', icon: UsersIcon },
  { key: 'ranks', label: 'Ranks', icon: ShieldCheck },
  { key: 'fleet', label: 'Fleet', icon: PlaneIcon },
  { key: 'flightTypes', label: 'Flight Types', icon: PlaneTakeoff },
  { key: 'customFields', label: 'Custom Fields', icon: Hash },
  { key: 'leaderboard', label: 'Leaderboard', icon: Trophy },
  { key: 'logo', label: 'Logo', icon: ImageIcon },
  { key: 'discord', label: 'Discord Integration', icon: Settings },
]
</script>