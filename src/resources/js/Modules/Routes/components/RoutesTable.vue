<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search routes..."
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
          <th class="px-6 py-3">Minimum Rank</th>
          <th class="px-6 py-3">Status</th>
          <th 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-3"
          >
            {{ customField.field_name }}
          </th>
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
          <td 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-4"
          >
            <div class="text-sm">
              {{ getCustomFieldValue(route, customField.field_key) }}
            </div>
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

// Props
const props = defineProps({
  customFields: {
    type: Array,
    default: () => []
  }
})

const routes = ref([])
const search = ref('')
const sortKey = ref('')
const sortAsc = ref(true)

const statusText = {
  '1': 'Active',
  '0': 'Inactive'
}

// Function to get custom field value
const getCustomFieldValue = (route, fieldKey) => {
  // Check if route has custom_fields array
  if (route.custom_fields && Array.isArray(route.custom_fields)) {
    // Find the custom field that matches the field_key
    const customField = route.custom_fields.find(field => {
      // Find the corresponding custom field definition to get the field_key
      const fieldDefinition = props.customFields.find(cf => cf.id === field.field_id)
      return fieldDefinition && fieldDefinition.field_key === fieldKey
    })
    
    if (customField) {
      return customField.value_display
    }
  }
  
  // Check if route has the field directly (fallback)
  if (route[fieldKey]) {
    return route[fieldKey]
  }
  
  return '-'
}

const fetchRoutes = async () => {
  try {
    const response = await RotateDataService('/routes/jxFetchRoutes', { scope: 'active' })
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