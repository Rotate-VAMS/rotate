<template>
  <div class="relative">
    <div class="flex justify-between items-center p-4">
      <div class="relative w-1/2" v-if="viewMode === 'card'">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <FilterIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="search"
          type="text"
          placeholder="Search events..."
          class="input border border-gray-300 rounded-md pl-10 pr-10 py-2 w-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <button
          v-if="search"
          @click="search = ''"
          class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div class="w-1/2" v-else>
        <!-- SPACER DIV -->
      </div>
      <div v-if="search" class="text-sm text-gray-500">
        {{ filteredEvents.length }} of {{ events.length }} events
      </div>
      <div class="flex items-center gap-2 ml-4">
        <button
          :class="viewMode === 'card' ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white' : 'bg-gray-200 text-gray-700'"
          class="px-3 py-1 rounded focus:outline-none"
          @click="setViewMode('card')"
        >
          Card View
        </button>
        <button
          :class="viewMode === 'grid' ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white' : 'bg-gray-200 text-gray-700'"
          class="px-3 py-1 rounded focus:outline-none"
          @click="setViewMode('grid')"
        >
          Grid View
        </button>
      </div>
    </div>
    <div v-if="loading" class="p-8 text-center text-gray-400">Loading events...</div>
    <div v-else>
      <div v-if="viewMode === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        <div
          v-for="event in filteredEvents"
          :key="event.id"
          class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col border border-gray-100 hover:shadow-2xl transition-shadow relative"
        >
          <div class="relative h-48 w-full">
            <img
              v-if="event.cover_image"
              :src="event.cover_image"
              :alt="event.event_name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center">
              <span class="text-gray-400 text-xs">No Image</span>
            </div>
            <div class="absolute bottom-0 left-0 w-full bg-black bg-opacity-60 px-4 py-2">
              <div class="text-lg font-bold text-white truncate">{{ event.event_name }}</div>
              <div class="text-xs text-gray-200">{{ formatDateTime(event.event_date_time) }}</div>
            </div>
          </div>
          <div class="p-4 flex-1 flex flex-col gap-2">
            <div class="text-sm text-gray-600 mb-1 truncate">{{ event.event_description }}</div>
            <div class="flex items-center gap-2 text-sm">
              <span class="font-medium">Departure:</span>
              <span>{{ event.origin_city }}</span>
              <span class="text-sm text-gray-700">({{ event.origin }})</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="font-medium">Arrival:</span>
              <span>{{ event.destination_city }}</span>
              <span class="text-sm text-gray-700">({{ event.destination }})</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="font-medium">Aircraft:</span>
              <div class="flex flex-wrap gap-2 max-w-xs overflow-x-auto">
                <span v-for="aircraft in parseAircraft(event.aircraft)" :key="aircraft" :class="getEventAircraftPillClass(aircraft)" class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full text-white">{{ aircraft }}</span>
              </div>
            </div>
            <div v-for="customField in customFields" :key="customField.id" class="flex items-center gap-2 text-sm">
              <span class="font-medium">{{ customField.field_name }}:</span>
              <span class="bg-gray-100 text-gray-700 rounded-full px-3 py-1 text-xs font-semibold">{{ getCustomFieldValue(event, customField.field_key) }}</span>
            </div>
          </div>
          <div v-if="!event.completed" class="flex items-center gap-2 p-4 border-t bg-gray-50">
            <button
              @click="event.attendees.includes(user.id) ? deregisterForEvent(event) : registerForEvent(event)"
              class="flex items-center gap-2 text-sm"
              :class="event.attendees.includes(user.id) ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
            >
              <TicketCheckIcon v-if="!event.attendees.includes(user.id)" class="w-4 h-4" />
              <span v-if="!event.attendees.includes(user.id)">Register</span>
              <TicketXIcon v-else class="w-4 h-4" />
              <span v-if="event.attendees.includes(user.id)">Deregister</span>
            </button>
          </div>
          <div v-else class="flex items-center gap-2 p-4 border-t bg-gray-50">
            <button
              @click="fileEventPirep(event)"
              class="flex items-center gap-2 text-sm text-green-600 hover:text-green-800"
            >
              <TicketCheckIcon class="w-4 h-4" />
              <span>File Pirep</span>
            </button>
          </div>
        </div>
      </div>
      <EventsTable v-else :customFields="customFields" @update:analytics="updateAnalytics" />
    </div>

    <!-- Event PIREP Form Drawer -->
    <RotateFormComponent
      :visible="showPirepDrawer"
      :title="`File PIREP for ${selectedEvent?.event_name || 'Event'}`"
      :fields="pirepFormFields"
      :initialData="pirepFormData"
      :isEditMode="false"
      @close="closePirepDrawer"
      @submit="submitEventPirep"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { FilterIcon, TicketCheckIcon, TicketXIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';
import EventsTable from './EventsTable.vue'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import { inject } from 'vue'

const showToast = inject('showToast');
const page = usePage();
const user = page.props.auth.user;

const events = ref([])
const customFields = ref([])
const loading = ref(true)
const search = ref('')
const viewMode = ref(localStorage.getItem('eventsViewMode') || 'card')
const emit = defineEmits(['update:analytics'])

// Event PIREP form state
const showPirepDrawer = ref(false)
const selectedEvent = ref(null)
const pirepFormData = ref({})
const flightTypes = ref([])
const pirepCustomFields = ref([])

// Computed form fields for event PIREP
const pirepFormFields = computed(() => {
  const baseFields = [
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

  // Add custom fields for PIREPs
  const customFormFields = pirepCustomFields.value.map(field => {
    const formField = {
      name: field.field_key,
      label: field.field_name,
      type: getFieldType(field.data_type),
      required: field.is_required === 1,
      description: field.field_description
    }
    
    // Add options for dropdown fields
    if (field.data_type === 6) { // Dropdown type
      if (Array.isArray(field.options)) {
        formField.options = field.options.map(opt => ({ id: opt, name: opt }))
      } else if (typeof field.options === 'object') {
        formField.options = Object.values(field.options).map(opt => ({ id: opt, name: opt }))
      } else {
        formField.options = []
      }
    }
    return formField
  })

  return [...baseFields, ...customFormFields]
})

// Function to map data types to form field types
const getFieldType = (dataType) => {
  switch (dataType) {
    case 1: return 'text' // Text
    case 2: return 'number' // Integer
    case 3: return 'number' // Float
    case 4: return 'checkbox' // Boolean
    case 5: return 'datetime-local' // Date
    case 6: return 'select' // Dropdown
    default: return 'text'
  }
}

const setViewMode = (mode) => {
  viewMode.value = mode
  localStorage.setItem('eventsViewMode', mode)
}

const filteredEvents = computed(() => {
  if (!search.value) return events.value
  const searchTerm = search.value.toLowerCase().trim()
  return events.value.filter(event => {
    if (event.event_name?.toLowerCase().includes(searchTerm)) return true
    if (event.event_description?.toLowerCase().includes(searchTerm)) return true
    if (event.origin?.toLowerCase().includes(searchTerm)) return true
    if (event.destination?.toLowerCase().includes(searchTerm)) return true
    if (event.aircraft) {
      try {
        const aircraftArray = JSON.parse(event.aircraft)
        if (Array.isArray(aircraftArray)) {
          return aircraftArray.some(aircraft => aircraft.toLowerCase().includes(searchTerm))
        }
      } catch (e) {
        if (event.aircraft.toLowerCase().includes(searchTerm)) return true
      }
    }
    return false
  })
})

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime*1000).toLocaleString()
}

