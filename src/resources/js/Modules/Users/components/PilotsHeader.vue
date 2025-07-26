<template>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-0">
      <div class="w-full sm:w-auto">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">View Pilots</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">View and manage all pilots in your airline. Monitor performance, track progress, and oversee pilot operations.</p>
      </div>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0" v-if="user.permissions.includes('create-user')">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-indigo-500 text-white font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto"
        >
          <PlusIcon class="w-4 h-4" /> <span>Add Pilot</span>
        </button>
        <button 
          @click="exportPilots"
          class="btn-white bg-white text-gray-800 font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto">
          <UploadIcon class="w-4 h-4" /> <span>Export</span>
        </button> 
      </div>

      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Pilot' : 'Edit Pilot'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { UploadIcon, PlusIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';
import { inject } from 'vue'

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

// Form fields structure
const formFields = ref([
  { name: 'name', label: 'Full Name', type: 'text', required: true },
  { name: 'callsign', label: 'Callsign', type: 'text', required: true },
  { name: 'email', label: 'Email', type: 'text', required: true },
  { name: 'rank_id', label: 'Rank', type: 'select', required: true, options: []},
  { name: 'role_id', label: 'Roles', type: 'select', required: true, options: []}
])

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

// Watch for custom fields changes and update form fields
watch(() => props.customFields, (newCustomFields) => {
  const baseFields = [
    { name: 'name', label: 'Full Name', type: 'text', required: true },
    { name: 'callsign', label: 'Callsign', type: 'text', required: true },
    { name: 'email', label: 'Email', type: 'text', required: true },
    { name: 'rank_id', label: 'Rank', type: 'select', required: true, options: []},
    { name: 'role_id', label: 'Role', type: 'select', required: true, options: []}
  ]
  
  const customFormFields = convertCustomFieldsToFormFields(newCustomFields)
  formFields.value = [...baseFields, ...customFormFields]
}, { immediate: true })

// Open drawer for create
const openDrawerForCreate = () => {
  formMode.value = 'create'
  formData.value = {} // reset
  showDrawer.value = true
}

// Fetch ranks
const fetchRanks = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchRanks')
    const rankOptions = response.data.map(rank => ({
      id: rank.id,
      name: rank.name
    }))
    
    // Update the rank field options in formFields
    const rankField = formFields.value.find(field => field.name === 'rank_id')
    if (rankField) {
      rankField.options = rankOptions
    }
  } catch (e) {
    console.error(e)
    showToast('Error fetching ranks', 'error')
  }
}

const fetchRoles = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchRoles')
    const roleOptions = response.data.map(role => ({
      id: role.id,
      name: role.name
    }))
    formFields.value.find(field => field.name === 'role_id').options = roleOptions
  } catch (e) {
    console.error(e)
    showToast('Error fetching roles', 'error')
  }
}

fetchRanks()
fetchRoles()

// Submit handler
const submitForm = async (payload) => {
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
    
    const response = await rotateDataService('/pilots/jxCreateEditPilot', finalPayload)
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred', 'error')
      return;
    }
    showToast(response.message || 'Pilot created successfully', 'success')
    window.dispatchEvent(new CustomEvent('pilots-updated'))
    showDrawer.value = false
}

// Export pilots
const exportPilots = () => {
  window.location.href = '/pilots/jxExportPilots'
}

// Expose methods for parent components
defineExpose({
  openDrawerForCreate,
  openDrawerForEdit: (pilot) => {
    formMode.value = 'edit'
    formData.value = { ...pilot }
    showDrawer.value = true
  }
})
</script> 