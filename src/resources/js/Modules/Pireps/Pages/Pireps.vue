<template>
  <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <PirepsHeader />

      <!-- Analytics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <PirepsAnalyticsCard title="My Pireps" :value="analytics.myPireps" :icon="icons.Users" />
        <PirepsAnalyticsCard title="All Pireps" :value="analytics.allPireps" :icon="icons.Activity" />
      </div>

      <!-- View Toggle -->
      <div class="flex justify-between items-center mb-4">
        <div></div> <!-- spacer -->
        <div class="flex gap-2">
          <button
            @click="switchView('cards')"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              viewMode === 'cards'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Cards
          </button>
          <button
            @click="switchView('table')"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              viewMode === 'table'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Table
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <div>
        <div v-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <BoardingPass
            v-for="pirep in pireps"
            :key="pirep.id"
            v-bind="pirep"
          />
        </div>
        <PirepsTable v-else />
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
import PirepsHeader from '../components/PirepsHeader.vue';
import PirepsAnalyticsCard from '../components/PirepsAnalyticsCard.vue';
import PirepsTable from '../components/PirepsTable.vue';
import BoardingPass from '../components/BoardingPass.vue';
import { Users, Activity, Clock, Star } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const breadcrumbs = page.props.breadcrumbs || [];
const analytics = ref({
  myPireps: 4,
  allPireps: 6,
});
const icons = { Users, Activity, Clock, Star };

const getViewFromQuery = () => {
  const params = new URLSearchParams(window.location.search);
  return params.get('view') || 'cards';
};

const setViewInQuery = (view) => {
  const params = new URLSearchParams(window.location.search);
  params.set('view', view);
  const newUrl = `${window.location.pathname}?${params.toString()}`;
  window.history.replaceState({}, '', newUrl);
};

const viewMode = ref(getViewFromQuery());

const switchView = (mode) => {
  viewMode.value = mode;
  setViewInQuery(mode);
};

onMounted(() => {
  window.addEventListener('popstate', () => {
    viewMode.value = getViewFromQuery();
  });
});

// Sample data, replace with actual fetched data
const pireps = ref([
  {
    id: 1,
    departure: 'KJFK',
    arrival: 'EGLL',
    flight_code: 'RO142',
    flight_time: '7h 42m',
    pilot: 'John Smith',
    distance: '3,459 NM',
    aircraft: ['Boeing 777-300ER'],
    fuel: '24,580 lbs',
    score: 95,
    multiplier: '2.5x',
    computed: '19h 15m',
    status: 'Completed',
    barcode: 'PIREP-001',
    timeAgo: '2h ago',
  },
  {
    id: 2,
    departure: 'KLAX',
    arrival: 'RJTT',
    flight_code: 'RO287',
    flight_time: '11h 20m',
    pilot: 'Sarah Johnson',
    distance: '5,487 NM',
    aircraft: ['Airbus A350-900'],
    fuel: '32,120 lbs',
    score: 88,
    multiplier: '1.8x',
    computed: '20h 24m',
    status: 'In-Progress',
    barcode: 'PIREP-002',
    timeAgo: '4h ago',
  }
]);
</script>