<template>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">View Pilots</h1>
        <p class="text-sm text-gray-500">View and manage all pilots in your airline. Monitor performance, track progress, and oversee pilot operations.</p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-indigo-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Add Pilot
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
import { ref } from 'vue'
import { ImportIcon, UploadIcon, PlusIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'

// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Form fields structure
const formFields = ref([
  { name: 'name', label: 'Full Name', type: 'text', required: true },
  { name: 'callsign', label: 'Callsign', type: 'text', required: true },
  { name: 'email', label: 'Email', type: 'text', required: true },
  { name: 'rank_id', label: 'Rank', type: 'select', required: true, options: []}
])

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
    console.error('Error fetching ranks:', e)
  }
}

fetchRanks()

// Submit handler
const submitForm = async (payload) => {
  try {
    const response = await rotateDataService('/pilots/jxCreateEditPilot', payload)
    if (!response.hasErrors) {
      // Emit event to refresh pilots list
      window.dispatchEvent(new CustomEvent('pilots-updated'))
      showDrawer.value = false
    }
  } catch (e) {
    console.error(e)
  }
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