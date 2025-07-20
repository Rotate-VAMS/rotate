<template>
  <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <PirepsHeader ref="pirepsHeaderRef" :custom-fields="pirepCustomFields" />

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
        <PirepsTable v-else :custom-fields="pirepCustomFields" />
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import rotateDataService from '@/rotate.js';

const page = usePage();
const breadcrumbs = page.props.breadcrumbs || [];
const pirepsHeaderRef = ref(null);
const pirepCustomFields = ref([]);
const pireps = ref([]);

const icons = { Users, Activity, Clock, Star };
const analytics = ref({
  myPireps: 4,
  allPireps: 6,
});

// Fetch pirep custom fields
const fetchPirepCustomFields = async () => {
  try {
    const response = await rotateDataService('/pireps/jxGetPirepCustomFields');
    if (!response.hasErrors) {
      pirepCustomFields.value = response.data;
    }
  } catch (error) {
    console.error('Error fetching pirep custom fields:', error);
  }
};
fetchPirepCustomFields();

// Handle edit pirep event
const handleOpenEditDrawer = (event) => {
  if (pirepsHeaderRef.value) {
    pirepsHeaderRef.value.openDrawerForEdit(event.detail);
  }
};

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
  window.addEventListener('open-edit-drawer', handleOpenEditDrawer);
});

onUnmounted(() => {
  window.removeEventListener('open-edit-drawer', handleOpenEditDrawer);
});
</script>