<template>
  <div class="max-w-6xl mx-auto p-3 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 sm:mb-8">
      <div class="flex items-center gap-2 sm:gap-3 mb-4 sm:mb-0">
        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center">
          <TrophyIcon class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">Leaderboard Configuration</h1>
          <p class="text-gray-600 text-xs sm:text-sm lg:text-base">Configure points, multipliers, and penalties for pilot activities.</p>
        </div>
      </div>
    </div>

      <!-- Leaderboard Content -->
    <div class="space-y-6 mb-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Flight Operations Section -->
        <div class="shadow-xl bg-gradient-to-br from-blue-50 via-blue-100 to-blue-50 border border-blue-300 rounded-lg p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-blue-100 rounded-lg border border-blue-300 flex items-center justify-center">
              <PlaneIcon class="w-5 h-5 text-blue-600" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Flight Operations</h3>
          </div>
          
          <div class="space-y-4">
            <div v-for="item in flightOperations" :key="item.leaderboard_event_name" class="flex items-center justify-between p-3 bg-white rounded-lg border border-blue-200">
              <div>
                <h4 class="font-medium text-gray-900">{{ getEventDisplayName(item.leaderboard_event_name) }}</h4>
                <p class="text-sm text-gray-600">{{ getEventDescription(item.leaderboard_event_name) }}</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="updateLeaderboardEvent(item.leaderboard_event_name, item.points - 5)"
                  class="w-8 h-8 bg-white border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50"
                >
                  <MinusIcon class="w-4 h-4 text-gray-600" />
                </button>
                <input
                  type="number"
                  :value="item.points"
                  @input="updateLeaderboardEvent(item.leaderboard_event_name, parseInt($event.target.value) || 0)"
                  @blur="updateLeaderboardEvent(item.leaderboard_event_name, parseInt($event.target.value) || 0)"
                  class="w-20 text-center font-semibold text-gray-900 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  min="0"
                />
                <button
                  @click="updateLeaderboardEvent(item.leaderboard_event_name, item.points + 5)"
                  class="w-8 h-8 bg-white border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50"
                >
                  <PlusIcon class="w-4 h-4 text-gray-600" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Milestones Section -->
        <div class="shadow-xl bg-gradient-to-br from-green-50 via-green-100 to-green-50 border border-green-300 rounded-lg p-6">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 bg-yellow-100 rounded-lg border border-yellow-300 flex items-center justify-center">
              <StarIcon class="w-5 h-5 text-yellow-600" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Milestones</h3>
          </div>
          
          <div class="space-y-4">
            <div v-for="item in milestones" :key="item.leaderboard_event_name" class="flex items-center justify-between p-3 bg-white rounded-lg border border-green-200">
              <div>
                <h4 class="font-medium text-gray-900">{{ getEventDisplayName(item.leaderboard_event_name) }}</h4>
                <p class="text-sm text-gray-600">{{ getEventDescription(item.leaderboard_event_name) }}</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="updateLeaderboardEvent(item.leaderboard_event_name, item.points - 5)"
                  class="w-8 h-8 bg-white border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50"
                >
                  <MinusIcon class="w-4 h-4 text-gray-600" />
                </button>
                <input
                  type="number"
                  :value="item.points"
                  @input="updateLeaderboardEvent(item.leaderboard_event_name, parseInt($event.target.value) || 0)"
                  @blur="updateLeaderboardEvent(item.leaderboard_event_name, parseInt($event.target.value) || 0)"
                  class="w-20 text-center font-semibold text-gray-900 border border-gray-300 rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  min="0"
                />
                <button
                  @click="updateLeaderboardEvent(item.leaderboard_event_name, item.points + 5)"
                  class="w-8 h-8 bg-white border border-gray-300 rounded-lg flex items-center justify-center hover:bg-gray-50"
                >
                  <PlusIcon class="w-4 h-4 text-gray-600" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Configuration Summary -->
    <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-4 sm:p-6">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
          <BarChart3Icon class="w-5 h-5 text-indigo-600" />
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Configuration Summary</h2>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-blue-700">Total PIREP Points</p>
              <p class="text-2xl font-bold text-blue-900">{{ totalPirepPoints }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <PlaneIcon class="w-5 h-5 text-blue-600" />
            </div>
          </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-yellow-700">Total Milestone Points</p>
              <p class="text-2xl font-bold text-yellow-900">{{ totalMilestonePoints }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
              <StarIcon class="w-5 h-5 text-yellow-600" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, inject, computed } from 'vue';
import { 
  TrophyIcon, 
  PlaneIcon, 
  TargetIcon, 
  CalendarIcon, 
  StarIcon, 
  ZapIcon, 
  AlertTriangleIcon,
  BarChart3Icon,
  PlusIcon,
  MinusIcon
} from 'lucide-vue-next';
import rotateDataService from '@/rotate.js';

const showToast = inject('showToast');

// Reactive data
const activeTab = ref('pireps');
const leaderboardSettings = ref({});
const leaderboardEvents = ref([]);

// Tab configuration
const tabs = [
  { id: 'pireps', name: 'PIREPS' },
  { id: 'milestones', name: 'Milestones' },
];

// Computed properties for grouped data
const flightOperations = computed(() => {
  return leaderboardEvents.value.filter(item => item.group === 'Flight Operations');
});

const milestones = computed(() => {
  return leaderboardEvents.value.filter(item => item.group === 'Milestones');
});

// Computed properties for summary
const totalPirepPoints = computed(() => {
  return flightOperations.value.reduce((sum, item) => sum + (item.points || 0), 0) +
         milestones.value.reduce((sum, item) => sum + (item.points || 0), 0);
});

const totalMilestonePoints = computed(() => {
  return milestones.value.reduce((sum, item) => sum + (item.points || 0), 0);
});

// Event display name mapping
const eventDisplayNames = {
  'pirep': 'Filing PIREP',
  'flight_short_haul': 'Short Haul Flight (< 2hrs)',
  'flight_long_haul': 'Long Haul Flight (> 4hrs)',
  'flight_ultra_long_haul': 'Ultra Long Haul Flight (> 8hrs)',
  'milestone_first_pirep': 'First PIREP',
  'milestone_hundred_pireps': '100 PIREPs',
  'milestone_five_hundred_pireps': '500 PIREPs',
  'milestone_one_thousand_pireps': '1000 PIREPs'
};

// Event description mapping
const eventDescriptions = {
  'pirep': 'Points for filing a PIREP',
  'flight_short_haul': 'Bonus for short haul flights',
  'flight_long_haul': 'Bonus for long haul flights',
  'flight_ultra_long_haul': 'Bonus for ultra long haul flights',
  'milestone_first_pirep': 'Achievement for first PIREP',
  'milestone_hundred_pireps': 'Achievement for 100 PIREPs',
  'milestone_five_hundred_pireps': 'Achievement for 500 PIREPs',
  'milestone_one_thousand_pireps': 'Achievement for 1000 PIREPs'
};

// Methods
const getEventDisplayName = (eventName) => {
  return eventDisplayNames[eventName] || eventName;
};

const getEventDescription = (eventName) => {
  return eventDescriptions[eventName] || 'Points for this activity';
};

const updateLeaderboardEvent = async (eventName, newPoints) => {
  try {
    // Find the item in the leaderboardEvents array and update it
    const item = leaderboardEvents.value.find(item => item.leaderboard_event_name === eventName);
    if (item) {
      item.points = Math.max(0, newPoints); // Ensure points don't go below 0
      
      // Call backend API to update the points using the correct route
      await rotateDataService('/settings/jxUpdateLeaderboardEvent', {
        event_name: eventName,
        points: item.points
      });
      
      showToast('Points updated successfully', 'success');
    }
  } catch (error) {
    console.error('Error updating leaderboard event:', error);
    showToast('Failed to update points', 'error');
  }
};

const updatePoints = async (itemName, newPoints) => {
  try {
    // Find the item in the appropriate array and update it
    const allItems = [
      ...eventPoints.value,
      ...achievementPoints.value,
      ...penalties.value
    ];
    
    const item = allItems.find(item => item.name === itemName);
    if (item) {
      item.points = Math.max(0, newPoints); // Ensure points don't go below 0
      
      // Call backend API to update the points
      await rotateDataService('/settings/jxUpdateLeaderboardSettings', {
        event_name: itemName,
        points: item.points
      });
      
      showToast('Points updated successfully', 'success');
    }
  } catch (error) {
    console.error('Error updating points:', error);
    showToast('Failed to update points', 'error');
  }
};

const updateMultiplier = async (itemName, newMultiplier) => {
  try {
    const item = multipliers.value.find(item => item.name === itemName);
    if (item) {
      item.multiplier = Math.max(0.1, newMultiplier); // Ensure multiplier doesn't go below 0.1
      
      // Call backend API to update the multiplier
      await rotateDataService('/settings/jxUpdateLeaderboardSettings', {
        event_name: itemName,
        multiplier: item.multiplier
      });
      
      showToast('Multiplier updated successfully', 'success');
    }
  } catch (error) {
    console.error('Error updating multiplier:', error);
    showToast('Failed to update multiplier', 'error');
  }
};

const loadLeaderboardSettings = async () => {
  const response = await rotateDataService('/settings/jxGetLeaderboardSettings');
  if (response.hasErrors) {
    showToast(response.message, 'error');
    return;
  }
  leaderboardSettings.value = response.data;
};

const loadLeaderboardEvents = async () => {
    const response = await rotateDataService('/settings/jxGetLeaderboardEvents');
    if (response.hasErrors) {
      showToast(response.message, 'error');
      return;
    }
  leaderboardEvents.value = response.data;
};

onMounted(() => {
  loadLeaderboardSettings();
  loadLeaderboardEvents();
});
</script>