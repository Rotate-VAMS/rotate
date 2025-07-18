<template>
    <div class="p-6 bg-white rounded-xl shadow-sm relative">
      <div class="flex flex-row justify-between items-center mb-2">
        <h2 class="text-lg font-semibold">Fleet</h2>
        <button
          @click="openDrawerForCreate"
          class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Create New Fleet
        </button>
      </div>
      <p>Manage your fleet and their configurations.</p>
  
      <!-- Ranks Card Grid -->
      <div v-if="fleets.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        <div
          v-for="fleet in fleets"
          :key="fleet.id"
          class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col justify-between shadow-sm"
        >
          <div>
            <h3 class="font-semibold text-base mb-1">{{ fleet.livery }} - {{ fleet.aircraft }}</h3>
            <p class="text-gray-600 text-sm mb-4">{{ fleet.minimum_rank }}</p>
          </div>
          <div class="flex gap-2">
            <button class="btn btn-sm flex items-center gap-2" @click="editFleet(fleet)">
              <EditIcon class="w-4 h-4" />
              <span class="text-sm">Edit</span>
            </button>
            <button class="btn btn-sm flex items-center gap-2" @click="deleteFleet(fleet)">
              <TrashIcon class="w-4 h-4" />
              <span class="text-sm">Delete</span>
            </button>
          </div>
        </div>
      </div>
      <div v-else class="text-gray-400 text-center mt-8">
        No fleets found.
      </div>
  
      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Fleet' : 'Edit Fleet'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import { PlusIcon, EditIcon, TrashIcon, SettingsIcon } from 'lucide-vue-next'
  import RotateFormComponent from '@/Components/RotateFormComponent.vue'
  import rotateDataService from '@/rotate.js'
  
  // Drawer state
  const showDrawer = ref(false)
  const formMode = ref('create')
  const formData = ref({})
  
  // Store fetched fleets
  const fleets = ref([])
  const allFleets = ref([])
  const ranks = ref([])
  // Form fields structure with computed properties to include dynamic options
  const formFields = computed(() => {
    if (formMode.value === 'create') {
      return [
        { 
          name: 'selected_fleet', 
          label: 'Select Fleet Types', 
          type: 'multiselect', 
          required: true,
          options: allFleets.value,
          placeholder: 'Search and select fleet types...',
          searchPlaceholder: 'Search fleet types...'
        },
        { name: 'minimum_rank', label: 'Minimum Rank', type: 'select', required: true, options: ranks.value, placeholder: 'Select a rank...', value: 'id' },
      ]
    } else {
      // Edit mode - only minimum rank is editable
      return [
        { 
          name: 'fleet_name', 
          label: 'Fleet Name', 
          type: 'text', 
          required: true,
          readonly: true,
          disabled: true
        },
        { name: 'minimum_rank', label: 'Minimum Rank', type: 'select', required: true, options: ranks.value, placeholder: 'Select a rank...', value: 'id' },
      ]
    }
  })
  
  // Open drawer for create
  const openDrawerForCreate = () => {
    formMode.value = 'create'
    formData.value = {} // reset
    showDrawer.value = true
  }
  
  // Action button handlers
  const editFleet = (fleet) => {
    formMode.value = 'edit'
    formData.value = { 
      ...fleet,
      fleet_name: `${fleet.livery} - ${fleet.aircraft}`, // Create display name from livery and aircraft
      selected_fleet: fleet.selected_fleet || [] // Ensure selected_fleet is initialized
    }
    showDrawer.value = true
  }
  const deleteFleet = async (fleet) => {
    if (confirm(`Delete "${fleet.fleet_name}"?`)) {
      const response = await rotateDataService('/settings/jxDeleteFleet', { id: fleet.id })
      if (!response.hasErrors) {
        alert(response.message)
        fetchFleets()
      }
    }
  }
  
  // Submit handler
  const submitForm = async (payload) => {
    try {
      const response = await rotateDataService('/settings/jxCreateEditFleet', payload)
      // Optional: show success toast, refresh list
      showDrawer.value = false
      fetchFleets()
    } catch (e) {
      console.error(e)
    }
  }
  
  // Fetch fleets
  const fetchFleets = async () => {
    try {
      const response = await rotateDataService('/settings/jxFetchFleets')
      fleets.value = response.data || []
    } catch (e) {
      console.error(e)
    }
  }

  const fetchRanks = async () => {

    try {
      const response = await rotateDataService('/settings/jxFetchRanks')
      ranks.value = response.data || []
    } catch (e) {
      console.error(e)
    }
  }

  const fetchAllFleets = async () => {
    try {
      const response = await rotateDataService('/settings/jxFetchAllFleets')
      allFleets.value = response.data || []
    } catch (e) {
      console.error(e)
    }
  }
  fetchFleets()
  fetchAllFleets()
  fetchRanks()
  </script>