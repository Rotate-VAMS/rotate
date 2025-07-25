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

// Fetch greetings based on time of day
const getGreeting = () => {
    const hours = new Date().getHours();
    if (0 <= hours && hours < 12) return 'Good Morning';
    if (12 <= hours && hours < 17) return 'Good Afternoon';
    return 'Good Evening';
}
</script>

<template>
  <AppLayout title="Dashboard">
    <!-- Add welcome back message -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900"> {{ getGreeting() }}, {{ user.name }}</h1>
      <p class="text-lg text-gray-600 mt-1">
        Welcome back to your dashboard!
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
        :caption="card.caption"
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