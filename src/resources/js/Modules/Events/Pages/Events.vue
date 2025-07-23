<template>
    <AppLayout title="Events">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <EventsHeader ref="eventsHeaderRef" />
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <EventsAnalyticsCard title="Total Events" :value="analyticsData.totalEvents" :icon="icons.Users" />
        <EventsAnalyticsCard title="Active Events" :value="analyticsData.activeEvents" :icon="icons.Activity" />
        </div>
  
      <EventsCardViewComponent @update:analytics="updateAnalytics" />
    </div>
    </AppLayout>
  </template>
  
  <script setup>
  import EventsAnalyticsCard from '../components/EventsAnalyticsCard.vue';
  import EventsHeader from '../components/EventsHeader.vue';
  import EventsCardViewComponent from '../components/EventsCardViewComponent.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
  import { Users, Activity, Clock, Star } from 'lucide-vue-next';
  import { ref, onMounted, onUnmounted } from 'vue';
  import { usePage } from '@inertiajs/vue3';

  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []
  const eventsHeaderRef = ref(null)
  const icons = { Users, Activity, Clock, Star };
  const analyticsData = ref({});

  const updateAnalytics = (analytics) => {
    analyticsData.value = {
      totalEvents: analytics.totalEvents || 0,
      activeEvents: analytics.activeEvents || 0,
    }
  }

  // Handle edit event
  const handleOpenEditDrawer = (event) => {
    if (eventsHeaderRef.value) {
      eventsHeaderRef.value.openDrawerForEdit(event.detail)
    }
  }

  onMounted(() => {
    window.addEventListener('open-edit-drawer', handleOpenEditDrawer)
  })

  onUnmounted(() => {
    window.removeEventListener('open-edit-drawer', handleOpenEditDrawer)
  })
</script>
  