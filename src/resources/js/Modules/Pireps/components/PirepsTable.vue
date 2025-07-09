<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search events..."
        class="input border border-gray-300 rounded-md px-4 py-2 w-1/2"
      />
      <button class="btn-primary flex items-center gap-2 text-sm px-4 py-2 rounded-md bg-blue-100 text-blue-800">
        <FilterIcon class="w-4 h-4" /> Filters
      </button>
    </div>

    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors">S.No</th>
          <th class="px-6 py-3">Pilot</th>
          <th class="px-6 py-3">Route</th>
          <th class="px-6 py-3">Aircraft</th>
          <th class="px-6 py-3">Distance</th>
          <th class="px-6 py-3">Flight Time</th>
          <th class="px-6 py-3">Flight Type</th>
          <th class="px-6 py-3">Multiplier</th>
          <th class="px-6 py-3">Computed Flight Time</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="pirep in pireps"
          :key="pirep.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="font-semibold">{{ pirep.pilot }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="font-semibold">{{ pirep.route }}</div>
          </td>
          <td class="px-6 py-4">
            <div v-for="aircraft in pirep.aircraft" :key="aircraft" class="text-xs text-gray-400">{{ aircraft }}</div>
          </td>
          <td class="px-6 py-4 text-center font-medium">{{ pirep.distance }} NM</td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ pirep.flight_time }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ pirep.type }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ pirep.minimum_rank }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ pirep.status }}</div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { FilterIcon, BadgeIcon } from 'lucide-vue-next'
import RotateDataService from '@/rotate.js'

const pireps = ref([])
const search = ref('')
const sortKey = ref('')
const sortAsc = ref(true)

const statusText = {
  '1': 'Active',
  '0': 'Inactive'
}

const fetchPireps = async () => {
  try {
    const response = await RotateDataService('/pireps/jxFetchPireps', {})
    pireps.value = response.pireps || []
  } catch (e) {
    console.error(e)
  }
}

fetchPireps()
</script>