<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <!-- Header Section -->
    <div class="p-4 space-y-4">
      <!-- Search and Controls Row -->
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
        <!-- Search Input -->
        <div class="relative flex-1 min-w-0">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <FilterIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="search"
          type="text"
          placeholder="Search pilots..."
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

        <!-- Controls Section -->
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center w-full sm:w-auto">
          <!-- Search Results Count -->
          <div v-if="search" class="text-sm text-gray-500 whitespace-nowrap">
          {{ filteredPilots.length }} of {{ pilots.length }} pilots
          </div>
          
          <!-- Group Selector -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm text-gray-600 whitespace-nowrap">Group by:</label>
            <select
              v-model="groupBy"
              class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-0 flex-1 sm:flex-none sm:w-48"
            >
              <option value="">No grouping</option>
              <option value="rank">Rank</option>
              <option value="status">Status</option>
              <option value="flying_hours">Flying Hours</option>
              <option 
                v-for="customField in groupableCustomFields" 
                :key="customField.id" 
                :value="customField.field_key"
              >
                {{ customField.field_name }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Grouped Table View -->
    <div v-if="groupBy && groupedPilots.length > 0" class="overflow-x-auto">
      <div v-for="group in groupedPilots" :key="group.key" class="border-b border-gray-200">
        <!-- Group Header -->
        <div 
          class="flex items-center justify-between px-4 sm:px-6 py-3 cursor-pointer hover:bg-gray-50"
          @click="toggleGroup(group.key)"
          :class="getGroupHeaderClass(group.key)"
        >
          <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
            <button class="text-gray-500 hover:text-gray-700 flex-shrink-0">
              <ChevronDownIcon v-if="!collapsedGroups.includes(group.key)" class="w-4 h-4" />
              <ChevronRightIcon v-else class="w-4 h-4" />
            </button>
            <div class="flex items-center gap-2 min-w-0 flex-1">
              <span class="font-medium text-xs sm:text-sm uppercase text-gray-600 flex-shrink-0">{{ getGroupLabel(group.key) }}</span>
              <span class="text-sm font-semibold truncate">{{ group.value }}</span>
            </div>
            <div class="flex items-center gap-2 sm:gap-4 text-xs text-gray-500 flex-shrink-0">
              <span class="whitespace-nowrap">Count {{ group.pilots.length }}</span>
              <span v-if="groupBy === 'flying_hours'" class="whitespace-nowrap">Sum {{ formatHours(group.totalHours) }}</span>
            </div>
          </div>
        </div>
        
        <!-- Group Content -->
        <div v-if="!collapsedGroups.includes(group.key)" class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-700 min-w-full">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Pilot</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Rank</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap text-center">Flights</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap text-center">Hours</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Recent Flights</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Status</th>
                <th 
                  v-for="customField in customFields" 
                  :key="customField.id" 
                  class="px-4 sm:px-6 py-3 whitespace-nowrap"
                >
                  {{ customField.field_name }}
                </th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap" v-if="user.permissions.includes('edit-user') || user.permissions.includes('delete-user')">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="pilot in group.pilots"
                :key="pilot.id"
                class="border-b hover:bg-gray-50"
              >
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="font-semibold">{{ pilot.name }}</div>
                  <div class="text-xs text-gray-400">{{ pilot.callsign }}</div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <span :class="pilot.rank_color" class="inline-flex items-center text-sm font-medium px-2 py-1 rounded-full">
                    <BadgeIcon class="w-4 h-4 mr-1" /> {{ pilot.rank }}
                  </span>
                </td>
                <td class="px-4 sm:px-6 py-4 text-center font-medium">{{ pilot.flights }}</td>
                <td class="px-4 sm:px-6 py-4 text-center font-medium">{{ pilot.flying_hours }}</td>
                <td class="px-4 sm:px-6 py-4">
                  <div class="flex gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
                    <span
                      v-for="flight in pilot.recent_flights"
                      :key="flight"
                      class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium flex-shrink-0"
                    >
                      {{ flight.origin }} - {{ flight.destination }}
                    </span>
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
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
                  class="px-4 sm:px-6 py-4 whitespace-nowrap"
                >
                  <div class="text-sm">
                    {{ getCustomFieldValue(pilot, customField.field_key) }}
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap" v-if="user.permissions.includes('edit-user') || user.permissions.includes('delete-user')">
                  <div class="flex items-center gap-1 sm:gap-2">
                    <button 
                      v-if="user.permissions.includes('edit-user')"
                      @click="editPilot(pilot)"
                      class="text-blue-600 hover:text-blue-800 p-1 rounded"
                      title="Edit Pilot"
                    >
                      <EditIcon class="w-4 h-4" />
                    </button>
                    <button 
                      v-if="user.permissions.includes('delete-user')"
                      @click="deletePilot(pilot)"
                      class="text-red-600 hover:text-red-800 p-1 rounded"
                      title="Delete Pilot"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                    <button
                      v-if="user.permissions.includes('edit-user')"
                      @click="togglePilotStatus(pilot)"
                      :class="pilot.status == '1' ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
                      class="p-1 rounded"
                      :title="pilot.status == '1' ? 'Deactivate Pilot' : 'Activate Pilot'"
                    >
                      <ShieldMinusIcon v-if="pilot.status == '1'" class="w-4 h-4" />
                      <ShieldCheckIcon v-else class="w-4 h-4" />
        </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Regular Table View (when no grouping) -->
    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700 min-w-full">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Pilot</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Rank</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap text-center">Flights</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap text-center">Hours</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Recent Flights</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Status</th>
          <th 
            v-for="customField in customFields" 
            :key="customField.id" 
              class="px-4 sm:px-6 py-3 whitespace-nowrap"
          >
            {{ customField.field_name }}
          </th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap" v-if="user.permissions.includes('edit-user') || user.permissions.includes('delete-user')">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="pilot in filteredPilots"
          :key="pilot.id"
          class="border-b hover:bg-gray-50"
        >
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
            <div class="font-semibold">{{ pilot.name }}</div>
            <div class="text-xs text-gray-400">{{ pilot.callsign }}</div>
          </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
            <span :class="pilot.rank_color" class="inline-flex items-center text-sm font-medium px-2 py-1 rounded-full">
              <BadgeIcon class="w-4 h-4 mr-1" /> {{ pilot.rank }}
            </span>
          </td>
            <td class="px-4 sm:px-6 py-4 text-center font-medium">{{ pilot.flights }}</td>
            <td class="px-4 sm:px-6 py-4 text-center font-medium">{{ pilot.flying_hours }}</td>
            <td class="px-4 sm:px-6 py-4">
            <div class="flex gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
              <span
                v-for="flight in pilot.recent_flights"
                :key="flight"
                  class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium flex-shrink-0"
              >
                {{ flight.origin }} - {{ flight.destination }}
              </span>
            </div>
          </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
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
              class="px-4 sm:px-6 py-4 whitespace-nowrap"
          >
            <div class="text-sm">
              {{ getCustomFieldValue(pilot, customField.field_key) }}
            </div>
          </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap" v-if="user.permissions.includes('edit-user') || user.permissions.includes('delete-user')">
              <div class="flex items-center gap-1 sm:gap-2">
              <button 
                v-if="user.permissions.includes('edit-user')"
                @click="editPilot(pilot)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Pilot"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                v-if="user.permissions.includes('delete-user')"
                @click="deletePilot(pilot)"
                class="text-red-600 hover:text-red-800 p-1 rounded"
                title="Delete Pilot"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
              <button
                v-if="user.permissions.includes('edit-user')"
                @click="togglePilotStatus(pilot)"
                :class="pilot.status == '1' ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
                class="p-1 rounded"
                :title="pilot.status == '1' ? 'Deactivate Pilot' : 'Activate Pilot'"
              >
                <ShieldMinusIcon v-if="pilot.status == '1'" class="w-4 h-4" />
                <ShieldCheckIcon v-else class="w-4 h-4" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { FilterIcon, BadgeIcon, EditIcon, TrashIcon, ShieldCheckIcon, ShieldMinusIcon, ChevronDownIcon, ChevronRightIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

// Props
const props = defineProps({
  customFields: {
    type: Array,
    default: () => []
  }
})

const pilots = ref([])
const search = ref('')
const groupBy = ref('')
const collapsedGroups = ref([])

// Computed property for groupable custom fields (integer, text, dropdown)
const groupableCustomFields = computed(() => {
  return props.customFields.filter(field => {
    // Allow grouping for: 1 (Text), 2 (Integer), 6 (Dropdown)
    return [1, 2, 6].includes(field.data_type)
  })
})

// Computed property for filtered pilots
const filteredPilots = computed(() => {
  if (!search.value) return pilots.value
  
  const searchTerm = search.value.toLowerCase().trim()
  
  return pilots.value.filter(pilot => {
    // Search in pilot name
    if (pilot.name?.toLowerCase().includes(searchTerm)) return true
    
    // Search in callsign
    if (pilot.callsign?.toLowerCase().includes(searchTerm)) return true
    
    // Search in rank
    if (pilot.rank?.toLowerCase().includes(searchTerm)) return true
    
    // Search in recent flights
    if (pilot.recent_flights && Array.isArray(pilot.recent_flights)) {
      return pilot.recent_flights.some(flight => 
        flight.origin?.toLowerCase().includes(searchTerm) ||
        flight.destination?.toLowerCase().includes(searchTerm)
      )
    }
    
    return false
  })
})

// Computed property for grouped pilots
const groupedPilots = computed(() => {
  if (!groupBy.value || !filteredPilots.value.length) return []
  
  const groups = {}
  
  filteredPilots.value.forEach(pilot => {
    let groupValue = ''
    
    if (groupBy.value === 'rank') {
      groupValue = pilot.rank || 'Unknown'
    } else if (groupBy.value === 'status') {
      groupValue = statusText[pilot.status] || 'Unknown'
    } else if (groupBy.value === 'flying_hours') {
      // Group by hour ranges
      const hours = parseInt(pilot.flying_hours) || 0
      if (hours < 50) groupValue = '0-50 Hours'
      else if (hours < 100) groupValue = '50-100 Hours'
      else if (hours < 250) groupValue = '100-250 Hours'
      else if (hours < 500) groupValue = '250-500 Hours'
      else groupValue = '500+ Hours'
    } else {
      // Custom field grouping
      groupValue = getCustomFieldValue(pilot, groupBy.value) || 'Unknown'
    }
    
    if (!groups[groupValue]) {
      groups[groupValue] = {
        key: groupValue,
        value: groupValue,
        pilots: [],
        totalHours: 0
      }
    }
    
    groups[groupValue].pilots.push(pilot)
    groups[groupValue].totalHours += parseInt(pilot.flying_hours) || 0
  })
  
  // Sort groups
  const sortedGroups = Object.values(groups).sort((a, b) => {
    if (groupBy.value === 'flying_hours') {
      // Sort hour ranges numerically
      const aHours = parseInt(a.value.split('-')[0]) || 0
      const bHours = parseInt(b.value.split('-')[0]) || 0
      return aHours - bHours
    }
    return a.value.localeCompare(b.value)
  })
  
  return sortedGroups
})

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

// Function to get group label
const getGroupLabel = (groupKey) => {
  if (groupBy.value === 'rank') return 'RANK'
  if (groupBy.value === 'status') return 'STATUS'
  if (groupBy.value === 'flying_hours') return 'FLIGHT HOURS'
  
  // For custom fields, find the field name
  const customField = props.customFields.find(field => field.field_key === groupBy.value)
  return customField ? customField.field_name.toUpperCase() : 'GROUP'
}

// Function to get group header class
const getGroupHeaderClass = (groupKey) => {
  const colors = [
    'bg-red-50 border-l-4 border-red-400',
    'bg-blue-50 border-l-4 border-blue-400',
    'bg-green-50 border-l-4 border-green-400',
    'bg-purple-50 border-l-4 border-purple-400',
    'bg-yellow-50 border-l-4 border-yellow-400',
    'bg-pink-50 border-l-4 border-pink-400',
    'bg-indigo-50 border-l-4 border-indigo-400',
    'bg-gray-50 border-l-4 border-gray-400'
  ]
  
  // Use hash of group key to determine color
  const hash = groupKey.split('').reduce((a, b) => {
    a = ((a << 5) - a) + b.charCodeAt(0)
    return a & a
  }, 0)
  
  return colors[Math.abs(hash) % colors.length]
}

// Function to format hours
const formatHours = (totalMinutes) => {
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  return `${hours}:${minutes.toString().padStart(2, '0')}`
}

// Function to toggle group collapse
const toggleGroup = (groupKey) => {
  const index = collapsedGroups.value.indexOf(groupKey)
  if (index > -1) {
    collapsedGroups.value.splice(index, 1)
  } else {
    collapsedGroups.value.push(groupKey)
  }
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

const togglePilotStatus = async (pilot) => {
  const response = await rotateDataService('/pilots/jxTogglePilotStatus', { id: pilot.id })
  if (!response.hasErrors) {
    alert(response.message || 'Pilot status toggled successfully')
    fetchPilots()
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