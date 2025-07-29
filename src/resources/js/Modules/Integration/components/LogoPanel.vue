<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="flex items-center gap-3 mb-4 sm:mb-0">
        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center">
          <ImageIcon class="w-6 h-6 text-white" />
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Logo</h1>
          <p class="text-gray-600 text-sm sm:text-base">Manage your logo</p>
        </div>
      </div>
    </div>
    <div class="p-6 bg-white rounded-xl shadow-sm relative">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        <div
          :key="logo?.id"
          class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col items-center justify-between shadow-sm"
        >
          <img v-if="logo" :src="logo" alt="Logo" class="w-full h-auto object-contain mb-4 rounded" />
          <div v-else class="bg-gradient-to-br from-indigo-500 via-purple-500 to-indigo-500 text-white shadow-md rounded-xl h-20 w-20 flex items-center justify-center text-lg font-bold mb-4">
            <span class="text-white text-3xl font-bold">R</span>
          </div>
          <div class="flex flex-row gap-2">
            <button class="btn btn-sm flex items-center gap-2" @click="editLogo(logo)">
              <EditIcon class="w-4 h-4" />
              <span class="text-sm">Edit</span>
            </button>
            <button v-if="!logoDefault && logo" class="btn btn-sm flex items-center gap-2" @click="deleteLogo(logo)">
              <TrashIcon class="w-4 h-4" />
              <span class="text-sm">Delete</span>
            </button>
          </div>
        </div>
      </div>
  
      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Logo' : 'Logo'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />
    </div>
  </div>
  </template>
  
  <script setup>
  import { ref, computed, inject } from 'vue'
  import { EditIcon, TrashIcon, ImageIcon } from 'lucide-vue-next'
  import RotateFormComponent from '@/Components/RotateFormComponent.vue'
  import rotateDataService from '@/rotate.js'

  const _Documents = {
    DOCUMENT_TYPE_IMAGE: 1,
    DOCUMENT_TYPE_VIDEO: 2,
    DOCUMENT_TYPE_AUDIO: 3,
    DOCUMENT_TYPE_DOCUMENT: 4
  }
  const logo = inject('logo')
  const logoDefault = inject('logoDefault')
  const showToast = inject('showToast')
  // Drawer state
  const showDrawer = ref(false)
  const formMode = ref('create')
  const formData = ref({})

  // Form fields structure with computed properties to include dynamic options
  const formFields = computed(() => {
    if (formMode.value === 'create') {
      return [
        { 
          name: 'logo', 
          label: 'Logo', 
          type: 'file', 
          required: true,
          acceptedTypes: 'image/jpeg,image/png,image/jpg,image/gif,image/svg+xml',
          maxSize: '2MB'
        }
      ]
    } else {
      // Edit mode - only minimum rank is editable
      return [
        { 
          name: 'logo', 
          label: 'Logo', 
          type: 'file', 
          required: true,
          acceptedTypes: 'image/jpeg,image/png,image/jpg,image/gif,image/svg+xml',
          maxSize: '2MB'
        },
      ]
    }
  })
  
  // Open drawer for create
  const openDrawerForCreate = () => {
    formMode.value = 'create'
    formData.value = {} // reset
    showDrawer.value = true
  }
  
  // Action button handlers
  const editLogo = (logo) => {
    formMode.value = 'edit'
    formData.value = { 
      ...logo,
    }
    showDrawer.value = true
  }
  const deleteLogo = async (logo) => {
      const response = await rotateDataService('/settings/jxDeleteLogo', { id: logo.id })
      if (response.hasErrors) {
        showToast(response.message, 'error')
        return;
      }
      showToast(response.message, 'success')
      window.location.reload();
  }
  
  // Submit handler
  const submitForm = async (payload) => {
    try {
      // Only keep the 'logo' property from the payload
      const processedPayload = {};
      if (payload.logo && payload.logo instanceof File) {
        const file = payload.logo;
        const reader = new FileReader();
        reader.onload = async () => {
          const base64Data = reader.result.split(',')[1];
          processedPayload.logo = {
            document_name: file.name,
            document_type: _Documents.DOCUMENT_TYPE_IMAGE,
            document_data: base64Data
          };
          await sendRequest(processedPayload);
        };
        reader.readAsDataURL(file);
        return;
      } else if (payload.logo && typeof payload.logo === 'object') {
        processedPayload.logo = payload.logo;
      }
      await sendRequest(processedPayload);
    } catch (e) {
      console.error(e);
    }
  };

  const sendRequest = async (payload) => {
    const response = await rotateDataService('/settings/jxCreateEditLogo', payload)
    if (response.hasErrors) {
      showToast(response.message, 'error');
      return;
    }
    showToast(response.message, 'success');
    showDrawer.value = false;
    window.location.reload();
  }

  </script>