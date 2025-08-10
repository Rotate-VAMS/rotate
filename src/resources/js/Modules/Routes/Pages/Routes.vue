<template>
    <AppLayout title="Routes">
    <div class="space-y-6">
      <AppBreadcrumb :items="breadcrumbs" />
      <RoutesHeader ref="routesHeaderRef" :custom-fields="routeCustomFields" />
  
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <RoutesAnalyticsCard title="Total Routes" :value="analyticsData.totalRoutes" :icon="icons.Users" />
        <RoutesAnalyticsCard title="Active Routes" :value="analyticsData.activeRoutes" :icon="icons.Activity" />
        </div>
  
      <RoutesTable :custom-fields="routeCustomFields" @update:analytics="updateAnalytics" />

      <!-- PIREP Creation Form -->
      <RotateFormComponent
        :visible="showPirepDrawer"
        :title="'File PIREP from Route'"
        :fields="pirepFormFields"
        :initialData="pirepFormData"
        :isEditMode="false"
        @close="showPirepDrawer = false"
        @submit="submitPirepForm"
      />
    </div>
    </AppLayout>
  </template>
  
  <script setup>
  import RoutesAnalyticsCard from '../components/RoutesAnalyticsCard.vue';
  import RoutesHeader from '../components/RoutesHeader.vue';
  import RoutesTable from '../components/RoutesTable.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  import AppBreadcrumb from '@/Components/AppBreadcrumb.vue';
  import RotateFormComponent from '@/Components/RotateFormComponent.vue';
  import { Users, Activity, Clock, Star } from 'lucide-vue-next';
  import { ref, computed, onMounted, onUnmounted, inject } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import rotateDataService from '@/rotate.js';

  const showToast = inject('showToast');
  const page = usePage();
  const breadcrumbs = page.props.breadcrumbs || []
  const routesHeaderRef = ref(null)
  const routeCustomFields = ref([])

  const icons = { Users, Activity, Clock, Star };
  const analyticsData = ref({
    totalRoutes: 0,
    activeRoutes: 0,
  });

  // PIREP creation state
  const showPirepDrawer = ref(false)
  const pirepFormData = ref({})
  const pirepCustomFields = ref([])
  const flightTypes = ref([])
  const selectedRoute = ref(null)

  // Function to convert custom fields to form fields
  const convertCustomFieldsToFormFields = (customFields) => {
    return customFields.map(field => {
      const formField = {
        name: field.field_key,
        label: field.field_name,
        type: getFieldType(field.data_type),
        required: field.is_required === 1,
        description: field.field_description
      }
      
      // Add options for dropdown fields
      if (field.data_type === 6) { // Dropdown type
        if (Array.isArray(field.options)) {
          formField.options = field.options.map(opt => ({ id: opt, name: opt }))
        } else if (typeof field.options === 'object') {
          formField.options = Object.values(field.options).map(opt => ({ id: opt, name: opt }))
        } else {
          formField.options = []
        }
      }
      return formField
    })
  }

  // Function to map data types to form field types
  const getFieldType = (dataType) => {
    switch (dataType) {
      case 1: return 'text' // Text
      case 2: return 'number' // Integer
      case 3: return 'number' // Float
      case 4: return 'checkbox' // Boolean
      case 5: return 'datetime-local' // Date
      case 6: return 'select' // Dropdown
      default: return 'text'
    }
  }

  // Computed form fields for PIREP creation
  const pirepFormFields = computed(() => {
    const baseFields = [
      {
        name: 'route_display', 
        label: 'Route', 
        type: 'text', 
        required: true,
        readonly: true,
        description: 'This route is pre-selected and cannot be changed'
      },
      {
        name: 'origin_destination', 
        label: 'Origin → Destination', 
        type: 'text', 
        required: true,
        readonly: true,
        description: 'Route details from the selected route'
      },
      {
        group: 'Flight Time',
        fields: [
          { 
            name: 'flight_time_hours', 
            label: 'Hours', 
            type: 'number', 
            required: true, 
            min: 0, 
            max: 23,
            description: 'You can modify the flight time as needed'
          },
          { 
            name: 'flight_time_minutes', 
            label: 'Minutes', 
            type: 'number', 
            required: true, 
            min: 0, 
            max: 59,
            description: 'You can modify the flight time as needed'
          },
        ]
      },
      { 
        name: 'flight_type_id', 
        label: 'Flight Type', 
        type: 'select', 
        required: true, 
        options: flightTypes.value.map(type => ({ id: type.id, name: type.flight_type + ' (' + type.multiplier + 'x)' }))
      },
    ]

    // Add custom fields
    const customFormFields = convertCustomFieldsToFormFields(pirepCustomFields.value)
    return [...baseFields, ...customFormFields]
  })

  // Fetch route custom fields
  const fetchRouteCustomFields = async () => {
    try {
      page.props.loading = true
      const response = await rotateDataService('/routes/jxGetRouteCustomFields')
      if (response.hasErrors) {
        showToast(response.message, 'error')
        return;
      }
      routeCustomFields.value = response.data
      page.props.loading = false
    } catch (e) {
      showToast('Error fetching route custom fields', 'error')
      page.props.loading = false
    }
  }
  fetchRouteCustomFields()

  // Fetch PIREP custom fields
  const fetchPirepCustomFields = async () => {
    try {
      const response = await rotateDataService('/pireps/jxGetPirepCustomFields')
      if (response.hasErrors) {
        showToast(response.message, 'error')
        return;
      }
      pirepCustomFields.value = response.data
    } catch (e) {
      console.error('Error fetching pirep custom fields:', e)
      showToast('Error fetching pirep custom fields', 'error')
    }
  }

  // Fetch flight types
  const fetchFlightTypes = async () => {
    try {
      const response = await rotateDataService('/settings/jxFetchFlightTypes')
      flightTypes.value = response.data || []
    } catch (e) {
      console.error('Error fetching flight types:', e)
      showToast('Error fetching flight types', 'error')
    }
  }

  // Handle edit route event
  const handleOpenEditDrawer = (event) => {
    page.props.loading = true
    if (routesHeaderRef.value) {
      routesHeaderRef.value.openDrawerForEdit(event.detail)
    }
    page.props.loading = false
  }

  const handleCreatePirep = async (event) => {
    const route = event.detail
    selectedRoute.value = route

    // Ensure required data is loaded
    if (pirepCustomFields.value.length === 0) {
      await fetchPirepCustomFields()
    }
    if (flightTypes.value.length === 0) {
      await fetchFlightTypes()
    }

    // Pre-populate form data with route information
    const estimatedFlightTimeHours = Math.floor(route.flight_time / 60)
    const estimatedFlightTimeMinutes = route.flight_time % 60

    pirepFormData.value = {
      route_id: route.id,
      route_display: `${route.flight_number} - ${route.route}`,
      origin_destination: `${route.origin} → ${route.destination} (${route.distance} NM)`,
      flight_time_hours: estimatedFlightTimeHours,
      flight_time_minutes: estimatedFlightTimeMinutes,
      flight_type_id: null // User must select this
    }

    showPirepDrawer.value = true
  }

  // Submit handler for PIREP creation
  const submitPirepForm = async (payload) => {
    try {
      page.props.loading = true
      
      // Basic validation
      if (!payload.route_id) {
        showToast('Route is required.', 'error')
        page.props.loading = false
        return
      }
      
      // Validate flight time fields
      if (!payload.flight_time_hours || payload.flight_time_hours < 0 || payload.flight_time_hours > 23) {
        showToast('Please enter a valid flight time hours (0-23).', 'error')
        page.props.loading = false
        return
      }
      
      if (payload.flight_time_minutes === undefined || payload.flight_time_minutes < 0 || payload.flight_time_minutes > 59) {
        showToast('Please enter a valid flight time minutes (0-59).', 'error')
        page.props.loading = false
        return
      }
      
      if (!payload.flight_type_id) {
        showToast('Please select a flight type.', 'error')
        page.props.loading = false
        return
      }

      // Separate custom fields from regular fields
      const customData = {}
      const regularData = {}
      
      // Get custom field keys
      const customFieldKeys = pirepCustomFields.value.map(field => field.field_key)
      
      // Separate the data (exclude readonly fields)
      Object.keys(payload).forEach(key => {
        if (key === 'route_display' || key === 'origin_destination') {
          // Skip readonly fields
          return
        }
        
        if (customFieldKeys.includes(key)) {
          customData[key] = payload[key]
        } else {
          regularData[key] = payload[key]
        }
      })
      
      // Add customData to the regular payload
      const finalSubmitPayload = {
        ...regularData,
        customData: customData
      }

      const response = await rotateDataService('/pireps/jxCreateEditPirep', finalSubmitPayload)
      if (response.hasErrors) {
        showToast(response.message, 'error')
        page.props.loading = false
        return;
      }
      
      showToast(response.message || 'PIREP filed successfully!', 'success')
      showPirepDrawer.value = false
      page.props.loading = false
      
      // Reset form data
      pirepFormData.value = {}
      selectedRoute.value = null
      
    } catch (e) {
      console.error(e)
      page.props.loading = false
      showToast('Error occurred while filing PIREP', 'error')
    }
  }

  const updateAnalytics = (analytics) => {
    analyticsData.value = {
      totalRoutes: analytics.totalRoutes || 0,
      activeRoutes: analytics.activeRoutes || 0,
    }
  }

  onMounted(() => {
    window.addEventListener('open-edit-drawer', handleOpenEditDrawer)
    window.addEventListener('create-pirep', handleCreatePirep)
  })

  onUnmounted(() => {
    window.removeEventListener('open-edit-drawer', handleOpenEditDrawer)
    window.removeEventListener('create-pirep', handleCreatePirep)
  })
  </script>
  