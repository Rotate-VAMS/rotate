<template>
    <AppLayout title="Integrations">
        <div class="space-y-6">
            <AppBreadcrumb :items="breadcrumbs" />
            <SettingsHeader />
            <div class="flex flex-row gap-6">
                <SettingsSidebar :active="activeTab" @navigate="changeTab" />
                <div class="flex-1">
                    <component :is="currentComponent" />
                </div>
            </div>
        </div>
    </AppLayout>
  </template>
  
<script setup>
  import { ref, computed, onMounted } from 'vue'
  import SettingsSidebar from '../components/SettingsSidebar.vue'
  import FleetPanel from '../components/FleetPanel.vue'
  import CustomFieldsPanel from '../components/CustomFieldsPanel.vue'
  import DiscordIntegrationPanel from '../components/DiscordIntegrationPanel.vue'
  import AccessControlPanel from '../components/AccessControlPanel.vue'
  import RanksPanel from '../components/RanksPanel.vue'
  import { usePage } from '@inertiajs/vue3'
  import AppLayout from '@/Layouts/AppLayout.vue'
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue'
  import SettingsHeader from '../components/SettingsHeader.vue'
  const page = usePage()
  const breadcrumbs = page.props.breadcrumbs || []

  const getTabFromQuery = () => {
    const params = new URLSearchParams(window.location.search)
    return params.get('tab') || 'fleet'
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
      customFields: CustomFieldsPanel,
      discord: DiscordIntegrationPanel,
      access: AccessControlPanel,
      ranks: RanksPanel,
    }[activeTab.value]
  })
</script>