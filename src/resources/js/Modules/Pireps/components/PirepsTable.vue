<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <div class="relative w-1/2">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <FilterIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="search"
          type="text"
          placeholder="Search pireps..."
          class="input border border-gray-300 rounded-md pl-10 pr-10 py-2 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <button
          v-if="search"
          @click="search = ''"
          class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div class="flex items-center gap-4">
        <div v-if="search" class="text-sm text-gray-500">
          {{ filteredPireps.length }} of {{ pireps.length }} pireps
        </div>
      </div>
    </div>

    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-3 cursor-pointer hover:bg-gray-200 transition-colors">Route</th>
          <th class="px-6 py-3">Flight Time</th>
          <th class="px-6 py-3">Flight Type</th>
          <th class="px-6 py-3">Distance</th>
          <th class="px-6 py-3">Status</th>
          <th 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-3"
          >
            {{ customField.field_name }}
          </th>
          <th class="px-6 py-3" v-if="user.permissions.includes('edit-pirep') || user.permissions.includes('delete-pirep')">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="pirep in filteredPireps"
          :key="pirep.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-4">
            <div class="font-semibold">{{ pirep.route }}</div>
            <div class="text-s text-gray-400">{{ pirep.flight_number }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ formatFlightTime(pirep.flight_time) }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ pirep.flight_type_name }}</div>
          </td>
          <td class="px-6 py-4 text-center font-medium">{{ pirep.distance }} NM</td>
          <td class="px-6 py-4">
            <div class="text-xs text-gray-400">{{ pirep.status ? 'Completed' : 'In-Progress' }}</div>
          </td>
          <td 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-4"
          >
            <div class="text-sm">
              {{ getCustomFieldValue(pirep, customField.field_key) }}
            </div>
          </td>
          <td class="px-6 py-4" v-if="user.permissions.includes('edit-pirep') || user.permissions.includes('delete-pirep')">
            <div class="flex items-center gap-2">
              <button 
                v-if="user.permissions.includes('edit-pirep')"
                @click="editPirep(pirep)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Pirep"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                v-if="user.permissions.includes('delete-pirep')"
                @click="deletePirep(pirep)"
                class="text-red-600 hover:text-red-800 p-1 rounded"
                title="Delete Pirep"
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
import { ref, computed, onMounted, onUnmounted, inject } from 'vue'
import { FilterIcon, BadgeIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
import RotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';

const showToast = inject('showToast');
const page = usePage();
const user = page.props.auth.user;

// Props
const props = defineProps({
  customFields: {
    type: Array,
    default: () => []
  }
})

const pireps = ref([])
const search = ref('')

// Computed property for filtered pireps
const filteredPireps = computed(() => {
  if (!search.value) return pireps.value
  
  const searchTerm = search.value.toLowerCase().trim()
  
  return pireps.value.filter(pirep => {
    // Search in route
    if (pirep.route?.toLowerCase().includes(searchTerm)) return true
    
    // Search in flight number
    if (pirep.flight_number?.toLowerCase().includes(searchTerm)) return true
    
    // Search in flight type
    if (pirep.flight_type_name?.toLowerCase().includes(searchTerm)) return true
    
    // Search in pilot name
    if (pirep.pilot_name?.toLowerCase().includes(searchTerm)) return true
    
    return false
  })
})

const sortKey = ref('')
const sortAsc = ref(true)

const statusText = {
  '1': 'Active',
  '0': 'Inactive'
}

// Function to get custom field value
const getCustomFieldValue = (pirep, fieldKey) => {
  // Check if pirep has custom_fields array
  if (pirep.custom_fields && Array.isArray(pirep.custom_fields)) {
    // Find the custom field that matches the field_key
    const customField = pirep.custom_fields.find(field => {
      // Find the corresponding custom field definition to get the field_key
      const fieldDefinition = props.customFields.find(cf => cf.id === field.field_id)
      return fieldDefinition && fieldDefinition.field_key === fieldKey
    })
    
    if (customField) {
      return customField.value_display
    }
  }
  
  // Check if pirep has the field directly (fallback)
  if (pirep[fieldKey]) {
    return pirep[fieldKey]
  }
  
  return '-'
}

const formatFlightTime = (totalMinutes) => {
  if (totalMinutes === null || totalMinutes === undefined) {
    return '-';
  }
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;
  return `${hours}h ${minutes}m`;
};

const fetchPireps = async () => {
  try {
    const response = await RotateDataService('/pireps/jxFetchPireps')
    pireps.value = response.data || []
  } catch (e) {
    console.error(e)
    showToast('Error fetching pireps', 'error')
  }
}

// Action handlers
const editPirep = (pirep) => {
  // Emit event to parent component to open edit drawer
  window.dispatchEvent(new CustomEvent('edit-pirep', { detail: pirep }))
}

const deletePirep = async (pirep) => {
  const response = await RotateDataService('/pireps/jxDeletePireps', { id: pirep.id })
  if (response.hasErrors) {
    showToast(response.message || 'Error occurred', 'error')
    return;
  }
  showToast(response.message || 'Pirep deleted successfully', 'success')
  fetchPireps()
}

// Event listeners
const handlePirepsUpdated = () => {
  fetchPireps()
}

const handleEditPirep = (event) => {
  // This will be handled by the parent component
  window.dispatchEvent(new CustomEvent('open-edit-drawer', { detail: event.detail }))
}

onMounted(() => {
  fetchPireps()
  window.addEventListener('pireps-updated', handlePirepsUpdated)
  window.addEventListener('edit-pirep', handleEditPirep)
})

onUnmounted(() => {
  window.removeEventListener('pireps-updated', handlePirepsUpdated)
  window.removeEventListener('edit-pirep', handleEditPirep)
})
</script>