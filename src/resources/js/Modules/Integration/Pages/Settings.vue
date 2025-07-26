<template>
    <AppLayout title="Integrations">
        <div class="space-y-6">
            <AppBreadcrumb :items="breadcrumbs" />
            <SettingsHeader />
            <div class="flex flex-row flex-wrap gap-6">
                <SettingsSidebar :active="activeTab" @navigate="changeTab" />
                <div class="flex-1">
                    <component :is="currentComponent" @logo-updated="fetchLogo" />
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
  import { usePage } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue'
  import SettingsHeader from '../components/SettingsHeader.vue'
  const page = usePage()
  const breadcrumbs = page.props.breadcrumbs || []

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
      logo: LogoPanel,
    }[activeTab.value]
  })
</script>