<template>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">View Routes</h1>
        <p class="text-sm text-gray-500">View and manage all routes in your airline. Monitor performance, track progress, and oversee route operations.</p>
      </div>
      <div class="flex items-center gap-2">
        <button 
          @click="openDrawerForCreate"
          class="btn-primary bg-indigo-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Add Route
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { ImportIcon, UploadIcon, PlusIcon } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'

// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Store fetched data
const fleets = ref([])
const ranks = ref([])

// Computed form fields that update based on the toggle state
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

  return baseFields
})

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
  try {
    // Basic validation
    if (!payload.flight_number?.trim()) {
      alert('Please enter a flight number.')
      return
    }
    
    if (!payload.origin_icao?.trim()) {
      alert('Please enter an origin ICAO code.')
      return
    }
    
    if (!payload.origin_icao?.trim().match(/^[A-Z]{4}$/)) {
      alert('Origin ICAO code must be exactly 4 uppercase letters.')
      return
    }
    
    if (!payload.destination_icao?.trim()) {
      alert('Please enter a destination ICAO code.')
      return
    }
    
    if (!payload.destination_icao?.trim().match(/^[A-Z]{4}$/)) {
      alert('Destination ICAO code must be exactly 4 uppercase letters.')
      return
    }
    
    if (!payload.fleet_ids || payload.fleet_ids.length === 0) {
      alert('Please select at least one fleet.')
      return
    }

    // Validate rank selection based on mode
    if (formMode.value === 'create') {
      // In create mode, validate that rank is selected when toggle is off
      if (!payload.use_aircraft_rank && !payload.rank_id) {
        alert('Please select a rank for this route when "Take aircraft rank for route rank" is disabled.')
        return
      }
    } else {
      // In edit mode, rank is always required
      if (!payload.rank_id) {
        alert('Please select a rank for this route.')
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

    const response = await rotateDataService('/routes/jxCreateEditRoutes', payload)
    if (!response.hasErrors) {
      // Emit event to refresh routes list
      window.dispatchEvent(new CustomEvent('routes-updated'))
      showDrawer.value = false
    }
  } catch (e) {
    console.error(e)
  }
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