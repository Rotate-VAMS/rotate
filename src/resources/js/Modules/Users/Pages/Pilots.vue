<template>
    <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <PilotsHeader />
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <PilotsAnalyticsCard title="Total Pilots" :value="analytics.totalPilots" :icon="icons.Users" />
        <PilotsAnalyticsCard title="Active Pilots" :value="analytics.activePilots" :icon="icons.Activity" />
        <PilotsAnalyticsCard title="Total Flying Hours" :value="analytics.totalHours" :icon="icons.Clock" />
        <PilotsAnalyticsCard title="Average Rating" :value="analytics.avgRating" :icon="icons.Star" />
      </div>
  
      <PilotsTable />
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
  import { ref } from 'vue';
  import { usePage } from '@inertiajs/vue3';

  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []

  const icons = { Users, Activity, Clock, Star };
  const analytics = ref({
    totalPilots: 6,
    activePilots: 4,
    totalHours: '5,433h',
    avgRating: '3.8',
  });
</script>
  