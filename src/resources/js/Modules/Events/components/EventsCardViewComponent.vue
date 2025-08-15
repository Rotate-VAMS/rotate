<template>
  <div class="relative">
    <!-- Search and View Controls -->
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

    <!-- Loading State -->
    <div v-if="loading" class="p-8 text-center text-gray-400">Loading events...</div>
    
    <!-- Events Content -->
    <div v-else>
      <!-- Upcoming Events Section -->
      <div v-if="upcomingEvents.length > 0" class="mb-8">
        <div class="flex items-center gap-3 mb-6 px-4">
          <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-800">Upcoming Events</h2>
          <div class="flex-1 h-px bg-gradient-to-r from-green-500 to-transparent"></div>
          <span class="text-sm text-gray-500 bg-green-100 px-3 py-1 rounded-full">{{ upcomingEvents.length }} upcoming</span>
        </div>
        
      <div v-if="viewMode === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        <div
            v-for="event in upcomingEvents"
          :key="event.id"
            class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col border border-gray-100 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 relative group"
        >
            <!-- Event Card Content -->
          <div class="relative h-48 w-full">
            <img
              v-if="event.cover_image"
              :src="event.cover_image"
              :alt="event.event_name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div v-else class="w-full h-full bg-gradient-to-br from-green-100 to-emerald-200 flex items-center justify-center">
                <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
              <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black via-black/70 to-transparent px-4 py-3">
              <div class="text-lg font-bold text-white truncate">{{ event.event_name }}</div>
                <div class="text-xs text-green-200 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  {{ formatDateTime(event.event_date_time) }}
            </div>
          </div>
              <!-- Status Badge -->
              <div class="absolute top-3 right-3 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-semibold">
                Upcoming
              </div>
            </div>
            
            <div class="p-4 flex-1 flex flex-col gap-2">
              <div class="text-sm text-gray-600 mb-1 line-clamp-2">{{ event.event_description }}</div>
            <div class="flex items-center gap-2 text-sm">
                <span class="font-medium text-gray-700">Departure:</span>
                <span class="text-gray-600">{{ event.origin_city }}</span>
                <span class="text-xs text-gray-500">({{ event.origin }})</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="font-medium text-gray-700">Arrival:</span>
                <span class="text-gray-600">{{ event.destination_city }}</span>
                <span class="text-xs text-gray-500">({{ event.destination }})</span>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span class="font-medium text-gray-700">Aircraft:</span>
                <div class="flex flex-wrap gap-1 max-w-xs">
                  <span v-for="aircraft in parseAircraft(event.aircraft)" :key="aircraft" :class="getEventAircraftPillClass(aircraft)" class="inline-flex items-center text-xs font-medium px-2 py-1 rounded-full text-white">{{ aircraft }}</span>
              </div>
            </div>
            <div v-for="customField in customFields" :key="customField.id" class="flex items-center gap-2 text-sm">
                <span class="font-medium text-gray-700">{{ customField.field_name }}:</span>
                <span class="bg-gray-100 text-gray-700 rounded-full px-2 py-1 text-xs font-semibold">{{ getCustomFieldValue(event, customField.field_key) }}</span>
              </div>
            </div>
            
            <div v-if="!event.completed && !event.pirep_filled" class="flex items-center gap-2 p-4 border-t bg-gradient-to-r from-green-50 to-emerald-50">
            <button
              @click="event.attendees.includes(user.id) ? deregisterForEvent(event) : registerForEvent(event)"
                class="flex items-center gap-2 text-sm font-medium transition-colors duration-200"
              :class="event.attendees.includes(user.id) ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800'"
            >
              <TicketCheckIcon v-if="!event.attendees.includes(user.id)" class="w-4 h-4" />
              <span v-if="!event.attendees.includes(user.id)">Register</span>
              <TicketXIcon v-else class="w-4 h-4" />
              <span v-if="event.attendees.includes(user.id)">Deregister</span>
            </button>
          </div>
          </div>
        </div>
      </div>

      <!-- Completed Events Section -->
      <div v-if="completedEvents.length > 0" class="mb-8">
        <div class="flex items-center gap-3 mb-6 px-4">
          <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-800">Completed Events</h2>
          <div class="flex-1 h-px bg-gradient-to-r from-purple-500 to-transparent"></div>
          <span class="text-sm text-gray-500 bg-purple-100 px-3 py-1 rounded-full">{{ completedEvents.length }} completed</span>
        </div>

        <!-- Collapsible Completed Events -->
        <div class="px-4">
          <button
            @click="toggleCompletedEvents"
            class="w-full bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4 hover:from-purple-100 hover:to-pink-100 transition-all duration-300 flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{ 'rotate-90': showCompletedEvents }">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
              <span class="font-semibold text-purple-800">View Completed Events</span>
            </div>
          </button>

          <!-- Slideshow Container -->
          <div v-if="showCompletedEvents" class="mt-4 transition-all duration-700 ease-in-out transform opacity-0 animate-fade-in">
            <div class="relative">
              <!-- Slideshow Controls -->
              <div class="flex justify-between items-center mb-4 md:flex-row flex-col">
                <button
                  @click="previousSlide"
                  class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                  </svg>
                  <span class="hidden md:block">Previous</span>
                </button>
                
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">{{ currentSlide + 1 }} of {{ completedEvents.length }}</span>
                    <div class="flex gap-1">
                      <div
                        v-for="(_, index) in completedEvents"
                        :key="index"
                        @click="goToSlide(index)"
                        class="w-2 h-2 rounded-full cursor-pointer transition-colors duration-200"
                        :class="index === currentSlide ? 'bg-purple-600' : 'bg-gray-300 hover:bg-gray-400'"
                      ></div>
                    </div>
                  </div>
                  

                </div>
                
                <button
                  @click="nextSlide"
                  class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200"
                >
                  <span class="hidden md:block">Next</span>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </div>

              <!-- Slideshow Content -->
              <div class="relative overflow-hidden rounded-xl bg-white shadow-lg">
                <div class="flex transition-transform duration-700 ease-in-out" :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
                  <div
                    v-for="event in completedEvents"
                    :key="event.id"
                    class="w-full flex-shrink-0"
                  >
                    <div class="flex flex-col lg:flex-row">
                      <!-- Event Image -->
                      <div class="lg:w-1/2 relative h-64 lg:h-auto">
                        <img
                          v-if="event.cover_image"
                          :src="event.cover_image"
                          :alt="event.event_name"
                          class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full bg-gradient-to-br from-purple-100 to-pink-200 flex items-center justify-center">
                          <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                        </div>
                        <div class="absolute top-4 right-4 bg-purple-600 text-white text-xs px-3 py-1 rounded-full font-semibold">
                          Completed
                        </div>
                      </div>
                      
                      <!-- Event Details -->
                      <div class="lg:w-1/2 p-6 flex flex-col justify-between">
                        <div>
                          <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ event.event_name }}</h3>
                          <p class="text-gray-600 mb-4 line-clamp-3">{{ event.event_description }}</p>
                          
                          <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                              <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                              <span class="text-gray-700">{{ formatDateTime(event.event_date_time) }}</span>
                            </div>
                            
                            <div class="flex items-center gap-3 text-sm">
                              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 0h10m-10 0a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2"></path>
                              </svg>
                              <span class="text-gray-700">{{ event.origin_city }} ({{ event.origin }}) → {{ event.destination_city }} ({{ event.destination }})</span>
                            </div>
                            
                            <div class="flex items-center gap-3 text-sm">
                              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                              </svg>
                              <div class="flex flex-wrap gap-1">
                                <span v-for="aircraft in parseAircraft(event.aircraft)" :key="aircraft" :class="getEventAircraftPillClass(aircraft)" class="inline-flex items-center text-xs font-medium px-2 py-1 rounded-full text-white">{{ aircraft }}</span>
                              </div>
                            </div>
                            
                            <div v-for="customField in customFields" :key="customField.id" class="flex items-center gap-3 text-sm">
                              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                              </svg>
                              <span class="text-gray-700">{{ customField.field_name }}: <span class="font-semibold">{{ getCustomFieldValue(event, customField.field_key) }}</span></span>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                          <div v-if="!event.pirep_filled && event.attendees.includes(user.id)" class="flex items-center gap-2">
            <button
              @click="fileEventPirep(event)"
                              class="flex items-center gap-2 text-sm font-medium text-green-600 hover:text-green-800 transition-colors duration-200"
            >
              <TicketCheckIcon class="w-4 h-4" />
              <span>File Pirep</span>
            </button>
                          </div>
                          <div v-else-if="event.pirep_filled && event.attendees.includes(user.id)" class="flex items-center gap-2">
                            <span class="flex items-center gap-2 text-sm font-medium text-green-600">
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                              <span>Pirep Filed</span>
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredEvents.length === 0" class="text-center py-12">
        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No events found</h3>
        <p class="text-gray-500">Try adjusting your search criteria or check back later for new events.</p>
      </div>

      <!-- Table View -->
      <EventsTable v-if="viewMode === 'grid'" :customFields="customFields" @update:analytics="updateAnalytics" />
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

