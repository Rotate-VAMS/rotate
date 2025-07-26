<template>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-0">
      <div class="w-full sm:w-auto">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">View Routes</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">View and manage all routes in your airline. Monitor performance, track progress, and oversee route operations.</p>
      </div>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0" v-if="user.permissions.includes('create-route')">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-indigo-500 text-white font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto"
        >
          <PlusIcon class="w-4 h-4" /> <span>Add Route</span>
        </button>
        <button 
          @click="openImportModal"
          class="btn-white bg-white text-gray-800 font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto"
        >
          <ImportIcon class="w-4 h-4" /> <span>Import</span>
        </button>
        <button 
          @click="exportRoutes"
          class="btn-white bg-white text-gray-800 font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto">
          <UploadIcon class="w-4 h-4" /> <span>Export</span>
        </button> 
      </div>

      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Route' : 'Edit Route'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />

      <!-- Import Modal -->
      <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
              <h1 class="text-xl font-bold text-gray-900">Import Routes</h1>
              <button 
                @click="closeImportModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <XIcon class="w-5 h-5" />
              </button>
            </div>
            
            <div class="mb-4">
              <p class="text-sm text-gray-600 mb-3">
                Upload a CSV file to import routes. The file should contain the following columns:
              </p>
              <div class="bg-gray-50 p-3 rounded-md text-xs">
                <p class="font-bold text-md mb-2">Required columns:</p>
                <ul class="space-y-1 text-gray-600">
                  <li><strong>1. flight_number</strong> - Flight number (e.g., "ROT001")</li>
                  <li><strong>2. fleet_ids</strong> - Fleet IDs in JSON array format (e.g., "[1,2]")</li>
                  <li><strong>3. origin_icao</strong> - Origin airport ICAO code (e.g., "KJFK")</li>
                  <li><strong>4. destination_icao</strong> - Destination airport ICAO code (e.g., "EGLL")</li>
                  <li><strong>5. min_rank</strong> - Minimum rank ID (e.g., "1")</li>
                  <li><strong>6. status</strong> - Route status (1 for active, 0 for inactive)</li>
                </ul>
              </div>
              <div class="bg-gradient-to-r from-yellow-100 to-yellow-200 p-4 rounded-lg border border-yellow-400 shadow-lg mt-4">
                <div class="flex items-center gap-2">
                    <CircleAlertIcon size="14" class="text-yellow-600" />
                    <p class="font-bold text-sm text-yellow-800">How to get <b>Fleet IDs and Rank IDs</b>?</p>
                </div>
                <ul class="list-disc list-inside text-sm text-gray-600">
                    <li> Go to the <b>Settings</b> page.</li>
                    <li> Click on the <b>Fleets</b> tab for fleet IDs and <b>Ranks</b> tab for rank IDs.</li>
                    <li> On the card for each item, you will see the ID.</li>
                </ul>
            </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Select CSV File
              </label>
              <input
                type="file"
                ref="fileInput"
                @change="handleFileSelect"
                accept=".csv"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              />
            </div>

            <div v-if="selectedFile" class="mb-4">
              <p class="text-sm text-gray-600">
                Selected file: <span class="font-medium">{{ selectedFile.name }}</span>
              </p>
            </div>

            <div v-if="importStatus" class="mb-4">
              <div v-if="importStatus.type === 'loading'" class="flex items-center text-blue-600">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                <span class="text-sm">{{ importStatus.message }}</span>
              </div>
              <div v-else-if="importStatus.type === 'success'" class="text-green-600 text-sm">
                {{ importStatus.message }}
              </div>
              <div v-else-if="importStatus.type === 'error'" class="text-red-600 text-sm">
                {{ importStatus.message }}
              </div>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                @click="closeImportModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
              >
                Cancel
              </button>
              <button
                @click="importRoutes"
                :disabled="!selectedFile || importStatus?.type === 'loading'"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Import Routes
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, inject } from 'vue'
import { ImportIcon, UploadIcon, PlusIcon, XIcon, CircleAlertIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'
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

// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Import modal state
const showImportModal = ref(false)
const selectedFile = ref(null)
const fileInput = ref(null)
const importStatus = ref(null)

// Store fetched data
const fleets = ref([])
const ranks = ref([])

// Export routes
const exportRoutes = () => {
  page.props.loading = true
  window.location.href = '/routes/jxExportRoutes'
  page.props.loading = false
}

// Function to convert custom fields to form fields
const convertCustomFieldsToFormFields = (customFields) => {
  return customFields.map(field => {
    const formField = {
      name: field.field_key,
      label: field.field_name,
      type: getFieldType(field.data_type),
      required: field.is_required === 1,
      description: field.field_description
    }
    
    // Add options for dropdown fields
    if (field.data_type === 6) { // Dropdown type
      formField.options = [] // You might want to fetch options from another endpoint
    }
    
    return formField
  })
}

// Function to map data types to form field types
const getFieldType = (dataType) => {
  switch (dataType) {
    case 1: return 'text' // Text
    case 2: return 'number' // Integer
    case 3: return 'number' // Float
    case 4: return 'checkbox' // Boolean
    case 5: return 'datetime-local' // Date
    case 6: return 'select' // Dropdown
    default: return 'text'
  }
}

// Computed form fields that update based on the toggle state and custom fields
const formFields = computed(() => {
  const baseFields = [
    { name: 'flight_number', label: 'Flight Number', type: 'text', required: true },
    { 
      name: 'origin_icao', 
      label: 'Origin ICAO', 
      type: 'text', 
      required: true,
      transform: (value) => value?.toUpperCase(),
    },
    { 
      name: 'destination_icao', 
      label: 'Destination ICAO', 
      type: 'text', 
      required: true,
      transform: (value) => value?.toUpperCase(),
    },
    { 
      name: 'fleet_ids', 
      label: 'Fleet', 
      type: 'multiselect', 
      required: true, 
      options: fleets.value.map(fleet => fleet.name),
      placeholder: 'Select fleets...',
      searchPlaceholder: 'Search fleets...'
    }
  ]

  // Add rank-related fields based on mode
  if (formMode.value === 'create') {
    // In create mode, show the toggle and conditional rank field
    baseFields.push(
      { 
        name: 'use_aircraft_rank', 
        label: 'Take aircraft rank for route rank', 
        type: 'checkbox', 
        required: false,
        helperText: 'When enabled, the route will use the aircraft\'s rank. When disabled, you can select a specific rank for this route.'
      },
      {
        name: 'rank_id',
        label: 'Route Rank',
        type: 'select',
        required: false,
        options: ranks.value.map(rank => ({
          id: rank.id,
          name: rank.name
        })),
        conditional: {
          field: 'use_aircraft_rank',
          value: false,
          show: true,
          required: true
        }
      }
    )
  } else {
    // In edit mode, always show the rank field (no toggle)
    baseFields.push({
      name: 'rank_id',
      label: 'Route Rank',
      type: 'select',
      required: true,
      options: ranks.value.map(rank => ({
        id: rank.id,
        name: rank.name
      }))
    })
  }

  // Add custom fields
  const customFormFields = convertCustomFieldsToFormFields(props.customFields)
  return [...baseFields, ...customFormFields]
})

// Watch for custom fields changes and update form fields
watch(() => props.customFields, (newCustomFields) => {
  // The computed formFields will automatically update
}, { immediate: true })

// Open drawer for create
const openDrawerForCreate = () => {
  formMode.value = 'create'
  formData.value = {
    use_aircraft_rank: true // Default to true
  }
  showDrawer.value = true
}

// Import modal functions
const openImportModal = () => {
  showImportModal.value = true
  selectedFile.value = null
  importStatus.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const closeImportModal = () => {
  showImportModal.value = false
  selectedFile.value = null
  importStatus.value = null
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
      showToast('Please select a valid CSV file.', 'error')
      event.target.value = ''
      return
    }
    selectedFile.value = file
  }
}

