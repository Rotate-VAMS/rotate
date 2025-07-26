<template>
  <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <PirepsHeader ref="pirepsHeaderRef" :custom-fields="pirepCustomFields" />

      <!-- Analytics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <PirepsAnalyticsCard title="My Pireps" :value="analytics.myPireps" :icon="icons.Users" />
        <PirepsAnalyticsCard title="All Pireps" :value="analytics.totalPireps" :icon="icons.Activity" />
      </div>

      <!-- Filter and View Toggle -->
      <div class="flex justify-between flex-wrap gap-2 items-center mb-4">
        <!-- Pireps Filter Toggle -->
        <div class="flex gap-2">
          <button
            @click="switchFilter('my')"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              pirepFilter === 'my'
                ? 'bg-green-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            My Pireps
          </button>
          <button
            @click="switchFilter('all')"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              pirepFilter === 'all'
                ? 'bg-green-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            All Pireps
          </button>
        </div>

        <!-- View Toggle -->
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
            :pirep="pirep"
            :custom-fields="pirepCustomFields"
          />
        </div>
        <PirepsTable v-else :custom-fields="pirepCustomFields" :pireps="pireps" />
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
import { ref, onMounted, onUnmounted, inject, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import rotateDataService from '@/rotate.js';

const page = usePage();
const breadcrumbs = page.props.breadcrumbs || [];
const pirepsHeaderRef = ref(null);
const pirepCustomFields = ref([]);
const pireps = ref([]);
const showToast = inject('showToast');

const icons = { Users, Activity, Clock, Star };
const analytics = ref({});

// Fetch pirep custom fields
const fetchPirepCustomFields = async () => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/pireps/jxGetPirepCustomFields');
    if (response.hasErrors) {
      showToast(response.message, 'error');
      return;
    }
    pirepCustomFields.value = response.data;
    page.props.loading = false
  } catch (e) {
    console.error(e)
    showToast('Error fetching pirep custom fields', 'error');
    page.props.loading = false
  }
};

const fetchPireps = async (filter = 'my') => {
  try {
    page.props.loading = true
    const response = await rotateDataService('/pireps/jxFetchPireps', {
      filter: filter
    })
    pireps.value = response.data || []
    analytics.value = response.analytics || {}
    page.props.loading = false
  } catch (e) {
    console.error(e)
    page.props.loading = false
    showToast('Error fetching pireps', 'error')
  }
}

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

const getFilterFromQuery = () => {
  const params = new URLSearchParams(window.location.search);
  return params.get('filter') || 'my';
};

const setViewInQuery = (view) => {
  const params = new URLSearchParams(window.location.search);
  params.set('view', view);
  const newUrl = `${window.location.pathname}?${params.toString()}`;
  window.history.replaceState({}, '', newUrl);
};

const setFilterInQuery = (filter) => {
  const params = new URLSearchParams(window.location.search);
  params.set('filter', filter);
  const newUrl = `${window.location.pathname}?${params.toString()}`;
  window.history.replaceState({}, '', newUrl);
};

const viewMode = ref(getViewFromQuery());
const pirepFilter = ref(getFilterFromQuery());

const switchView = (mode) => {
  viewMode.value = mode;
  setViewInQuery(mode);
};

const switchFilter = (filter) => {
  pirepFilter.value = filter;
  setFilterInQuery(filter);
};

// Watch for filter changes and refetch data
watch(pirepFilter, (newFilter) => {
  fetchPireps(newFilter);
});

onMounted(() => {
  fetchPireps(pirepFilter.value);

  window.addEventListener('popstate', () => {
    viewMode.value = getViewFromQuery();
    pirepFilter.value = getFilterFromQuery();
  });
  window.addEventListener('open-edit-drawer', handleOpenEditDrawer);
});

onUnmounted(() => {
  window.removeEventListener('open-edit-drawer', handleOpenEditDrawer);
});
</script>