<script setup>
import AnalyticsCard from '../components/AnalyticsCard.vue';
import RecentActivity from '../components/RecentActivity.vue';
import UpcomingEvents from '../components/UpcomingEvents.vue';
import QuickLinks from '../components/QuickLinks.vue';
import DashboardButtons from '../components/DashboardButtons.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    analytics: Array,
    recentActivities: Array,
    upcomingEvents: Array,
    quickLinks: Array,
});

const user = usePage().props.auth.user;
</script>

<template>
  <AppLayout title="Dashboard">
    <!-- Add welcome back message -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ user.name }}</h1>
      <p class="text-lg text-gray-600 mt-1">
        Manage your virtual airline operations and monitor pilot activities.
      </p>
    </div>
    <DashboardButtons />
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <AnalyticsCard
        v-for="(card, index) in props.analytics"
        :key="index"
        :title="card.title"
        :value="card.value"
        :type="card.type"
      />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Recent Pilot Activity -->
      <div class="col-span-2">
        <RecentActivity :activities="props.recentActivities" />
      </div>

      <div class="space-y-6">
        <UpcomingEvents :events="props.upcomingEvents" />
        <QuickLinks :links="props.quickLinks" />
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
</style>