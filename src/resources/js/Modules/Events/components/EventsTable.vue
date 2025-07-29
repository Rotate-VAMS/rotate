<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <div class="relative w-1/2">
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
      <div v-if="search" class="text-sm text-gray-500">
        {{ filteredEvents.length }} of {{ events.length }} events
      </div>
    </div>

    <table class="w-full text-sm text-left text-gray-700 overflow-x-auto">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-4">
            <CalendarDays class="w-4 h-4 inline-block mr-2" />
            Event Name
          </th>
          <th class="px-6 py-4">
            <TextIcon class="w-4 h-4 inline-block mr-2" />
            Description
          </th>
          <th class="px-6 py-4">
            <ClockIcon class="w-4 h-4 inline-block mr-2" />
            Date & Time
          </th>
          <th class="px-6 py-4">
            <PlaneTakeoff class="w-4 h-4 inline-block mr-2" />
            Origin
          </th>
          <th class="px-6 py-4">
            <PlaneLanding class="w-4 h-4 inline-block mr-2" />
            Destination
          </th>
          <th class="px-6 py-4">
            <PlaneIcon class="w-4 h-4 inline-block mr-2" />
            Aircraft
          </th>
          <th class="px-6 py-4">
            <ImageIcon class="w-4 h-4 inline-block mr-2" />
            Cover
          </th>
          <th v-for="customField in props.customFields" :key="customField.id" class="px-6 py-4">{{ customField.field_name }}</th>
          <th class="px-6 py-4" v-if="user.permissions.includes('edit-event') || user.permissions.includes('delete-event')">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="event in filteredEvents"
          :key="event.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-6 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-rose-500 to-purple-500 text-white flex items-center justify-center">
                <CalendarDays class="w-5 h-5" />
              </div>
              <div class="font-bold text-gray-900">{{ event.event_name }}</div>
            </div>
          </td>
          <td class="px-6 py-6">
            <div v-if="!expandDescription" class="text-sm text-gray-600 max-w-xs truncate cursor-pointer hover:text-gray-800" @click="expandDescription = !expandDescription">{{ event.event_description }}</div>
            <div v-if="expandDescription" class="text-sm text-gray-600 cursor-pointer hover:text-gray-800" @click="expandDescription = !expandDescription">{{ event.event_description }}</div>
          </td>
          <td class="px-6 py-6">
            <div class="text-sm text-gray-700">{{ formatDateTime(event.event_date_time) }}</div>
          </td>
          <td class="px-6 py-6">
            <div class="text-semibold text-gray-700">{{ event.origin_city }}</div>
            <div class="text-sm text-gray-700">({{ event.origin }})</div>
          </td>
          <td class="px-6 py-6">
            <div class="text-semibold">{{ event.destination_city }}</div>
            <div class="text-sm text-gray-700">({{ event.destination }})</div>
          </td>
          <td class="px-6 py-6">
            <div class="text-sm flex flex-wrap gap-2 max-w-xs overflow-x-auto whitespace-nowrap">
              <span v-for="aircraft in JSON.parse(event.aircraft)" :key="aircraft" :class="getEventAircraftPillClass(aircraft)" class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full text-white">
                {{ aircraft }}
              </span>
            </div>
          </td>
          <td class="px-6 py-6">
            <div v-if="event.cover_image" class="w-14 h-14 rounded-lg overflow-hidden">
              <img :src="event.cover_image" :alt="event.event_name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-14 h-14 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 text-xs">No Image</span>
            </div>
          </td>
          <td v-for="customField in props.customFields" :key="customField.id" class="px-6 py-6">
            <div class="text-sm text-gray-700">{{ getCustomFieldValue(event, customField.field_key) }}</div>
          </td>
          <td class="px-6 py-6" v-if="user.permissions.includes('edit-event') || user.permissions.includes('delete-event')">
            <div class="flex items-center gap-3">
              <button 
                v-if="user.permissions.includes('edit-event')"
                @click="editEvent(event)"
                class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors"
                title="Edit Event"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                v-if="user.permissions.includes('delete-event')"
                @click="deleteEvent(event)"
                class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors"
                title="Delete Event"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
              <button 
                @click="event.attendees.includes(user.id) ? deregisterForEvent(event) : registerForEvent(event)"
                :class="event.attendees.includes(user.id) ? 'text-red-600 hover:text-red-800 hover:bg-red-50' : 'text-green-600 hover:text-green-800 hover:bg-green-50'"
                class="p-2 rounded-lg transition-colors"
                :title="event.attendees.includes(user.id) ? 'Deregister for Event' : 'Register for Event'"
              >
                <TicketCheckIcon v-if="!event.attendees.includes(user.id)" class="w-4 h-4" />
                <TicketXIcon v-else class="w-4 h-4" />
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { FilterIcon, EditIcon, TrashIcon, TicketCheckIcon, TicketXIcon, ImageIcon, Route, CalendarDays, PlaneIcon, ClockIcon, TextIcon, PlaneTakeoff, PlaneLanding } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';
import { inject } from 'vue'

