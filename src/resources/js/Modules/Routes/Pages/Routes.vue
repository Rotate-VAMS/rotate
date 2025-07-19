<template>
    <AppLayout title="Routes">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <RoutesHeader ref="routesHeaderRef" />
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <RoutesAnalyticsCard title="Total Routes" :value="analytics.totalRoutes" :icon="icons.Users" />
        <RoutesAnalyticsCard title="Active Routes" :value="analytics.activeRoutes" :icon="icons.Activity" />
        </div>
  
      <RoutesTable />
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

  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []
  const routesHeaderRef = ref(null)

  const icons = { Users, Activity, Clock, Star };
  const analytics = ref({
    totalRoutes: page.props.analyticsData.total_routes,
    activeRoutes: page.props.analyticsData.total_active_routes,
  });

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
  