const parseAircraft = (aircraft) => {
  try {
    return JSON.parse(aircraft)
  } catch {
    return aircraft ? [aircraft] : []
  }
}

const getEventAircraftPillClass = (aircraft) => {
  const gradients = [
    'bg-gradient-to-r from-indigo-500 to-purple-500',
    'bg-gradient-to-r from-blue-500 to-cyan-500',
    'bg-gradient-to-r from-green-500 to-emerald-500',
    'bg-gradient-to-r from-orange-500 to-red-500',
    'bg-gradient-to-r from-pink-500 to-rose-500',
    'bg-gradient-to-r from-yellow-500 to-orange-500',
    'bg-gradient-to-r from-teal-500 to-blue-500',
    'bg-gradient-to-r from-purple-500 to-pink-500',
    'bg-gradient-to-r from-red-500 to-pink-500',
    'bg-gradient-to-r from-cyan-500 to-blue-500',
    'bg-gradient-to-r from-emerald-500 to-teal-500',
    'bg-gradient-to-r from-violet-500 to-purple-500'
  ];
  
  // Assign classes randomly
  const hash = aircraft.split('').reduce((acc, char) => {
    return acc + char.charCodeAt(0);
  }, 0);
  
  return gradients[Math.abs(hash) % gradients.length];
}

