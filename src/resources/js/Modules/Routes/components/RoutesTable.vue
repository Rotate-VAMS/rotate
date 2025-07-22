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
            placeholder="Search routes..."
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
            {{ filteredRoutes.length }} of {{ routes.length }} routes
          </div>
          
          <!-- Group Selector -->
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm text-gray-600 whitespace-nowrap">Group by:</label>
            <select
              v-model="groupBy"
              class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-0 flex-1 sm:flex-none sm:w-48"
            >
              <option value="">No grouping</option>
              <option value="minimum_rank">Minimum Rank</option>
              <option value="status">Status</option>
              <option value="distance">Distance</option>
              <option value="flight_time">Flight Time</option>
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
    <div v-if="groupBy && groupedRoutes.length > 0" class="overflow-x-auto">
      <div v-for="group in groupedRoutes" :key="group.key" class="border-b border-gray-200">
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
              <span class="whitespace-nowrap">Count {{ group.routes.length }}</span>
              <span v-if="groupBy === 'distance'" class="whitespace-nowrap">Sum {{ group.totalDistance }} NM</span>
              <span v-if="groupBy === 'flight_time'" class="whitespace-nowrap">Sum {{ formatFlightTime(group.totalFlightTime) }}</span>
            </div>
          </div>
        </div>
        
        <!-- Group Content -->
        <div v-if="!collapsedGroups.includes(group.key)" class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-700 min-w-full">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
              <tr>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Flight Number</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Route</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Aircraft</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap text-center">Distance</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Flight Time</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Minimum Rank</th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Status</th>
                <th 
                  v-for="customField in customFields" 
                  :key="customField.id" 
                  class="px-4 sm:px-6 py-3 whitespace-nowrap"
                >
                  {{ customField.field_name }}
                </th>
                <th class="px-4 sm:px-6 py-3 whitespace-nowrap" v-if="user.permissions.includes('edit-route') || user.permissions.includes('delete-route')">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="route in group.routes"
                :key="route.id"
                class="border-b hover:bg-gray-50"
              >
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="font-semibold">{{ route.flight_number }}</div>
                </td>
                <td class="px-4 sm:px-6 py-4">
                  <div class="font-semibold">{{ route.route }}</div>
                  <div class="text-s text-gray-400">{{ route.name_route }}</div>
                </td>
                <td class="px-4 sm:px-6 py-4">
                  <div class="flex flex-wrap gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
                    <div v-for="(aircraft, key) in route.fleet_names" :key="key" class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium">{{ aircraft }} - {{ key }}</div>
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 text-center font-medium">{{ route.distance }} NM</td>
                <td class="px-4 sm:px-6 py-4">
                  <div class="text-xs text-gray-400">{{ route.flight_time }}</div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-xs text-gray-400">{{ route.minimum_rank }}</div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-xs text-gray-400">{{ route.status ? 'Active' : 'Inactive' }}</div>
                </td>
                <td 
                  v-for="customField in customFields" 
                  :key="customField.id" 
                  class="px-4 sm:px-6 py-4 whitespace-nowrap"
                >
                  <div class="text-sm">
                    {{ getCustomFieldValue(route, customField.field_key) }}
                  </div>
                </td>
                <td class="px-4 sm:px-6 py-4 whitespace-nowrap" v-if="user.permissions.includes('edit-route') || user.permissions.includes('delete-route')">
                  <div class="flex items-center gap-1 sm:gap-2">
                    <button 
                      v-if="user.permissions.includes('edit-route')"
                      @click="editRoute(route)"
                      class="text-blue-600 hover:text-blue-800 p-1 rounded"
                      title="Edit Route"
                    >
                      <EditIcon class="w-4 h-4" />
                    </button>
                    <button 
                      v-if="user.permissions.includes('delete-route')"
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
      </div>
    </div>

    <!-- Regular Table View (when no grouping) -->
    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700 min-w-full">
        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap cursor-pointer hover:bg-gray-200 transition-colors">Flight Number</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Route</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Aircraft</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap text-center">Distance</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Flight Time</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Minimum Rank</th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap">Status</th>
            <th 
              v-for="customField in customFields" 
              :key="customField.id" 
              class="px-4 sm:px-6 py-3 whitespace-nowrap"
            >
              {{ customField.field_name }}
            </th>
            <th class="px-4 sm:px-6 py-3 whitespace-nowrap" v-if="user.permissions.includes('edit-route') || user.permissions.includes('delete-route')">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="route in filteredRoutes"
            :key="route.id"
            class="border-b hover:bg-gray-50"
          >
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
              <div class="font-semibold">{{ route.flight_number }}</div>
            </td>
            <td class="px-4 sm:px-6 py-4">
              <div class="font-semibold">{{ route.route }}</div>
              <div class="text-s text-gray-400">{{ route.name_route }}</div>
            </td>
            <td class="px-4 sm:px-6 py-4">
              <div class="flex flex-wrap gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
                <div v-for="(aircraft, key) in route.fleet_names" :key="key" class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium">{{ aircraft }} - {{ key }}</div>
              </div>
            </td>
            <td class="px-4 sm:px-6 py-4 text-center font-medium">{{ route.distance }} NM</td>
            <td class="px-4 sm:px-6 py-4">
              <div class="text-xs text-gray-400">{{ route.flight_time }}</div>
            </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
              <div class="text-xs text-gray-400">{{ route.minimum_rank }}</div>
            </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
              <div class="text-xs text-gray-400">{{ route.status ? 'Active' : 'Inactive' }}</div>
            </td>
            <td 
              v-for="customField in customFields" 
              :key="customField.id" 
              class="px-4 sm:px-6 py-4 whitespace-nowrap"
            >
              <div class="text-sm">
                {{ getCustomFieldValue(route, customField.field_key) }}
              </div>
            </td>
            <td class="px-4 sm:px-6 py-4 whitespace-nowrap" v-if="user.permissions.includes('edit-route') || user.permissions.includes('delete-route')">
              <div class="flex items-center gap-1 sm:gap-2">
                <button 
                  v-if="user.permissions.includes('edit-route')"
                  @click="editRoute(route)"
                  class="text-blue-600 hover:text-blue-800 p-1 rounded"
                  title="Edit Route"
                >
                  <EditIcon class="w-4 h-4" />
                </button>
                <button 
                  v-if="user.permissions.includes('delete-route')"
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { FilterIcon, BadgeIcon, EditIcon, TrashIcon, ChevronDownIcon, ChevronRightIcon } from 'lucide-vue-next'
import RotateDataService from '@/rotate.js'
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

