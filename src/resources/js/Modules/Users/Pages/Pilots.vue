<template>
    <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <PilotsHeader ref="pilotsHeaderRef" :custom-fields="userCustomFields" />
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <PilotsAnalyticsCard title="Total Pilots" :value="analytics.totalPilots" :icon="icons.Users" />
        <PilotsAnalyticsCard title="Active Pilots" :value="analytics.activePilots" :icon="icons.Activity" />
        <PilotsAnalyticsCard title="Total Flying Hours" :value="analytics.totalHours" :icon="icons.Clock" />
        <PilotsAnalyticsCard title="Average Rating" :value="analytics.avgRating" :icon="icons.Star" />
      </div>
  
      <PilotsTable :custom-fields="userCustomFields" />
    </div>
    </AppLayout>
  </template>
  
  <script setup>
  import PilotsAnalyticsCard from '../components/PilotsAnalyticsCard.vue';
  import PilotsHeader from '../components/PilotsHeader.vue';
  import PilotsTable from '../components/PilotsTable.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
  import { Users, Activity, Clock, Star } from 'lucide-vue-next';
  import { ref, onMounted, onUnmounted } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import rotateDataService from '@/rotate.js';
  import { inject } from 'vue'

  const showToast = inject('showToast');
  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []
  const pilotsHeaderRef = ref(null)
  const userCustomFields = ref([])

  const icons = { Users, Activity, Clock, Star };
  const analytics = ref({
    totalPilots: 6,
    activePilots: 4,
    totalHours: '5,433h',
    avgRating: '3.8',
  });

  // Handle edit pilot event
  const handleOpenEditDrawer = (event) => {
    if (pilotsHeaderRef.value) {
      pilotsHeaderRef.value.openDrawerForEdit(event.detail)
    }
  }

  // Fetch user custom fields
  const fetchUserCustomFields = async () => {
    try {
      const response = await rotateDataService('/pilots/jxGetUserCustomFields')
      if (response.hasErrors) {
        showToast(response.message || 'Error occurred', 'error')
        return;
      }
      userCustomFields.value = response.data
  } catch (e) {
    console.error(e)
    showToast('Error fetching user custom fields', 'error')
  }
  }
  fetchUserCustomFields()

  onMounted(() => {
    window.addEventListener('open-edit-drawer', handleOpenEditDrawer)
  })

  onUnmounted(() => {
    window.removeEventListener('open-edit-drawer', handleOpenEditDrawer)
  })
</script>
  