<template>
  <div v-if="tenant.available_features.includes('leaderboard')" class="bg-white rounded-xl shadow-xl border border-gray-200 p-4 sm:p-6 glassmorphism">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-500 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Pilot Leaderboard</h2>
        </div>
        <p class="text-sm sm:text-base text-gray-600">Top performing pilots ranked by activity and performance</p>
      </div>
    </div>

    <!-- Leaderboard Entries -->
    <div v-if="leaderboardData.length > 0" class="space-y-3 mb-6">
      <div 
        v-for="(pilot, index) in leaderboardData" 
        :key="pilot.id"
        :class="[
          'p-3 sm:p-4 rounded-lg shadow-sm transition-all hover:shadow-md',
          index < 3 ? 'bg-yellow-50 border border-yellow-200' : 'bg-white border border-gray-100'
        ]"
      >
        <!-- Mobile Layout -->
        <div class="block sm:hidden">
          <div class="flex items-center mb-3">
            <!-- Rank/Medal -->
            <div class="flex-shrink-0 mr-3">
              <div v-if="index === 0" class="w-6 h-6 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-full flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <div v-else-if="index === 1" class="w-6 h-6 bg-gradient-to-r from-gray-400 to-gray-500 rounded-full flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <div v-else-if="index === 2" class="w-6 h-6 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <div v-else class="w-6 h-6 bg-gradient-to-r from-gray-200 to-gray-300 rounded-full flex items-center justify-center shadow-sm">
                <span class="text-xs font-medium text-gray-600">#{{ index + 1 }}</span>
              </div>
            </div>

            <!-- Profile Picture -->
            <div class="flex-shrink-0 mr-3 relative">
              <div class="bg-gradient-to-br from-indigo-500 via-purple-500 to-indigo-500 text-white shadow-md rounded-xl h-8 w-8 flex items-center justify-center text-sm font-bold">
                {{ pilot.user_name.charAt(0) }}
              </div>
              <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
            </div>

            <!-- Pilot Info -->
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="font-semibold text-gray-900 text-sm">{{ pilot.user_name }}</h3>
                <span 
                  :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    getTierBadgeClass(index)
                  ]"
                >
                  {{ getTierName(index) }}
                </span>
              </div>
              <p class="text-xs text-gray-600">{{ pilot.callsign }}</p>
            </div>
          </div>

          <!-- Mobile Performance Metrics -->
          <div class="grid grid-cols-3 gap-2">
            <div class="text-center bg-gray-50 rounded-lg p-2">
              <div class="text-sm font-bold text-gray-900">{{ pilot.total_flights }}</div>
              <div class="text-xs text-gray-600">Flights</div>
            </div>
            <div class="text-center bg-gray-50 rounded-lg p-2">
              <div class="text-sm font-bold text-gray-900">{{ formatFlightTime(pilot.flying_hours) }}</div>
              <div class="text-xs text-gray-600">Hours</div>
            </div>
            <div class="text-center bg-gray-50 rounded-lg p-2">
              <div class="text-sm font-bold text-purple-600">{{ formatNumber(pilot.points) }}</div>
              <div class="text-xs text-gray-600">Points</div>
            </div>
          </div>
        </div>

        <!-- Desktop Layout -->
        <div class="hidden sm:flex items-center">
          <!-- Rank/Medal -->
          <div class="flex-shrink-0 mr-4">
            <div v-if="index === 0" class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <div v-else-if="index === 1" class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <div v-else-if="index === 2" class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <div v-else class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
              <span class="text-sm font-medium text-gray-600">#{{ index + 1 }}</span>
            </div>
          </div>

          <!-- Profile Picture -->
          <div class="flex-shrink-0 mr-4 relative">
            <div class="bg-gradient-to-br from-indigo-500 via-purple-500 to-indigo-500 text-white shadow-md rounded-xl h-10 w-10 flex items-center justify-center text-lg font-bold">
              {{ pilot.user_name.charAt(0) }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
          </div>

          <!-- Pilot Info -->
          <div class="flex-1 mr-4">
            <div class="flex items-center gap-2 mb-1">
              <h3 class="font-semibold text-gray-900">{{ pilot.user_name }}</h3>
              <span 
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  getTierBadgeClass(index)
                ]"
              >
                {{ getTierName(index) }}
              </span>
            </div>
            <p class="text-sm text-gray-600">{{ pilot.callsign }}</p>
          </div>

          <!-- Performance Metrics -->
          <div class="flex items-center gap-6 mr-4">
            <div class="text-center">
              <div class="text-lg font-bold text-gray-900">{{ pilot.total_flights }}</div>
              <div class="text-xs text-gray-600">Flights</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-gray-900">{{ formatFlightTime(pilot.flying_hours) }}</div>
              <div class="text-xs text-gray-600">Hours</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-purple-600">{{ formatNumber(pilot.points) }}</div>
              <div class="text-xs text-gray-600">Points</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-8 sm:py-12">
      <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
      </div>
      <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No pilot data available</h3>
      <p class="text-sm sm:text-base text-gray-600">There are currently no pilots to display in the leaderboard.</p>
    </div>

    <!-- View Full Leaderboard Button -->
    <div class="text-center">
      <button class="shadow-xl inline-flex items-center gap-2 px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500 text-white font-bold rounded-lg transition-colors text-sm sm:text-base" @click="openFullLeaderboard()">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
        <span class="hidden sm:inline">View Full Leaderboard</span>
        <span class="sm:hidden">View All</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
  leaderboard: {
    type: Array,
    required: true
  },
  tenant: {
    type: Object,
    required: true
  }
})

const leaderboardData = ref([])

// Initialize leaderboard data from props
const initializeLeaderboardData = () => {
  if (props.leaderboard) {
    leaderboardData.value = Object.entries(props.leaderboard).map(([id, pilot]) => ({
      id,
      ...pilot
    }))
  } else {
    leaderboardData.value = []
  }
}

// Format numbers with commas
const formatNumber = (num) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

// Get tier badge styling
const getTierBadgeClass = (index) => {
  if (index === 0) {
    return 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300 shadow-sm font-semibold'
  } else if (index === 1) {
    return 'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300 shadow-sm font-semibold'
  } else if (index === 2) {
    return 'bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 border border-orange-300 shadow-sm font-semibold'
  }
}

// Get tier name
const getTierName = (index) => {
  if (index === 0) {
    return 'Gold'
  } else if (index === 1) {
    return 'Silver'
  } else if (index === 2) {
    return 'Bronze'
  } else {
    return ''
  }
}

const formatFlightTime = (totalMinutes) => {
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  return `${hours}h ${minutes}m`
}

const openFullLeaderboard = () => {
  window.location.href = '/settings/leaderboard'
}

// Initialize data when component mounts or props change
onMounted(() => {
  initializeLeaderboardData()
})

// Watch for prop changes
watch(() => props.leaderboard, () => {
  initializeLeaderboardData()
}, { immediate: true })
</script>