const routes = ref([])
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

// Computed property for filtered routes
const filteredRoutes = computed(() => {
  if (!search.value) return routes.value
  
  const searchTerm = search.value.toLowerCase().trim()
  
  return routes.value.filter(route => {
    // Search in flight number
    if (route.flight_number?.toLowerCase().includes(searchTerm)) return true
    
    // Search in route
    if (route.route?.toLowerCase().includes(searchTerm)) return true
    
    // Search in route name
    if (route.name_route?.toLowerCase().includes(searchTerm)) return true
    
    // Search in minimum rank
    if (route.minimum_rank?.toLowerCase().includes(searchTerm)) return true
    
    // Search in fleet names
    if (route.fleet_names) {
      return Object.values(route.fleet_names).some(aircraft => 
        aircraft?.toLowerCase().includes(searchTerm)
      )
    }
    
    return false
  })
})

// Computed property for grouped routes
const groupedRoutes = computed(() => {
  if (!groupBy.value || !filteredRoutes.value.length) return []
  
  const groups = {}
  
  filteredRoutes.value.forEach(route => {
    let groupValue = ''
    
    if (groupBy.value === 'minimum_rank') {
      groupValue = route.minimum_rank || 'Unknown'
    } else if (groupBy.value === 'status') {
      groupValue = route.status ? 'Active' : 'Inactive'
    } else if (groupBy.value === 'distance') {
      // Group by distance ranges
      const distance = parseInt(route.distance) || 0
      if (distance < 500) groupValue = '0-500 NM'
      else if (distance < 1000) groupValue = '500-1000 NM'
      else if (distance < 2000) groupValue = '1000-2000 NM'
      else if (distance < 5000) groupValue = '2000-5000 NM'
      else groupValue = '5000+ NM'
    } else if (groupBy.value === 'flight_time') {
      // Group by flight time ranges
      const timeStr = route.flight_time || '0:00'
      const [hours, minutes] = timeStr.split(':').map(Number)
      const totalMinutes = (hours * 60) + (minutes || 0)
      
      if (totalMinutes < 60) groupValue = '0-1 Hour'
      else if (totalMinutes < 180) groupValue = '1-3 Hours'
      else if (totalMinutes < 360) groupValue = '3-6 Hours'
      else if (totalMinutes < 720) groupValue = '6-12 Hours'
      else groupValue = '12+ Hours'
    } else {
      // Custom field grouping
      groupValue = getCustomFieldValue(route, groupBy.value) || 'Unknown'
    }
    
    if (!groups[groupValue]) {
      groups[groupValue] = {
        key: groupValue,
        value: groupValue,
        routes: [],
        totalDistance: 0,
        totalFlightTime: 0
      }
    }
    
    groups[groupValue].routes.push(route)
    groups[groupValue].totalDistance += parseInt(route.distance) || 0
    
    // Calculate total flight time
    const timeStr = route.flight_time || '0:00'
    const [hours, minutes] = timeStr.split(':').map(Number)
    const totalMinutes = (hours * 60) + (minutes || 0)
    groups[groupValue].totalFlightTime += totalMinutes
  })
  
  // Sort groups
  const sortedGroups = Object.values(groups).sort((a, b) => {
    if (groupBy.value === 'distance') {
      // Sort distance ranges numerically
      const aDistance = parseInt(a.value.split('-')[0]) || 0
      const bDistance = parseInt(b.value.split('-')[0]) || 0
      return aDistance - bDistance
    } else if (groupBy.value === 'flight_time') {
      // Sort flight time ranges numerically
      const aTime = a.value.includes('+') ? 9999 : parseInt(a.value.split('-')[0]) || 0
      const bTime = b.value.includes('+') ? 9999 : parseInt(b.value.split('-')[0]) || 0
      return aTime - bTime
    }
    return a.value.localeCompare(b.value)
  })
  
  return sortedGroups
})

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

// Function to get group label
const getGroupLabel = (groupKey) => {
  if (groupBy.value === 'minimum_rank') return 'MINIMUM RANK'
  if (groupBy.value === 'status') return 'STATUS'
  if (groupBy.value === 'distance') return 'DISTANCE'
  if (groupBy.value === 'flight_time') return 'FLIGHT TIME'
  
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

// Function to format flight time
const formatFlightTime = (totalMinutes) => {
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