const importRoutes = async () => {
  if (!selectedFile.value) {
    showToast('Please select a CSV file to import.', 'error')
    return
  }

  importStatus.value = {
    type: 'loading',
    message: 'Importing routes...'
  }

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const response = await fetch('/routes/jxImportRoutes', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json',
      },
      body: formData
    })

    const result = await response.json()

    if (result.hasErrors) {
      importStatus.value = {
        type: 'error',
        message: result.message
      }
      showToast(result.message, 'error')
    } else {
      importStatus.value = {
        type: 'success',
        message: result.message
      }
      showToast(result.message, 'success')
      
      // Trigger routes update event
      window.dispatchEvent(new CustomEvent('routes-updated'))
      
      // Close modal after a short delay
      setTimeout(() => {
        closeImportModal()
      }, 2000)
    }
  } catch (error) {
    console.error('Import error:', error)
    importStatus.value = {
      type: 'error',
      message: 'An error occurred while importing routes. Please try again.'
    }
    showToast('An error occurred while importing routes. Please try again.', 'error')
  }
}

// Fetch fleets
const fetchFleets = async () => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/settings/jxFetchFleets')
    fleets.value = response.data.map(fleet => ({
      id: fleet.id,
      name: fleet.livery + ' - ' + fleet.aircraft
    })) || []
    page.props.loading = false
  } catch (e) {
    console.error('Error fetching fleets:', e)
    page.props.loading = false
  }
}

