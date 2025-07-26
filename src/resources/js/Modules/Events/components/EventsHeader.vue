<template>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3 sm:gap-0">
      <div class="w-full sm:w-auto">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">View Events</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">View and manage all events in your airline. Monitor performance, track progress, and oversee event operations.</p>
      </div>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto mt-2 sm:mt-0" v-if="user.permissions.includes('create-event')">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold rounded-md px-4 py-2 flex items-center justify-center gap-2 w-full sm:w-auto"
        >
          <PlusIcon class="w-4 h-4" /> <span>Create New Event</span>
        </button>
      </div>

      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Event' : 'Edit Event'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { PlusIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';
import { inject } from 'vue'

const showToast = inject('showToast');
const page = usePage();
const user = page.props.auth.user;

// Document type constants (matching backend)
const _Documents = {
  DOCUMENT_TYPE_IMAGE: 1,
  DOCUMENT_TYPE_VIDEO: 2,
  DOCUMENT_TYPE_AUDIO: 3,
  DOCUMENT_TYPE_DOCUMENT: 4
}

// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Form fields structure based on backend validation
const formFields = ref([
  { name: 'event_name', label: 'Event Name', type: 'text', required: true },
  { name: 'event_description', label: 'Event Description', type: 'textarea', required: true },
  { name: 'event_date_time', label: 'Event Date & Time', type: 'datetime-local', required: true },
  { name: 'origin', label: 'Origin', type: 'text', required: true },
  { name: 'destination', label: 'Destination', type: 'text', required: true },
  { name: 'aircraft', label: 'Aircraft', type: 'multiselect', required: true, options: [] },
  { name: 'cover_image', label: 'Cover Image', type: 'file', required: false, acceptedTypes: 'image/jpeg,image/png,image/jpg,image/gif,image/svg+xml', maxSize: '2MB' }
])

const customFields = ref([])

const fetchCustomFields = async () => {
  try {
    const response = await rotateDataService('/events/jxFetchCustomFields')
    customFields.value = response.data || []
    injectCustomFieldsToForm()
  } catch (e) {
    console.error(e)
  }
}

const injectCustomFieldsToForm = () => {
  // Remove any previously injected custom fields
  formFields.value = formFields.value.filter(f => !f.isCustom)
  // Add custom fields
  customFields.value.forEach(field => {
    const formField = {
      name: field.field_key,
      label: field.field_name,
      type: getFieldType(field.data_type),
      required: field.is_required === 1,
      isCustom: true,
      description: field.field_description
    }
    if (field.data_type === 6) {
      formField.options = field.options || []
    }
    formFields.value.push(formField)
  })
}

const getFieldType = (dataType) => {
  switch (dataType) {
    case 1: return 'text'
    case 2: return 'number'
    case 3: return 'float'
    case 4: return 'checkbox'
    case 5: return 'date'
    case 6: return 'select'
    default: return 'text'
  }
}

onMounted(() => {
  fetchAllFleets()
  fetchCustomFields()
})

// Open drawer for create
const openDrawerForCreate = () => {
  formMode.value = 'create'
  formData.value = {} // reset
  showDrawer.value = true
}

// Submit handler
const submitForm = async (payload) => {
  try {
    // Convert datetime-local to proper date format for backend
    const processedPayload = { ...payload }
    if (processedPayload.event_date_time) {
      const date = new Date(processedPayload.event_date_time)
      processedPayload.event_date_time = date.toISOString()
    }
    if (formMode.value === 'edit' && formData.value.id) {
      processedPayload.id = formData.value.id
    }
    if (processedPayload.cover_image && processedPayload.cover_image instanceof File) {
      const file = processedPayload.cover_image
      const reader = new FileReader()
      reader.onload = async () => {
        const base64Data = reader.result.split(',')[1]
        processedPayload.cover_image = {
          document_name: file.name,
          document_type: _Documents.DOCUMENT_TYPE_IMAGE,
          document_data: base64Data
        }
        // Add custom fields to payload
        processedPayload.customData = extractCustomFieldData(payload)
        await sendRequest(processedPayload)
      }
      reader.readAsDataURL(file)
      return
    }
    // Add custom fields to payload
    processedPayload.customData = extractCustomFieldData(payload)
    await sendRequest(processedPayload)
  } catch (e) {
    console.error(e)
    showToast('An error occurred while saving the event.', 'error')
  }
}

const extractCustomFieldData = (payload) => {
  const customData = {}
  const customFieldKeys = customFields.value.map(field => field.field_key)
  Object.keys(payload).forEach(key => {
    if (customFieldKeys.includes(key)) {
      customData[key] = payload[key]
    }
  })
  return customData
}

// Helper function to send the actual request
const sendRequest = async (payload) => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/events/jxCreateEditEvent', payload)
    if (!response.hasErrors) {
      page.props.loading = false
      // Emit event to refresh events list
      window.dispatchEvent(new CustomEvent('events-updated'))
      showDrawer.value = false
    } else {
      page.props.loading = false
      // Handle validation errors
      console.error('Validation errors:', response.errors)
      showToast('Please check the form and try again.', 'error')
    }
  } catch (e) {
    console.error(e)
    showToast('An error occurred while saving the event.', 'error')
    page.props.loading = false
  }
}

const fetchAllFleets = async () => {
  page.props.loading = true
  const response = await rotateDataService('/settings/jxFetchAllFleets')
  formFields.value[5].options = response.data.map(fleet => fleet)
  page.props.loading = false
}

defineExpose({
  openDrawerForCreate,
  openDrawerForEdit: (event) => {
    formMode.value = 'edit'
    const editData = { ...event }
    editData.aircraft = JSON.parse(editData.aircraft)
    if (editData.event_date_time) {
      const date = new Date(editData.event_date_time * 1000)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      editData.event_date_time = `${year}-${month}-${day}T${hours}:${minutes}`
    }
    if (editData.id) {
      editData.cover_image = null
    }
    // Inject custom field values
    if (event.custom_fields && Array.isArray(event.custom_fields)) {
      event.custom_fields.forEach(field => {
        const fieldDef = customFields.value.find(cf => cf.id === field.field_id)
        if (fieldDef) {
          editData[fieldDef.field_key] = field.value
        }
      })
    }
    formData.value = editData
    showDrawer.value = true
  }
})
</script> 