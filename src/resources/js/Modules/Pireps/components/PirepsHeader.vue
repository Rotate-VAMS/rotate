<template>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">View Pireps</h1>
        <p class="text-sm text-gray-500">View and manage all pireps in your airline. Monitor performance, track progress, and oversee pireps operations.</p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-indigo-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Add Pirep
        </button>
        <button class="btn-white bg-white text-gray-800 text-bold rounded-md px-4 py-2 flex items-center gap-2">
          <ImportIcon class="w-4 h-4" /> Import
        </button>
        <button class="btn-white bg-white text-gray-800 text-bold rounded-md px-4 py-2 flex items-center gap-2">
          <UploadIcon class="w-4 h-4" /> Export
        </button> 
      </div>

      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Pirep' : 'Edit Pirep'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { ImportIcon, UploadIcon, PlusIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'

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
const routes = ref([])
const flightTypes = ref([])

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
    { 
      name: 'route_id', 
      label: 'Route', 
      type: 'searchable-select', 
      required: true, 
      options: routes.value.map(route => ({ id: route.id, name: route.flight_number + ' - ' + route.route })),
      placeholder: 'Search routes...',
      searchPlaceholder: 'Search routes...'
    },
    {
      group: 'Flight Time',
      fields: [
        { name: 'flight_time_hours', label: 'Hours', type: 'number', required: true, min: 0, max: 23 },
        { name: 'flight_time_minutes', label: 'Minutes', type: 'number', required: true, min: 0, max: 59 },
        ]
    },
    { 
      name: 'flight_type_id', 
      label: 'Flight Type', 
      type: 'select', 
      required: true, 
      options: flightTypes.value.map(type => ({ id: type.id, name: type.flight_type + ' (' + type.multiplier + 'x)' }))
    },
  ]

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
  formData.value = {}
  showDrawer.value = true
}

// Fetch fleets
const fetchFleets = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchFleets')
    fleets.value = response.data || []
  } catch (e) {
    console.error('Error fetching fleets:', e)
  }
}

// Fetch routes
const fetchRoutes = async () => {
  try {
    const response = await rotateDataService('/routes/jxFetchRoutes', { scope: 'pireps' })
    routes.value = response.data || []
  } catch (e) {
    console.error('Error fetching routes:', e)
  }
}

// Fetch flight types
const fetchFlightTypes = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchFlightTypes')
    flightTypes.value = response.data || []
  } catch (e) {
    console.error('Error fetching flight types:', e)
  }
}

// Submit handler
const submitForm = async (payload) => {
  try {
    // Basic validation
    if (!payload.route_id) {
      alert('Please select a route.')
      return
    }
    
    // Validate flight time fields
    if (!payload.flight_time_hours || payload.flight_time_hours < 0 || payload.flight_time_hours > 23) {
      alert('Please enter a valid flight time hours (0-23).')
      return
    }
    
    if (!payload.flight_time_minutes || payload.flight_time_minutes < 0 || payload.flight_time_minutes > 59) {
      alert('Please enter a valid flight time minutes (0-59).')
      return
    }
    
    if (!payload.flight_type_id) {
      alert('Please select a flight type.')
      return
    }

    // Convert flight time to total minutes for backend
    const finalPayload = {
      ...payload,
      flight_time_hours: payload.flight_time_hours,
      flight_time_minutes: payload.flight_time_minutes
    }

    // Separate custom fields from regular fields
    const customData = {}
    const regularData = {}
    
    // Get custom field keys
    const customFieldKeys = props.customFields.map(field => field.field_key)
    
    // Separate the data
    Object.keys(finalPayload).forEach(key => {
      if (customFieldKeys.includes(key)) {
        customData[key] = finalPayload[key]
      } else {
        regularData[key] = finalPayload[key]
      }
    })
    
    // Add customData to the regular payload
    const finalSubmitPayload = {
      ...regularData,
      customData: customData
    }

    const response = await rotateDataService('/pireps/jxCreateEditPirep', finalSubmitPayload)
    if (!response.hasErrors) {
      // Emit event to refresh pireps list
      window.dispatchEvent(new CustomEvent('pireps-updated'))
      showDrawer.value = false
    }
  } catch (e) {
    console.error(e)
  }
}

// Initialize data
fetchFleets()
fetchRoutes()
fetchFlightTypes()

// Handle edit pirep event from table
const handleOpenEditDrawer = async (event) => {
  const pirep = event.detail
  
  // Ensure data is loaded before mapping
  if (fleets.value.length === 0) {
    await fetchFleets()
  }
  if (routes.value.length === 0) {
    await fetchRoutes()
  }
  if (flightTypes.value.length === 0) {
    await fetchFlightTypes()
  }
  
  // Map pirep data for the form
  const mappedPirep = {
    ...pirep,
    route_id: pirep.route_id,
    flight_time: pirep.flight_time,
    flight_time_hours: Math.floor(pirep.flight_time / 60), // Map total minutes to hours
    flight_time_minutes: pirep.flight_time % 60, // Map total minutes to minutes
    flight_type_id: pirep.flight_type_id,
  }
  
  // Map custom fields from custom_fields array to direct properties
  if (pirep.custom_fields && Array.isArray(pirep.custom_fields)) {
    pirep.custom_fields.forEach(customField => {
      // Find the custom field definition to get the field_key
      const fieldDefinition = props.customFields.find(cf => cf.id === customField.field_id)
      if (fieldDefinition) {
        // Map the value to the field_key for the form
        mappedPirep[fieldDefinition.field_key] = customField.value
      }
    })
  }
  
  openDrawerForEdit(mappedPirep)
}

// Expose methods for parent components
defineExpose({
  openDrawerForCreate,
  openDrawerForEdit: async (pirep) => {
    // Ensure data is loaded before mapping
    if (fleets.value.length === 0) {
      await fetchFleets()
    }
    if (routes.value.length === 0) {
      await fetchRoutes()
    }
    if (flightTypes.value.length === 0) {
      await fetchFlightTypes()
    }
    
    // Map pirep data for the form
    const mappedPirep = {
      ...pirep,
      route_id: pirep.route_id,
      flight_time: pirep.flight_time,
      flight_time_hours: Math.floor(pirep.flight_time / 60), // Map total minutes to hours
      flight_time_minutes: pirep.flight_time % 60, // Map total minutes to minutes
      flight_type_id: pirep.flight_type_id,
    }
    
    // Map custom fields from custom_fields array to direct properties
    if (pirep.custom_fields && Array.isArray(pirep.custom_fields)) {
      pirep.custom_fields.forEach(customField => {
        // Find the custom field definition to get the field_key
        const fieldDefinition = props.customFields.find(cf => cf.id === customField.field_id)
        if (fieldDefinition) {
          // Map the value to the field_key for the form
          mappedPirep[fieldDefinition.field_key] = customField.value
        }
      })
    }
    
    formMode.value = 'edit'
    formData.value = mappedPirep
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