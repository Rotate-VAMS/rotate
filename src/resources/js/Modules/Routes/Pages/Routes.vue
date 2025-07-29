<template>
    <AppLayout title="Routes">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <RoutesHeader ref="routesHeaderRef" :custom-fields="routeCustomFields" />
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <RoutesAnalyticsCard title="Total Routes" :value="analyticsData.totalRoutes" :icon="icons.Users" />
        <RoutesAnalyticsCard title="Active Routes" :value="analyticsData.activeRoutes" :icon="icons.Activity" />
        </div>
  
      <RoutesTable :custom-fields="routeCustomFields" @update:analytics="updateAnalytics" />
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
  import { ref, onMounted, onUnmounted, inject } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import rotateDataService from '@/rotate.js';

  const showToast = inject('showToast');
  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []
  const routesHeaderRef = ref(null)
  const routeCustomFields = ref([])

  const icons = { Users, Activity, Clock, Star };
  const analyticsData = ref({
    totalRoutes: 0,
    activeRoutes: 0,
  });

  // Fetch route custom fields
  const fetchRouteCustomFields = async () => {
    try {
      page.props.loading = true
      const response = await rotateDataService('/routes/jxGetRouteCustomFields')
      if (response.hasErrors) {
        showToast(response.message, 'error')
        return;
      }
      routeCustomFields.value = response.data
      page.props.loading = false
    } catch (e) {
      showToast('Error fetching route custom fields', 'error')
      page.props.loading = false
    }
  }
  fetchRouteCustomFields()

  // Handle edit route event
  const handleOpenEditDrawer = (event) => {
    page.props.loading = true
    if (routesHeaderRef.value) {
      routesHeaderRef.value.openDrawerForEdit(event.detail)
    }
    page.props.loading = false
  }

  const updateAnalytics = (analytics) => {
    analyticsData.value = {
      totalRoutes: analytics.totalRoutes || 0,
      activeRoutes: analytics.activeRoutes || 0,
    }
  }

  onMounted(() => {
    window.addEventListener('open-edit-drawer', handleOpenEditDrawer)
  })

  onUnmounted(() => {
    window.removeEventListener('open-edit-drawer', handleOpenEditDrawer)
  })
  </script>
  