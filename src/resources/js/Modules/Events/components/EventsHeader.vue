<template>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">View Events</h1>
        <p class="text-sm text-gray-500">View and manage all events in your airline. Monitor performance, track progress, and oversee event operations.</p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Create New Event
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
import { ref } from 'vue'
import { PlusIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'

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
  { name: 'aircraft', label: 'Aircraft', type: 'text', required: true },
  { name: 'cover_image', label: 'Cover Image', type: 'file', required: false, acceptedTypes: 'image/jpeg,image/png,image/jpg,image/gif,image/svg+xml', maxSize: '2MB' }
])

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
      // Convert datetime-local to ISO string for backend
      const date = new Date(processedPayload.event_date_time)
      processedPayload.event_date_time = date.toISOString()
    }
    
    // Add event ID if in edit mode
    if (formMode.value === 'edit' && formData.value.id) {
      processedPayload.id = formData.value.id
    }
    
    // Handle file upload for cover_image
    if (processedPayload.cover_image && processedPayload.cover_image instanceof File) {
      // Convert File object to base64 and create document data structure
      const file = processedPayload.cover_image
      const reader = new FileReader()
      
      reader.onload = async () => {
        const base64Data = reader.result.split(',')[1] // Remove data:image/...;base64, prefix
        
        // Create document data structure as expected by _Documents.php
        processedPayload.cover_image = {
          document_name: file.name,
          document_type: _Documents.DOCUMENT_TYPE_IMAGE, // 1 for image
          document_data: base64Data
        }
        
        // Send the request with processed payload
        await sendRequest(processedPayload)
      }
      
      reader.readAsDataURL(file)
      return // Exit early, will continue in reader.onload
    }
    
    // If no file upload, send request directly
    await sendRequest(processedPayload)
  } catch (e) {
    console.error(e)
    alert('An error occurred while saving the event.')
  }
}

// Helper function to send the actual request
const sendRequest = async (payload) => {
  try {
    const response = await rotateDataService('/events/jxCreateEditEvent', payload)
    if (!response.hasErrors) {
      // Emit event to refresh events list
      window.dispatchEvent(new CustomEvent('events-updated'))
      showDrawer.value = false
    } else {
      // Handle validation errors
      console.error('Validation errors:', response.errors)
      alert('Please check the form and try again.')
    }
  } catch (e) {
    console.error(e)
    alert('An error occurred while saving the event.')
  }
}

// Expose methods for parent components
defineExpose({
  openDrawerForCreate,
  openDrawerForEdit: (event) => {
    formMode.value = 'edit'
    const editData = { ...event }
    
    // Convert backend date format to datetime-local format for editing
    if (editData.event_date_time) {
      const date = new Date(editData.event_date_time * 1000) // Convert timestamp to date
      // Format for datetime-local input (YYYY-MM-DDTHH:MM)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      editData.event_date_time = `${year}-${month}-${day}T${hours}:${minutes}`
    }
    
    // Handle cover image for edit - we'll need to fetch the document if it exists
    if (editData.id) {
      // For now, we'll clear the cover_image field in edit mode
      // The user can re-upload if needed
      editData.cover_image = null
    }
    
    formData.value = editData
    showDrawer.value = true
  }
})
</script> 