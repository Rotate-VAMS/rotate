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
        <button class="btn-white bg-white text-gray-800 font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto">
          <ImportIcon class="w-4 h-4" /> <span>Import</span>
        </button>
        <button class="btn-white bg-white text-gray-800 font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto">
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, inject } from 'vue'
import { ImportIcon, UploadIcon, PlusIcon } from 'lucide-vue-next'
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

// Store fetched data
const fleets = ref([])
const ranks = ref([])

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
    case 5: return 'date' // Date
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

// Fetch fleets
const fetchFleets = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchFleets')
    fleets.value = response.data.map(fleet => ({
      id: fleet.id,
      name: fleet.livery + ' - ' + fleet.aircraft
    })) || []
  } catch (e) {
    console.error('Error fetching fleets:', e)
  }
}

// Fetch ranks
const fetchRanks = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchRanks')
    ranks.value = response.data.map(rank => ({
      id: rank.id,
      name: rank.name
    })) || []
  } catch (e) {
    console.error('Error fetching ranks:', e)
  }
}

// Submit handler
const submitForm = async (payload) => {
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
}

// Initialize data
fetchFleets()
fetchRanks()

// Handle edit route event from table
const handleOpenEditDrawer = async (event) => {
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
}

// Expose methods for parent components
defineExpose({
  openDrawerForCreate,
  openDrawerForEdit: async (route) => {
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