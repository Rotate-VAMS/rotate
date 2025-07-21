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
          <th class="px-6 py-3">Pilot</th>
          <th class="px-6 py-3">Rank</th>
          <th class="px-6 py-3">Flights</th>
          <th class="px-6 py-3">Hours</th>
          <th class="px-6 py-3">Recent Flight Logs</th>
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
          v-for="pilot in pilots"
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
          <td class="px-6 py-4 text-center font-medium">{{ pilot.flying_hours }}</td>
          <td class="px-6 py-4">
            <div class="flex gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
              <span
                v-for="flight in pilot.recent_flights"
                :key="flight"
                class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium"
              >
                {{ flight.origin }} - {{ flight.destination }}
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
          <td 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-4"
          >
            <div class="text-sm">
              {{ getCustomFieldValue(pilot, customField.field_key) }}
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2">
              <button 
                @click="editPilot(pilot)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Pilot"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                @click="deletePilot(pilot)"
                class="text-red-600 hover:text-red-800 p-1 rounded"
                title="Delete Pilot"
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { FilterIcon, BadgeIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'

// Props
const props = defineProps({
  customFields: {
    type: Array,
    default: () => []
  }
})

const pilots = ref([])
const search = ref('')

const statusText = {
  '1': 'Active',
  '0': 'Inactive'
}

// Function to get custom field value
const getCustomFieldValue = (pilot, fieldKey) => {
  // Check if pilot has custom_fields array
  if (pilot.custom_fields && Array.isArray(pilot.custom_fields)) {
    // Find the custom field that matches the field_key
    const customField = pilot.custom_fields.find(field => {
      // Find the corresponding custom field definition to get the field_key
      const fieldDefinition = props.customFields.find(cf => cf.id === field.field_id)
      return fieldDefinition && fieldDefinition.field_key === fieldKey
    })
    
    if (customField) {
      return customField.value
    }
  }
  
  // Check if pilot has the field directly (fallback)
  if (pilot[fieldKey]) {
    return pilot[fieldKey]
  }
  
  return '-'
}

const fetchPilots = async () => {
  try {
    const response = await rotateDataService('/pilots/jxFetchPilots', {})
    pilots.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

// Action handlers
const editPilot = (pilot) => {
  // Emit event to parent component to open edit drawer
  window.dispatchEvent(new CustomEvent('edit-pilot', { detail: pilot }))
}

const deletePilot = async (pilot) => {
  if (confirm(`Delete pilot "${pilot.name}"?`)) {
    try {
      const response = await rotateDataService('/pilots/jxDeletePilot', { id: pilot.id })
      if (!response.hasErrors) {
        alert(response.message || 'Pilot deleted successfully')
        fetchPilots()
      } else {
        alert(response.message || 'Error occurred')
      }
    } catch (e) {
      console.error(e)
      alert('Error occurred while deleting pilot')
    }
  }
}

// Event listeners
const handlePilotsUpdated = () => {
  fetchPilots()
}

const handleEditPilot = (event) => {
  // This will be handled by the parent component
  window.dispatchEvent(new CustomEvent('open-edit-drawer', { detail: event.detail }))
}

onMounted(() => {
  fetchPilots()
  window.addEventListener('pilots-updated', handlePilotsUpdated)
  window.addEventListener('edit-pilot', handleEditPilot)
})

onUnmounted(() => {
  window.removeEventListener('pilots-updated', handlePilotsUpdated)
  window.removeEventListener('edit-pilot', handleEditPilot)
})
</script>