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
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors" @click="sortBy('name')">
            <div class="flex items-center gap-1">
              Pilot
              <span v-if="sortKey === 'name'" class="text-blue-600">
                {{ sortAsc ? '↑' : '↓' }}
              </span>
            </div>
          </th>
          <th class="px-6 py-3">Rank</th>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors" @click="sortBy('flights')">
            <div class="flex items-center gap-1">
              Flights
              <span v-if="sortKey === 'flights'" class="text-blue-600">
                {{ sortAsc ? '↑' : '↓' }}
              </span>
            </div>
          </th>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors" @click="sortBy('hours')">
            <div class="flex items-center gap-1">
              Hours
              <span v-if="sortKey === 'hours'" class="text-blue-600">
                {{ sortAsc ? '↑' : '↓' }}
              </span>
            </div>
          </th>
          <th class="px-6 py-3">Recent Flight Logs</th>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors" @click="sortBy('status')">
            <div class="flex items-center gap-1">
              Status
              <span v-if="sortKey === 'status'" class="text-blue-600">
                {{ sortAsc ? '↑' : '↓' }}
              </span>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="pilot in sortedPilots"
          :key="pilot.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="font-semibold">{{ pilot.name }}</div>
            <div class="text-xs text-gray-400">{{ pilot.callsign }}</div>
          </td>
          <td class="px-6 py-4">
            <span :class="pilot.rank_color" class="inline-flex items-center text-sm font-medium px-2 py-1 rounded-full">
              <BadgeIcon class="w-4 h-4 mr-1" /> {{ pilot.rank }}
            </span>
          </td>
          <td class="px-6 py-4 text-center font-medium">{{ pilot.flights }}</td>
          <td class="px-6 py-4 text-center font-medium">{{ pilot.hours }}</td>
          <td class="px-6 py-4">
            <div class="flex gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
              <span
                v-for="flight in pilot.last_flights"
                :key="flight"
                class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium"
              >
                {{ flight }}
              </span>
            </div>
          </td>
          <td class="px-6 py-4">
            <span
              :class="pilot.status === '1' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'"
              class="text-xs font-semibold px-3 py-1 rounded-full"
            >
              {{ statusText[pilot.status] }}
            </span>
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

const pilots = ref([])
const search = ref('')
const sortKey = ref('')
const sortAsc = ref(true)

const statusText = {
  '1': 'Active',
  '0': 'Inactive'
}

const fetchPilots = async () => {
  try {
    const response = await RotateDataService('/pilots/jxFetchPilots', {})
    pilots.value = response || []
  } catch (e) {
    console.error(e)
  }
}

const filteredPilots = computed(() => {
  if (!search.value) return pilots.value
  const searchTerm = search.value.toLowerCase()
  return pilots.value.filter(p =>
    [p.name, p.callsign, p.email].some(field =>
      field?.toLowerCase().includes(searchTerm)
    )
  )
})

const sortedPilots = computed(() => {
  let result = [...filteredPilots.value]
  
  if (!sortKey.value) return result
  
  return result.sort((a, b) => {
    let valA = a[sortKey.value]
    let valB = b[sortKey.value]
    
    // Handle null/undefined values
    if (valA == null && valB == null) return 0
    if (valA == null) return 1
    if (valB == null) return -1
    
    // Convert to strings for comparison
    valA = valA.toString().toLowerCase()
    valB = valB.toString().toLowerCase()
    
    // Handle numeric values
    if (!isNaN(valA) && !isNaN(valB)) {
      return sortAsc.value 
        ? parseFloat(valA) - parseFloat(valB)
        : parseFloat(valB) - parseFloat(valA)
    }
    
    // Handle string values
    if (sortAsc.value) {
      return valA.localeCompare(valB)
    } else {
      return valB.localeCompare(valA)
    }
  })
})

function sortBy(key) {
  if (sortKey.value === key) {
    sortAsc.value = !sortAsc.value
  } else {
    sortKey.value = key
    sortAsc.value = true
  }
}

fetchPilots()
</script>