// Fetch ranks
const fetchRanks = async () => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/settings/jxFetchRanks')
    ranks.value = response.data.map(rank => ({
      id: rank.id,
      name: rank.name
    })) || []
    page.props.loading = false
  } catch (e) {
    console.error('Error fetching ranks:', e)
    page.props.loading = false
  }
}

// Submit handler
const submitForm = async (payload) => {
  page.props.loading = true
  // Basic validation
  if (!payload.flight_number?.trim()) {
    showToast('Please enter a flight number.', 'error')
    return
  }
  
  if (!payload.origin_icao?.trim()) {
    showToast('Please enter an origin ICAO code.', 'error')
    return
  }
  
  if (!payload.origin_icao?.trim().match(/^[A-Z]{4}$/)) {
    showToast('Origin ICAO code must be exactly 4 uppercase letters.', 'error')
    return
  }
  
  if (!payload.destination_icao?.trim()) {
    showToast('Please enter a destination ICAO code.', 'error')
    return
  }
  
  if (!payload.destination_icao?.trim().match(/^[A-Z]{4}$/)) {
    showToast('Destination ICAO code must be exactly 4 uppercase letters.', 'error')
    return
  }
  
  if (!payload.fleet_ids || payload.fleet_ids.length === 0) {
    showToast('Please select at least one fleet.', 'error')
    return
  }

  // Validate rank selection based on mode
  if (formMode.value === 'create') {
    // In create mode, validate that rank is selected when toggle is off
    if (!payload.use_aircraft_rank && !payload.rank_id) {
      showToast('Please select a rank for this route when "Take aircraft rank for route rank" is disabled.', 'error')
      return
    }
  } else {
    // In edit mode, rank is always required
    if (!payload.rank_id) {
      showToast('Please select a rank for this route.', 'error')
      return
    }
    // In edit mode, always set use_aircraft_rank to false since we're directly showing rank selection
    payload.use_aircraft_rank = false
  }

  // Convert fleet names back to IDs for backend
  if (payload.fleet_ids && Array.isArray(payload.fleet_ids)) {
    const fleetIds = payload.fleet_ids.map(fleetName => {
      const fleet = fleets.value.find(f => f.name === fleetName)
      return fleet ? fleet.id : null
    }).filter(id => id !== null)
    
    payload.fleet_ids = fleetIds
  }

  // Separate custom fields from regular fields
  const customData = {}
  const regularData = {}
  
  // Get custom field keys
  const customFieldKeys = props.customFields.map(field => field.field_key)
  
  // Separate the data
  Object.keys(payload).forEach(key => {
    if (customFieldKeys.includes(key)) {
      customData[key] = payload[key]
    } else {
      regularData[key] = payload[key]
    }
  })
  
  // Add customData to the regular payload
  const finalPayload = {
    ...regularData,
    customData: customData
  }

  const response = await rotateDataService('/routes/jxCreateEditRoutes', finalPayload)
  if (response.hasErrors) {
    showToast(response.message, 'error')
    return;
  }
  showToast(response.message, 'success')
  window.dispatchEvent(new CustomEvent('routes-updated'))
  showDrawer.value = false
  page.props.loading = false
}

