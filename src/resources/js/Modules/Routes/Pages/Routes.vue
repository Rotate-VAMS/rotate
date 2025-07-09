<template>
    <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <RoutesHeader />
  
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
  import { ref } from 'vue';
  import { usePage } from '@inertiajs/vue3';

  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []

  const icons = { Users, Activity, Clock, Star };
  const analytics = ref({
    totalRoutes: 6,
    activeRoutes: 4,
  });
  </script>
  