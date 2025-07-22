<template>
  <div class="p-6 bg-white rounded-xl shadow-sm relative">
    <div class="flex flex-row justify-between items-center mb-2">
      <h2 class="text-lg font-semibold">Custom Field Configuration</h2>
      <button
        @click="openDrawerForCreate"
        class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
      >
        <PlusIcon class="w-4 h-4" /> Create New Field
      </button>
    </div>
    <p>Manage your custom fields and their configurations.</p>

    <!-- Custom Fields Card Grid -->
    <div v-if="customFields.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
      <div
        v-for="field in customFields"
        :key="field.id"
        class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col justify-between shadow-sm"
      >
        <div>
          <h3 class="font-semibold text-base mb-1">{{ field.field_name }}</h3>
          <p class="text-gray-600 text-sm mb-4">{{ field.field_description }}</p>
        </div>
        <div class="flex gap-2">
          <button class="btn btn-sm flex items-center gap-2" @click="editField(field)">
            <EditIcon class="w-4 h-4" />
            <span class="text-sm">Edit</span>
          </button>
          <button v-if="field.data_type === 6" class="btn btn-sm flex items-center gap-2" @click="configureField(field)">
            <SettingsIcon class="w-4 h-4" />
            <span class="text-sm">Configure</span>
          </button>
          <button class="btn btn-sm flex items-center gap-2" @click="deleteField(field)">
            <TrashIcon class="w-4 h-4" />
            <span class="text-sm">Delete</span>
          </button>
        </div>
      </div>
    </div>
    <div v-else class="text-gray-400 text-center mt-8">
      No custom fields found.
    </div>

    <!-- Inject RotateFormComponent drawer -->
    <RotateFormComponent
      :visible="showDrawer"
      :title="formMode === 'create' ? 'Create Custom Field' : 'Edit Custom Field'"
      :fields="formFields"
      :initialData="formData"
      :isEditMode="formMode === 'edit'"
      @close="showDrawer = false"
      @submit="submitForm"
    />

    <!-- Configure Drawer -->
    <div v-if="showConfigureDrawer" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
      <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
        <div class="w-screen max-w-md">
          <CustomFieldDropdownConfigureComponent
            :field="selectedFieldForConfigure"
            :visible="showConfigureDrawer"
            @close="showConfigureDrawer = false"
            @save="handleConfigureSave"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { PlusIcon, EditIcon, TrashIcon, SettingsIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import CustomFieldDropdownConfigureComponent from './CustomFieldDropdownConfigureComponent.vue'
import rotateDataService from '@/rotate.js'

// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Configure drawer state
const showConfigureDrawer = ref(false)
const selectedFieldForConfigure = ref(null)

// Store fetched custom fields
const customFields = ref([])

// Form fields structure
const formFields = [
  { name: 'field_name', label: 'Field Name', type: 'text', required: true },
  { name: 'field_description', label: 'Field Description', type: 'text', required: true },
  { name: 'data_type', label: 'Data Type', type: 'select', required: true, options: [{id:1, name:'Text'}, {id:2, name:'Integer (Eg.1, 2, 3)'}, {id:3, name:'Float (Eg. 1.1, 2.2, 3.3)'}, {id:4, name:'Boolean (Eg. true, false)'}, {id:5, name:'Date (Eg. 2021-01-01)'}, {id:6, name:'Dropdown'}] },
  { name: 'source_type', label: 'Source', type: 'select', required: true, options: [{id:1, name:'Pilots'}, {id:2, name:'PIREPs'}, {id:3, name:'Events'}, {id:4, name:'Routes'}] },
  { name: 'is_required', label: 'Is Required', type: 'checkbox', required: true },
]

// Open drawer for create
const openDrawerForCreate = () => {
  formMode.value = 'create'
  formData.value = {} // reset
  showDrawer.value = true
}

// Action button handlers
const editField = (field) => {
  formMode.value = 'edit'
  formData.value = { ...field }
  formData.value.is_required = formData.value.is_required === 1 ? true : false
  showDrawer.value = true
}
const configureField = (field) => {
  selectedFieldForConfigure.value = field
  showConfigureDrawer.value = true
}
const deleteField = async (field) => {
  if (confirm(`Delete "${field.field_name}"?`)) {
    const response = await rotateDataService('/settings/jxDeleteCustomField', { id: field.id })
    if (!response.hasErrors) {
      alert(response.message)
      fetchCustomFields()
    }
  }
}

// Submit handler
const submitForm = async (payload) => {
  try {
    payload.is_required = payload.is_required === true ? 1 : 0
    const response = await rotateDataService('/settings/jxCreateEditCustomFields', payload)
    // Optional: show success toast, refresh list
    showDrawer.value = false
    fetchCustomFields()
  } catch (e) {
    console.error(e)
  }
}

// Handle configure save
const handleConfigureSave = async (configuration) => {
  try {
    // Here you would call your backend API to save the configuration
    console.log('Saving configuration:', configuration)
    
    // For now, just close the drawer and show a success message
    showConfigureDrawer.value = false
    alert('Configuration saved successfully!')
    
    // Optionally refresh the custom fields list
    // fetchCustomFields()
  } catch (e) {
    console.error(e)
    alert('Failed to save configuration')
  }
}

// Fetch custom fields
const fetchCustomFields = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchCustomFields')
    customFields.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

fetchCustomFields()
</script>