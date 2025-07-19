<template>
    <AppLayout title="Routes">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <RoutesHeader ref="routesHeaderRef" :custom-fields="routeCustomFields" />
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <RoutesAnalyticsCard title="Total Routes" :value="analytics.totalRoutes" :icon="icons.Users" />
        <RoutesAnalyticsCard title="Active Routes" :value="analytics.activeRoutes" :icon="icons.Activity" />
        </div>
  
      <RoutesTable :custom-fields="routeCustomFields" />
    </div>
    </AppLayout>
  </template>
  
  <script setup>
  import RoutesAnalyticsCard from '../components/RoutesAnalyticsCard.vue';
  import RoutesHeader from '../components/RoutesHeader.vue';
  import RoutesTable from '../components/RoutesTable.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
  import { Users, Activity, Clock, Star } from 'lucide-vue-next';
  import { ref, onMounted, onUnmounted } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import rotateDataService from '@/rotate.js';

  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []
  const routesHeaderRef = ref(null)
  const routeCustomFields = ref([])

  const icons = { Users, Activity, Clock, Star };
  const analytics = ref({
    totalRoutes: page.props.analyticsData.total_routes,
    activeRoutes: page.props.analyticsData.total_active_routes,
  });

  // Fetch route custom fields
  const fetchRouteCustomFields = async () => {
    try {
      const response = await rotateDataService('/routes/jxGetRouteCustomFields')
      if (!response.hasErrors) {
        routeCustomFields.value = response.data
      }
    } catch (error) {
      console.error('Error fetching route custom fields:', error)
    }
  }
  fetchRouteCustomFields()

  // Handle edit route event
  const handleOpenEditDrawer = (event) => {
    if (routesHeaderRef.value) {
      routesHeaderRef.value.openDrawerForEdit(event.detail)
    }
  }

  onMounted(() => {
    window.addEventListener('open-edit-drawer', handleOpenEditDrawer)
  })

  onUnmounted(() => {
    window.removeEventListener('open-edit-drawer', handleOpenEditDrawer)
  })
  </script>
  