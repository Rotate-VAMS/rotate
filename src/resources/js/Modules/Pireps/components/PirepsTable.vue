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
          <!-- Show icon and text inline if possible -->
          <th class="px-6 py-3 hover:bg-gray-200 transition-colors">
            <UserIcon class="w-4 h-4 inline-block mr-2" /> Pilot
          </th>
          <th class="px-6 py-3 hover:bg-gray-200 transition-colors">
            <RouteIcon class="w-4 h-4 inline-block mr-2" /> Route
          </th>
          <th class="px-6 py-3 hover:bg-gray-200 transition-colors">
            <ClockIcon class="w-4 h-4 inline-block mr-2" /> Flight Time
          </th>
          <th class="px-6 py-3 hover:bg-gray-200 transition-colors">
            <PlaneIcon class="w-4 h-4 inline-block mr-2" /> Flight Type
          </th>
          <th class="px-6 py-3 hover:bg-gray-200 transition-colors">
            <RouteIcon class="w-4 h-4 inline-block mr-2" /> Distance
          </th>
          <th 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-3"
          >
            {{ customField.field_name }}
          </th>
          <th class="px-6 py-3" v-if="user.permissions.includes('edit-all-pirep') || user.permissions.includes('delete-all-pirep') || user.permissions.includes('edit-own-pirep') || user.permissions.includes('delete-own-pirep')">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="pirep in filteredPireps"
          :key="pirep.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-4">
            <div class="font-semibold">{{ pirep.pilot_name }}</div>
            <div class="text-s text-gray-400">{{ pirep.callsign }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="font-semibold">{{ pirep.origin }} - {{ pirep.destination }}</div>
            <div class="text-s text-gray-400">{{ pirep.flight_number }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="font-semibold">{{ formatFlightTime(pirep.flight_time) }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2">
              <span 
                :class="getFlightTypePillClass(pirep.flight_type_name)"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white"
              >
                {{ pirep.flight_type_name }}
              </span>
              <span class="text-xs text-gray-400">({{ pirep.multiplier }}x)</span>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="font-semibold">{{ pirep.distance }} NM</div>
          </td>

          <td 
            v-for="customField in customFields" 
            :key="customField.id" 
            class="px-6 py-4"
          >
            <div class="font-semibold">
              {{ getCustomFieldValue(pirep, customField.field_key) }}
            </div>
          </td>
          <td class="px-6 py-4" v-if="user.permissions.includes('edit-all-pirep') || user.permissions.includes('delete-all-pirep') || (user.permissions.includes('edit-own-pirep') && pirep.user_id === user.id) || (user.permissions.includes('delete-own-pirep') && pirep.user_id === user.id)">
            <div class="flex items-center gap-2">
              <button 
                v-if="user.permissions.includes('edit-all-pirep') || (user.permissions.includes('edit-own-pirep') && pirep.user_id === user.id)"
                @click="editPirep(pirep)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Pirep"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                v-if="user.permissions.includes('delete-all-pirep') || (user.permissions.includes('delete-own-pirep') && pirep.user_id === user.id)"
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
import { FilterIcon, BadgeIcon, EditIcon, TrashIcon, UserIcon, ClockIcon, PlaneIcon } from 'lucide-vue-next'
import RotateDataService from '@/rotate.js'
import { RouteIcon } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

const showToast = inject('showToast');
const page = usePage();
const user = page.props.auth.user;

// Props
const props = defineProps({
  customFields: {
    type: Array,
    default: () => []
  },
  pireps: {
    type: Array,
    default: () => []
  }
})

const search = ref('')

// Computed property for filtered pireps
const filteredPireps = computed(() => {
  if (!search.value) return props.pireps
  
  const searchTerm = search.value.toLowerCase().trim()
  
  return props.pireps.filter(pirep => {
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

// Function to get gradient class for flight type pills
const getFlightTypePillClass = (flightTypeName) => {
  const gradients = [
    'bg-gradient-to-r from-indigo-500 to-purple-500',
    'bg-gradient-to-r from-blue-500 to-cyan-500',
    'bg-gradient-to-r from-green-500 to-emerald-500',
    'bg-gradient-to-r from-orange-500 to-red-500',
    'bg-gradient-to-r from-pink-500 to-rose-500',
    'bg-gradient-to-r from-yellow-500 to-orange-500',
    'bg-gradient-to-r from-teal-500 to-blue-500',
    'bg-gradient-to-r from-purple-500 to-pink-500',
    'bg-gradient-to-r from-red-500 to-pink-500',
    'bg-gradient-to-r from-cyan-500 to-blue-500',
    'bg-gradient-to-r from-emerald-500 to-teal-500',
    'bg-gradient-to-r from-violet-500 to-purple-500'
  ];
  
  // Assign classes randomly
  const hash = flightTypeName.split('').reduce((acc, char) => {
    return acc + char.charCodeAt(0);
  }, 0);
  
  return gradients[Math.abs(hash) % gradients.length];
};

const formatFlightTime = (totalMinutes) => {
  if (totalMinutes === null || totalMinutes === undefined) {
    return '-';
  }
  const hours = Math.floor(totalMinutes / 60);
  const minutes = totalMinutes % 60;
  return `${hours}h ${minutes}m`;
};

// Action handlers
const editPirep = (pirep) => {
  // Emit event to parent component to open edit drawer
  window.dispatchEvent(new CustomEvent('edit-pirep', { detail: pirep }))
}

const deletePirep = async (pirep) => {
  page.props.loading = true
  const response = await RotateDataService('/pireps/jxDeletePireps', { id: pirep.id })
  if (response.hasErrors) {
    page.props.loading = false
    showToast(response.message || 'Error occurred', 'error')
    return;
  }
  showToast(response.message || 'Pirep deleted successfully', 'success')
  page.props.loading = false
  window.dispatchEvent(new CustomEvent('pireps-updated'))
}

// Event listeners
const handleEditPirep = (event) => {
  // This will be handled by the parent component
  window.dispatchEvent(new CustomEvent('open-edit-drawer', { detail: event.detail }))
}

onMounted(() => {
  window.addEventListener('edit-pirep', handleEditPirep)
})

onUnmounted(() => {
  window.removeEventListener('edit-pirep', handleEditPirep)
})
</script>