const getCustomFieldValue = (event, fieldKey) => {
  if (event.custom_fields && Array.isArray(event.custom_fields)) {
    const customField = event.custom_fields.find(field => {
      const fieldDefinition = customFields.value.find(cf => cf.id === field.field_id)
      return fieldDefinition && fieldDefinition.field_key === fieldKey
    })
    if (customField) {
      return customField.value
    }
  }
  if (event[fieldKey]) {
    return event[fieldKey]
  }
  return '-'
}

const fetchCustomFields = async () => {
  try {
    const response = await rotateDataService('/events/jxFetchCustomFields')
    customFields.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

const fetchEvents = async () => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/events/jxFetchEvents')
    events.value = response.data || []
    emit('update:analytics', response.analytics || {})
    page.props.loading = false
  } catch (e) {
    console.error(e)
    page.props.loading = false
  }
}

const fetchFlightTypes = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchFlightTypes')
    flightTypes.value = response.data || []
  } catch (e) {
    console.error('Error fetching flight types:', e)
  }
}

const fetchPirepCustomFields = async () => {
  try {
    const response = await rotateDataService('/pireps/jxGetPirepCustomFields')
    pirepCustomFields.value = response.data || []
  } catch (e) {
    console.error('Error fetching PIREP custom fields:', e)
  }
}

const fetchAll = async () => {
  loading.value = true
  await Promise.all([fetchCustomFields(), fetchEvents(), fetchFlightTypes(), fetchPirepCustomFields()])
  loading.value = false
}

const registerForEvent = async (event) => {
  page.props.loading = true
  const response = await rotateDataService('/events/jxRegisterForEvent', { id: event.id })
  if (response.hasErrors) {
    page.props.loading = false
    showToast(response.message || 'Error occurred while registering for event', 'error')
    return;
  }
  showToast(response.message || 'Event registered successfully', 'success')
  fetchEvents()
  page.props.loading = false
}

const deregisterForEvent = async (event) => {
  page.props.loading = true
  const response = await rotateDataService('/events/jxDeRegisterForEvent', { id: event.id })
  if (response.hasErrors) {
    page.props.loading = false
    showToast(response.message || 'Error occurred while deregistering for event', 'error')
    return;
  }
  showToast(response.message || 'Event deregistered successfully', 'success')
  fetchEvents()
  page.props.loading = false
}

const handleEditEvent = (event) => {
  window.dispatchEvent(new CustomEvent('open-edit-drawer', { detail: event.detail }))
}

const fileEventPirep = async (event) => {
  selectedEvent.value = event
  pirepFormData.value = {
    event_id: event.id
  }
  showPirepDrawer.value = true
}

const closePirepDrawer = () => {
  showPirepDrawer.value = false
  selectedEvent.value = null
  pirepFormData.value = {}
}

const submitEventPirep = async (payload) => {
  try {
    page.props.loading = true
    
    // Basic validation
    if (!payload.flight_time_hours || payload.flight_time_hours < 0 || payload.flight_time_hours > 23) {
      showToast('Please enter a valid flight time hours (0-23).', 'error')
      page.props.loading = false
      return
    }
    
    if (!payload.flight_time_minutes || payload.flight_time_minutes < 0 || payload.flight_time_minutes > 59) {
      showToast('Please enter a valid flight time minutes (0-59).', 'error')
      page.props.loading = false
      return
    }
    
    if (!payload.flight_type_id) {
      showToast('Please select a flight type.', 'error')
      page.props.loading = false
      return
    }

    // Separate custom fields from regular fields
    const customData = {}
    const regularData = {}
    
    // Get custom field keys
    const customFieldKeys = pirepCustomFields.value.map(field => field.field_key)
    
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
      customData: customData,
      event_id: selectedEvent.value.id
    }

    const response = await rotateDataService('/events/jxFileEventPirep', finalPayload)
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred while filing pirep', 'error')
      page.props.loading = false
      return;
    }
    
    showToast(response.message || 'PIREP filed successfully', 'success')
    closePirepDrawer()
    page.props.loading = false
  } catch (e) {
    console.error(e)
    page.props.loading = false
    showToast('Error occurred while filing pirep', 'error')
  }
}

const updateAnalytics = (analytics) => {
  analytics.value = analytics || {}
}

onMounted(() => {
  fetchAll()
  window.addEventListener('events-updated', fetchEvents)
  window.addEventListener('edit-event', handleEditEvent)
})

onUnmounted(() => {
  window.removeEventListener('events-updated', fetchEvents)
  window.removeEventListener('edit-event', handleEditEvent)
})
</script> 