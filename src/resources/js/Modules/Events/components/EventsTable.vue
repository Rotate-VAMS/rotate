<template>
  <div class="relative overflow-x-auto shadow-lg sm:rounded-xl glassmorphism">
    <div class="flex justify-between items-center p-4">
      <input
        v-model="search"
        type="text"
        placeholder="Search events..."
        class="input border border-gray-300 rounded-md px-4 py-2 w-1/2"
      />
      <button class="btn-primary flex items-center gap-2 text-sm px-4 py-2 rounded-md bg-blue-100 text-blue-800">
        <FilterIcon class="w-4 h-4" /> Filters
      </button>
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
          <th class="px-6 py-3">Actions</th>
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
            <div class="text-sm">{{ event.aircraft }}</div>
          </td>
          <td class="px-6 py-4">
            <div v-if="event.cover_image" class="w-12 h-12 rounded-lg overflow-hidden">
              <img :src="event.cover_image" :alt="event.event_name" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-400 text-xs">No Image</span>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center gap-2">
              <button 
                @click="editEvent(event)"
                class="text-blue-600 hover:text-blue-800 p-1 rounded"
                title="Edit Event"
              >
                <EditIcon class="w-4 h-4" />
              </button>
              <button 
                @click="deleteEvent(event)"
                class="text-red-600 hover:text-red-800 p-1 rounded"
                title="Delete Event"
              >
                <TrashIcon class="w-4 h-4" />
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
import { FilterIcon, EditIcon, TrashIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'

const events = ref([])
const search = ref('')

// Computed property for filtered events
const filteredEvents = computed(() => {
  if (!search.value) return events.value
  
  return events.value.filter(event => 
    event.event_name?.toLowerCase().includes(search.value.toLowerCase()) ||
    event.event_description?.toLowerCase().includes(search.value.toLowerCase()) ||
    event.origin?.toLowerCase().includes(search.value.toLowerCase()) ||
    event.destination?.toLowerCase().includes(search.value.toLowerCase()) ||
    event.aircraft?.toLowerCase().includes(search.value.toLowerCase())
  )
})

// Format date time
const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString()
}

const fetchEvents = async () => {
  try {
    const response = await rotateDataService('/events/jxFetchEvents', { id: 1 }) // Send a default id if required by backend
    events.value = response.data || []
  } catch (e) {
    console.error(e)
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