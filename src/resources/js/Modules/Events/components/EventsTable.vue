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

    <table class="w-full text-sm text-left text-gray-700">
      <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-6 py-3">Event Name</th>
          <th class="px-6 py-3">Description</th>
          <th class="px-6 py-3">Date & Time</th>
          <th class="px-6 py-3">Route</th>
          <th class="px-6 py-3">Aircraft</th>
          <th class="px-6 py-3">Cover Image</th>
          <th v-for="customField in props.customFields" :key="customField.id" class="px-6 py-3">{{ customField.field_name }}</th>
          <th class="px-6 py-3" v-if="user.permissions.includes('edit-event') || user.permissions.includes('delete-event')">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="event in filteredEvents"
          :key="event.id"
          class="border-b hover:bg-gray-50"
        >
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="font-semibold">{{ event.event_name }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-gray-600 max-w-xs truncate">{{ event.event_description }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm">{{ formatDateTime(event.event_date_time) }}</div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm">
              <span class="font-medium">{{ event.origin }}</span>
              <span class="mx-2">→</span>
              <span class="font-medium">{{ event.destination }}</span>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm flex gap-1 overflow-x-auto max-w-xs whitespace-nowrap">
              <span v-for="aircraft in JSON.parse(event.aircraft)" :key="aircraft" class="bg-gray-200 text-xs rounded-full px-3 py-1 font-medium">
                {{ aircraft }}
              </span>
            </div>
          </td>
          <td class="px-6 py-4">
            <div v-if="event.cover_image" class="w-12 h-12 rounded-lg overflow-hidden">
              <img :src="event.cover_image" :alt="event.event_name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 text-xs">No Image</span>
            </div>
          </td>
          <td v-for="customField in props.customFields" :key="customField.id" class="px-6 py-4">
            <div class="text-sm">{{ getCustomFieldValue(event, customField.field_key) }}</div>
          </td>
          <td class="px-6 py-4" v-if="user.permissions.includes('edit-event') || user.permissions.includes('delete-event')">
            <div class="flex items-center gap-2">
              <button 
                v-if="user.permissions.includes('edit-event')"
                @click="editEvent(event)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Event"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                v-if="user.permissions.includes('delete-event')"
                @click="deleteEvent(event)"
                class="text-red-600 hover:text-red-800 p-1 rounded"
                title="Delete Event"
              >
                <TrashIcon class="w-4 h-4" />
              </button>
              <button 
                @click="event.attendees.includes(user.id) ? deregisterForEvent(event) : registerForEvent(event)"
                :class="event.attendees.includes(user.id) ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
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
import { FilterIcon, EditIcon, TrashIcon, TicketCheckIcon, TicketXIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'
import { usePage } from '@inertiajs/vue3';

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
  return new Date(dateTime*1000).toLocaleString()
}

const fetchEvents = async () => {
  try {
    const response = await rotateDataService('/events/jxFetchEvents') // Send a default id if required by backend
    events.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

const registerForEvent = async (event) => {
  const response = await rotateDataService('/events/jxRegisterForEvent', { id: event.id })
  if (!response.hasErrors) {
    alert(response.message || 'Event registered successfully')
    fetchEvents()
  } else {
    alert(response.message || 'Error occurred while registering for event')
  }
}

const deregisterForEvent = async (event) => {
  const response = await rotateDataService('/events/jxDeRegisterForEvent', { id: event.id })
  if (!response.hasErrors) {
    alert(response.message || 'Event deregistered successfully')
    fetchEvents()
  } else {
    alert(response.message || 'Error occurred while deregistering for event')
  }
}

// Action handlers
const editEvent = (event) => {
  // Emit event to parent component to open edit drawer
  window.dispatchEvent(new CustomEvent('edit-event', { detail: event }))
}

const deleteEvent = async (event) => {
  if (confirm(`Delete event "${event.event_name}"?`)) {
    try {
      const response = await rotateDataService('/events/jxDeleteEvent', { id: event.id })
      if (!response.hasErrors) {
        alert(response.message || 'Event deleted successfully')
        fetchEvents()
      } else {
        alert(response.message || 'Error occurred while deleting event')
      }
    } catch (e) {
      console.error(e)
      alert('Error occurred while deleting event')
    }
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