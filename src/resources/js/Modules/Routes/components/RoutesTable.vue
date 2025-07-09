<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search pilots..."
        class="input border border-gray-300 rounded-md px-4 py-2 w-1/2"
      />
      <button class="btn-primary flex items-center gap-2 text-sm px-4 py-2 rounded-md bg-blue-100 text-blue-800">
        <FilterIcon class="w-4 h-4" /> Filters
      </button>
    </div>

    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors">Flight Number</th>
          <th class="px-6 py-3">Route</th>
          <th class="px-6 py-3">Aircraft</th>
          <th class="px-6 py-3">Distance</th>
          <th class="px-6 py-3">Flight Time</th>
          <th class="px-6 py-3">Type</th>
          <th class="px-6 py-3">Minimum Rank</th>
          <th class="px-6 py-3">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="route in routes"
          :key="route.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="font-semibold">{{ route.flight_number }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="font-semibold">{{ route.route }}</div>
          </td>
          <td class="px-6 py-4">
            <div v-for="aircraft in route.aircraft" :key="aircraft" class="text-xs text-gray-400">{{ aircraft }}</div>
          </td>
          <td class="px-6 py-4 text-center font-medium">{{ route.distance }} NM</td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.flight_time }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.type }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.minimum_rank }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.status }}</div>
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

const routes = ref([])
const search = ref('')
const sortKey = ref('')
const sortAsc = ref(true)

const statusText = {
  '1': 'Active',
  '0': 'Inactive'
}

const fetchRoutes = async () => {
  try {
    const response = await RotateDataService('/routes/jxFetchRoutes', {})
    routes.value = response.routes || []
  } catch (e) {
    console.error(e)
  }
}

fetchRoutes()
</script>