// Helper function to filter events by search term
const filterEventsBySearch = (events) => {
  if (!search.value) return events
  const searchTerm = search.value.toLowerCase().trim()
  return events.filter(event => {
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
}

const filteredEvents = computed(() => {
  return filterEventsBySearch(events.value)
})

const completedEvents = computed(() => {
  return filterEventsBySearch(events.value.filter(event => event.completed))
})

const upcomingEvents = computed(() => {
  return filterEventsBySearch(events.value.filter(event => !event.completed))
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
    fetchEvents()
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

// Slideshow logic for completed events
const showCompletedEvents = ref(false)
const currentSlide = ref(0)

const toggleCompletedEvents = () => {
  showCompletedEvents.value = !showCompletedEvents.value
  if (showCompletedEvents.value) {
    currentSlide.value = 0
  }
}

const nextSlide = () => {
  if (currentSlide.value < completedEvents.value.length - 1) {
    currentSlide.value++
  } else {
    // Loop back to first slide
    currentSlide.value = 0
  }
}

const previousSlide = () => {
  if (currentSlide.value > 0) {
    currentSlide.value--
  } else {
    // Loop to last slide
    currentSlide.value = completedEvents.value.length - 1
  }
}

const goToSlide = (index) => {
  currentSlide.value = index
}



// Keyboard navigation
const handleKeydown = (event) => {
  if (!showCompletedEvents.value) return
  
  switch (event.key) {
    case 'ArrowLeft':
      event.preventDefault()
      previousSlide()
      break
    case 'ArrowRight':
      event.preventDefault()
      nextSlide()
      break
    case 'Escape':
      event.preventDefault()
      showCompletedEvents.value = false
      break
  }
}



onMounted(() => {
  fetchAll()
  window.addEventListener('events-updated', fetchEvents)
  window.addEventListener('edit-event', handleEditEvent)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('events-updated', fetchEvents)
  window.removeEventListener('edit-event', handleEditEvent)
  document.removeEventListener('keydown', handleKeydown)
})
</script> 

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.5s ease-out forwards;
}
</style> 