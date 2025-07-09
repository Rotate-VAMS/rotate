<template>
    <AppLayout title="Pilots">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <EventsHeader />
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <EventsAnalyticsCard title="Total Events" :value="analytics.totalEvents" :icon="icons.Users" />
        <EventsAnalyticsCard title="Active Events" :value="analytics.activeEvents" :icon="icons.Activity" />
        </div>
  
      <EventsTable />
    </div>
    </AppLayout>
  </template>
  
  <script setup>
  import EventsAnalyticsCard from '../components/EventsAnalyticsCard.vue';
  import EventsHeader from '../components/EventsHeader.vue';
  import EventsTable from '../components/EventsTable.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
  import { Users, Activity, Clock, Star } from 'lucide-vue-next';
  import { ref } from 'vue';
  import { usePage } from '@inertiajs/vue3';

  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []

  const icons = { Users, Activity, Clock, Star };
  const analytics = ref({
    totalEvents: 6,
    activeEvents: 4,
  });
  </script>
  