// Initialize data
fetchFleets()
fetchRanks()

// Handle edit route event from table
const handleOpenEditDrawer = async (event) => {
  page.props.loading = true
  const route = event.detail
  
  // Ensure fleets are loaded before mapping
  if (fleets.value.length === 0) {
    await fetchFleets()
  }
  
  // Map route data for the form
  const mappedRoute = {
    ...route,
    // Map fleet IDs to fleet display names for the multiselect
    fleet_ids: route.fleet_ids.map(fleetId => {
      const fleetData = fleets.value.find(f => f.id === fleetId)
      return fleetData ? fleetData.name : null
    }).filter(name => name !== null),
    // Map rank data
    rank_id: route.min_rank_id,
    use_aircraft_rank: route.use_aircraft_rank !== false, // Default to true if not explicitly false
  }
  
  // Map custom fields from custom_fields array to direct properties
  if (route.custom_fields && Array.isArray(route.custom_fields)) {
    route.custom_fields.forEach(customField => {
      // Find the custom field definition to get the field_key
      const fieldDefinition = props.customFields.find(cf => cf.id === customField.field_id)
      if (fieldDefinition) {
        // Map the value to the field_key for the form
        mappedRoute[fieldDefinition.field_key] = customField.value
      }
    })
  }
  
  openDrawerForEdit(mappedRoute)
  page.props.loading = false
}

// Expose methods for parent components
defineExpose({
  openDrawerForCreate,
  openDrawerForEdit: async (route) => {
    page.props.loading = true
    // Ensure fleets are loaded before mapping
    if (fleets.value.length === 0) {
      await fetchFleets()
    }
    
    // Map fleet IDs to display names if they're not already mapped
    const mappedRoute = {
      ...route,
      fleet_ids: Array.isArray(route.fleet_ids) ? route.fleet_ids.map(fleetId => {
        // If it's already a string (display name), return as is
        if (typeof fleetId === 'string' && !fleets.value.find(f => f.id === fleetId)) {
          return fleetId
        }
        // If it's an ID, map to display name
        const fleetData = fleets.value.find(f => f.id === fleetId)
        return fleetData ? fleetData.name : null
      }).filter(name => name !== null) : route.fleet_ids,
      use_aircraft_rank: route.use_aircraft_rank !== false, // Default to true if not explicitly false
    }
    
    // Map custom fields from custom_fields array to direct properties
    if (route.custom_fields && Array.isArray(route.custom_fields)) {
      route.custom_fields.forEach(customField => {
        // Find the custom field definition to get the field_key
        const fieldDefinition = props.customFields.find(cf => cf.id === customField.field_id)
        if (fieldDefinition) {
          // Map the value to the field_key for the form
          mappedRoute[fieldDefinition.field_key] = customField.value
        }
      })
    }
    
    formMode.value = 'edit'
    formData.value = mappedRoute
    showDrawer.value = true
    page.props.loading = false
  }
})

// Add event listeners
onMounted(() => {
  window.addEventListener('open-edit-drawer', handleOpenEditDrawer)
})

onUnmounted(() => {
  window.removeEventListener('open-edit-drawer', handleOpenEditDrawer)
})
</script> 