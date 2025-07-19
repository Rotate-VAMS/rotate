<template>
  <div class="p-6 bg-white rounded-xl shadow-sm relative">
    <div class="flex flex-row justify-between items-center mb-2">
      <h2 class="text-lg font-semibold">Flight Types</h2>
      <button
        @click="openDrawerForCreate"
        class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
      >
        <PlusIcon class="w-4 h-4" /> Create New Flight Type
      </button>
    </div>
    <p>Manage your flight types and their configurations.</p>

    <!-- Ranks Card Grid -->
    <div v-if="flightTypes.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
      <div
        v-for="flightType in flightTypes"
        :key="flightType.id"
        class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col justify-between shadow-sm"
      >
        <div>
          <h3 class="font-semibold text-base mb-1">{{ flightType.flight_type }}</h3>
          <p class="text-gray-600 text-sm mb-4"><span class="font-bold">Multiplier:</span> {{ flightType.multiplier }}x</p>
        </div>
        <div class="flex gap-2">
          <button class="btn btn-sm flex items-center gap-2" @click="editFlightType(flightType)">
            <EditIcon class="w-4 h-4" />
            <span class="text-sm">Edit</span>
          </button>
          <button class="btn btn-sm flex items-center gap-2" @click="deleteFlightType(flightType)">
            <TrashIcon class="w-4 h-4" />
            <span class="text-sm">Delete</span>
          </button>
        </div>
      </div>
    </div>
    <div v-else class="text-gray-400 text-center mt-8">
      No flight types found.
    </div>

    <!-- Inject RotateFormComponent drawer -->
    <RotateFormComponent
      :visible="showDrawer"
      :title="formMode === 'create' ? 'Create Flight Type' : 'Edit Flight Type'"
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
import { PlusIcon, EditIcon, TrashIcon, SettingsIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'

// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Store fetched flight types
const flightTypes = ref([])

// Form fields structure
const formFields = [
  { name: 'flight_type', label: 'Flight Type', type: 'text', required: true },
  { name: 'multiplier', label: 'Multiplier', type: 'float', required: true },
]

// Open drawer for create
const openDrawerForCreate = () => {
  formMode.value = 'create'
  formData.value = {} // reset
  showDrawer.value = true
}

// Action button handlers
const editFlightType = (flightType) => {
  formMode.value = 'edit'
  formData.value = { ...flightType }
  showDrawer.value = true
}
const deleteFlightType = async (flightType) => {
  if (confirm(`Delete "${flightType.flight_type}"?`)) {
    const response = await rotateDataService('/settings/jxDeleteFlightType', { id: flightType.id })
    if (!response.hasErrors) {
      alert(response.message)
      fetchFlightTypes()
    }
  }
}

// Submit handler
const submitForm = async (payload) => {
  try {
    const response = await rotateDataService('/settings/jxCreateEditFlightType', payload)
    // Optional: show success toast, refresh list
    showDrawer.value = false
    fetchFlightTypes()
  } catch (e) {
    console.error(e)
  }
}

// Fetch flight types
const fetchFlightTypes = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchFlightTypes')
    flightTypes.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

fetchFlightTypes()
</script>