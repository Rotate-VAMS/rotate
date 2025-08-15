<script setup>
import AnalyticsCard from '../components/AnalyticsCard.vue';
import RecentActivity from '../components/RecentActivity.vue';
import UpcomingEvents from '../components/UpcomingEvents.vue';
import QuickLinks from '../components/QuickLinks.vue';
import DashboardButtons from '../components/DashboardButtons.vue';
import DashboardLeaderboard from '../components/DashboardLeaderboard.vue';
import QuoteCard from '../components/QuoteCard.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    analytics: Array,
    recentActivities: Array,
    upcomingEvents: Array,
    leaderboard: Array,
    quote: String,
  });

const user = usePage().props.auth.user;
const tenant = usePage().props.auth.tenant;

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
    <!-- Welcome Section with Quote -->
    <div class="mb-6 mt-2 flex flex-col lg:flex-row justify-between items-center gap-4">
      <div class="flex-1">
        <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-slate-900 via-indigo-600 to-slate-900 bg-clip-text text-transparent mb-1"> {{ getGreeting() }}, {{ user.name }}</h1>
        <p class="text-lg md:text-xl text-slate-600">
          Welcome back to your dashboard!
        </p>
        <div class="mt-6">
          <DashboardButtons :tenant="tenant" />
        </div>
      </div>
      <div class="w-full lg:w-72 xl:w-80">
        <QuoteCard :quote="props.quote" />
      </div>
    </div>

    <!-- Analytics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <AnalyticsCard
        v-for="(card, index) in props.analytics"
        :key="index"
        :title="card.title"
        :value="card.value"
        :type="card.type"
        :caption="card.caption"
        :tenant="tenant"
        :visible="card.visible"
      />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
      <!-- Recent Pilot Activity -->
      <div class="lg:col-span-2">
        <RecentActivity :activities="props.recentActivities" />
      </div>

      <!-- Sidebar -->
      <div class="space-y-4">
        <UpcomingEvents :events="props.upcomingEvents" :tenant="tenant" />
        <QuickLinks />
      </div>
    </div>

    <!-- Pilot Leaderboard Section -->
    <div class="mt-6">
      <DashboardLeaderboard :leaderboard="props.leaderboard" :tenant="tenant" />
    </div>
  </AppLayout>
</template>