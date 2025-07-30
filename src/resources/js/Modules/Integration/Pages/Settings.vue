<template>
    <AppLayout title="Integrations">
        <div class="space-y-6">
            <AppBreadcrumb :items="breadcrumbs" />
            <SettingsHeader />
            <div class="flex flex-row flex-wrap gap-6">
                <SettingsSidebar :active="activeTab" @navigate="changeTab" />
                <div class="flex-1">
                    <!-- Show upgrade component for restricted features -->
                    <div v-if="activeTab === 'customFields' && !isCustomFieldsAvailable">
                        <RotateUpgradeComponent
                            required-plan="Cadet"
                            title="Custom Fields - Premium Feature"
                            message="Create and manage custom fields for your pilots, PIREPs, events, and routes. Customize your data collection to match your specific needs."
                            :features="[
                                'Create unlimited custom fields',
                                'Support for text, numbers, dates, and dropdowns',
                                'Apply to pilots, PIREPs, events, and routes',
                                'Required field validation',
                                'Advanced field configuration'
                            ]"
                            @upgrade="handleUpgrade"
                            @learn-more="handleLearnMore"
                        />
                    </div>
                    
                    <div v-else-if="activeTab === 'logo' && !isLogoAvailable">
                        <RotateUpgradeComponent
                            required-plan="Cadet"
                            title="Custom Branding - Premium Feature"
                            message="Upload your own logo and customize your virtual airline's branding. Make your VA stand out with professional custom branding."
                            :features="[
                                'Upload custom logo',
                                'Professional branding',
                                'Brand consistency across the platform',
                                'Enhanced visual identity',
                                'Custom logo display on all pages'
                            ]"
                            @upgrade="handleUpgrade"
                            @learn-more="handleLearnMore"
                        />
                    </div>
                    
                    <div v-else-if="activeTab === 'discord' && !isDiscordIntegrationAvailable">
                        <RotateUpgradeComponent
                            required-plan="Captain"
                            title="Discord Integration - Premium Feature"
                            message="Connect your Discord server to file PIREPs and receive real-time event notifications. Streamline your operations with seamless Discord integration."
                            :features="[
                                'File PIREPs directly from Discord',
                                'Real-time event notifications',
                                'Bot commands for flight operations',
                                'Automatic Discord ID synchronization',
                                'Advanced notification settings'
                            ]"
                            @upgrade="handleUpgrade"
                            @learn-more="handleLearnMore"
                        />
                    </div>
                    
                    <!-- Show normal component if feature is available -->
                    <component v-else :is="currentComponent" @logo-updated="fetchLogo" />
                </div>
            </div>
        </div>
    </AppLayout>
  </template>
  
<script setup>
  import { ref, computed, onMounted, inject } from 'vue'
  import SettingsSidebar from '../components/SettingsSidebar.vue'
  import FleetPanel from '../components/FleetPanel.vue'
  import FlightTypesPanel from '../components/FlightTypesPanel.vue'
  import CustomFieldsPanel from '../components/CustomFieldsPanel.vue'
  import DiscordIntegrationPanel from '../components/DiscordIntegrationPanel.vue'
  import RolesPanel from '../components/RolesPanel.vue'
  import RanksPanel from '../components/RanksPanel.vue'
  import LogoPanel from '../components/LogoPanel.vue'
  import LeaderboardPanel from '../components/LeaderboardPanel.vue'
  import RotateUpgradeComponent from '@/Components/RotateUpgradeComponent.vue'
  import { usePage } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue'
  import SettingsHeader from '../components/SettingsHeader.vue'
  const page = usePage()
  const breadcrumbs = page.props.breadcrumbs || []

  const tenant = page.props.auth.tenant;

  const getTabFromQuery = () => {
    const params = new URLSearchParams(window.location.search)
    return params.get('tab') || 'roles'
  }

  const setTabInQuery = (key) => {
    const params = new URLSearchParams(window.location.search)
    params.set('tab', key)
    const newUrl = `${window.location.pathname}?${params.toString()}`
    window.history.replaceState({}, '', newUrl)
  }

  const activeTab = ref(getTabFromQuery())
  
  const changeTab = (key) => {
    activeTab.value = key
    setTabInQuery(key)
  }
  
  onMounted(() => {
    window.addEventListener('popstate', () => {
      activeTab.value = getTabFromQuery()
    })
  })
  
  const currentComponent = computed(() => {
    return {
      fleet: FleetPanel,
      flightTypes: FlightTypesPanel,
      customFields: CustomFieldsPanel,
      discord: DiscordIntegrationPanel,
      ranks: RanksPanel,
      roles: RolesPanel,
      leaderboard: LeaderboardPanel,
      logo: LogoPanel,
    }[activeTab.value]
  })

  // Check if features are available based on tenant plan
  const isCustomFieldsAvailable = computed(() => {
    return tenant.available_features.includes('custom-fields')
  })

  const isLogoAvailable = computed(() => {
    return tenant.available_features.includes('logo')
  })

  const isDiscordIntegrationAvailable = computed(() => {
    return tenant.available_features.includes('discord-integration')
  })

  // Handle upgrade events
  const handleUpgrade = (data) => {
    // Redirect to upgrade page or show upgrade modal
    console.log('Upgrade requested for:', data.requiredPlan)
    // You can implement the actual upgrade logic here
    // For example: window.location.href = '/upgrade'
  }

  const handleLearnMore = (data) => {
    // Handle learn more action
    console.log('Learn more requested for:', data.requiredPlan)
    // You can implement the actual learn more logic here
  }

  // Fetch logo function (placeholder for logo panel)
  const fetchLogo = () => {
    // This function is called when logo is updated
    // You can implement logo refresh logic here if needed
    console.log('Logo updated')
  }
</script>