const showToast = inject('showToast');
const page = usePage();
const user = page.props.auth.user;

// Accept customFields as a prop
const props = defineProps({
  customFields: {
    type: Array,
    default: () => []
  }
})

const events = ref([])
const search = ref('')
const emit = defineEmits(['update:analytics'])
const expandDescription = ref(false)

// Computed property for filtered events
const filteredEvents = computed(() => {
  if (!search.value) return events.value
  
  const searchTerm = search.value.toLowerCase().trim()
  
  return events.value.filter(event => {
    // Search in event name
    if (event.event_name?.toLowerCase().includes(searchTerm)) return true
    
    // Search in event description
    if (event.event_description?.toLowerCase().includes(searchTerm)) return true
    
    // Search in origin
    if (event.origin?.toLowerCase().includes(searchTerm)) return true
    
    // Search in destination
    if (event.destination?.toLowerCase().includes(searchTerm)) return true
    
    // Search in aircraft (handle JSON parsing)
    if (event.aircraft) {
      try {
        const aircraftArray = JSON.parse(event.aircraft)
        if (Array.isArray(aircraftArray)) {
          return aircraftArray.some(aircraft => 
            aircraft.toLowerCase().includes(searchTerm)
          )
        }
      } catch (e) {
        // If JSON parsing fails, search in the raw string
        if (event.aircraft.toLowerCase().includes(searchTerm)) return true
      }
    }
    
    return false
  })
})

// Helper to get custom field value from event
const getCustomFieldValue = (event, fieldKey) => {
  if (event.custom_fields && Array.isArray(event.custom_fields)) {
    const customField = event.custom_fields.find(field => {
      const fieldDefinition = props.customFields.find(cf => cf.id === field.field_id)
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

// Format date time
const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime*1000).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getEventAircraftPillClass = (eventAircraft) => {
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
  const hash = eventAircraft.split('').reduce((acc, char) => {
    return acc + char.charCodeAt(0);
  }, 0);
  
  return gradients[Math.abs(hash) % gradients.length];
};

const fetchEvents = async () => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/events/jxFetchEvents') // Send a default id if required by backend
    events.value = response.data || []
    emit('update:analytics', response.analytics || {})
    page.props.loading = false
  } catch (e) {
    console.error(e)
    page.props.loading = false
  }
}

const registerForEvent = async (event) => {
  page.props.loading = true
  const response = await rotateDataService('/events/jxRegisterForEvent', { id: event.id })
  if (response.hasErrors) {
    showToast(response.message || 'Error occurred while registering for event', 'error')
    page.props.loading = false
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
    showToast(response.message || 'Error occurred while deregistering for event', 'error')
    page.props.loading = false
    return;
  }
  showToast(response.message || 'Event deregistered successfully', 'success')
  fetchEvents()
  page.props.loading = false
}

// Action handlers
const editEvent = (event) => {
  // Emit event to parent component to open edit drawer
  window.dispatchEvent(new CustomEvent('edit-event', { detail: event }))
}

const deleteEvent = async (event) => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/events/jxDeleteEvent', { id: event.id })
    if (response.hasErrors) {
      showToast(response.message || 'Error occurred while deleting event', 'error')
      page.props.loading = false
      return;
    }
    showToast(response.message || 'Event deleted successfully', 'success')
    fetchEvents()
    page.props.loading = false
    } catch (e) {
      console.error(e)
      showToast('Error occurred while deleting event', 'error')
      page.props.loading = false
    }
}

// Event listeners
const handleEventsUpdated = () => {
  fetchEvents()
}

const handleEditEvent = (event) => {
  // This will be handled by the parent component
  window.dispatchEvent(new CustomEvent('open-edit-drawer', { detail: event.detail }))
}

onMounted(() => {
  fetchEvents()
  window.addEventListener('events-updated', handleEventsUpdated)
  window.addEventListener('edit-event', handleEditEvent)
})

onUnmounted(() => {
  window.removeEventListener('events-updated', handleEventsUpdated)
  window.removeEventListener('edit-event', handleEditEvent)
})
</script>