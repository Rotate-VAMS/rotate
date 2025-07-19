<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search routes..."
        class="input border border-gray-300 rounded-md px-4 py-2 w-1/2"
      />
    </div>

    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors">Flight Number</th>
          <th class="px-6 py-3">Route</th>
          <th class="px-6 py-3">Aircraft</th>
          <th class="px-6 py-3">Distance</th>
          <th class="px-6 py-3">Flight Time</th>
          <th class="px-6 py-3">Minimum Rank</th>
          <th class="px-6 py-3">Status</th>
          <th class="px-6 py-3">Actions</th>
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
            <div class="text-s text-gray-400">{{ route.name_route }}</div>
          </td>
          <td class="px-6 py-4">
            <div v-for="(aircraft, key) in route.fleet_names" :key="key" class="text-xs text-gray-400">{{ aircraft }} - {{ key }}</div>
          </td>
          <td class="px-6 py-4 text-center font-medium">{{ route.distance }} NM</td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.flight_time }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.minimum_rank }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ route.status ? 'Active' : 'Inactive' }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2">
              <button 
                @click="editRoute(route)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Route"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                @click="deleteRoute(route)"
                class="text-red-600 hover:text-red-800 p-1 rounded"
                title="Delete Route"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { FilterIcon, BadgeIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
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
    routes.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

// Action handlers
const editRoute = (route) => {
  // Emit event to parent component to open edit drawer
  window.dispatchEvent(new CustomEvent('edit-route', { detail: route }))
}

const deleteRoute = async (route) => {
  if (confirm(`Delete route "${route.flight_number}"?`)) {
    try {
      const response = await RotateDataService('/routes/jxDeleteRoutes', { id: route.id })
      if (!response.hasErrors) {
        alert(response.message || 'Route deleted successfully')
        fetchRoutes()
      } else {
        alert(response.message || 'Error occurred')
      }
    } catch (e) {
      console.error(e)
      alert('Error occurred while deleting route')
    }
  }
}

// Event listeners
const handleRoutesUpdated = () => {
  fetchRoutes()
}

const handleEditRoute = (event) => {
  // This will be handled by the parent component
  window.dispatchEvent(new CustomEvent('open-edit-drawer', { detail: event.detail }))
}

onMounted(() => {
  fetchRoutes()
  window.addEventListener('routes-updated', handleRoutesUpdated)
  window.addEventListener('edit-route', handleEditRoute)
})

onUnmounted(() => {
  window.removeEventListener('routes-updated', handleRoutesUpdated)
  window.removeEventListener('edit-route', handleEditRoute)
})
</script>