<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="flex items-center gap-3 mb-4 sm:mb-0">
        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center">
          <ShieldCheck class="w-6 h-6 text-white" />
        </div>
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Ranks</h1>
          <p class="text-gray-600 text-sm sm:text-base">Manage your ranks</p>
        </div>
      </div>
      <div class="flex flex-row justify-between items-center mb-2">
        <button
          @click="openDrawerForCreate"
          class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Create New Rank
        </button>
      </div>
    </div>
    <div class="p-6 bg-white rounded-xl shadow-sm relative">
      <!-- Ranks Card Grid -->
      <div v-if="ranks.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        <div
          v-for="rank in ranks"
          :key="rank.id"
          class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col justify-between shadow-sm"
        >
          <div>
            <h3 class="font-semibold text-base mb-1">{{ rank.name }}</h3>
            <p class="text-gray-600 text-sm mb-4">{{ rank.min_hours }} hours</p>
          </div>
          <div class="flex gap-2">
            <button class="btn btn-sm flex items-center gap-2" @click="editRank(rank)">
              <EditIcon class="w-4 h-4" />
              <span class="text-sm">Edit</span>
            </button>
            <button class="btn btn-sm flex items-center gap-2" @click="deleteRank(rank)">
              <TrashIcon class="w-4 h-4" />
              <span class="text-sm">Delete</span>
            </button>
          </div>
        </div>
      </div>
      <div v-else class="text-gray-400 text-center mt-8">
        No ranks found.
      </div>

      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Rank' : 'Edit Rank'"
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
import { ref, inject } from 'vue'
import { PlusIcon, EditIcon, TrashIcon, ShieldCheck } from 'lucide-vue-next'
import RotateFormComponent from '@/Components/RotateFormComponent.vue'
import rotateDataService from '@/rotate.js'

const showToast = inject('showToast')
// Drawer state
const showDrawer = ref(false)
const formMode = ref('create')
const formData = ref({})

// Store fetched ranks
const ranks = ref([])

// Form fields structure
const formFields = [
  { name: 'rank_name', label: 'Rank Name', type: 'text', required: true },
  { name: 'min_hours', label: 'Minimum Hours', type: 'rank_time', required: true },
]

// Open drawer for create
const openDrawerForCreate = () => {
  formMode.value = 'create'
  formData.value = {} // reset
  showDrawer.value = true
}

// Action button handlers
const editRank = (rank) => {
  formMode.value = 'edit'
  formData.value = { ...rank }
  showDrawer.value = true
}
const deleteRank = async (rank) => {
  const response = await rotateDataService('/settings/jxDeleteRank', { id: rank.id })
  if (response.hasErrors) {
    showToast(response.message, 'error')
    return;
  }
  showToast(response.message, 'success')
  fetchRanks()
}

// Submit handler
const submitForm = async (payload) => {
  try {
    const response = await rotateDataService('/settings/jxCreateEditRank', payload)
    if (response.hasErrors) {
      showToast(response.message, 'error')
      return;
    }
    showToast(response.message, 'success')
    showDrawer.value = false
    fetchRanks()
  } catch (e) {
    console.error(e)
  }
}

// Fetch ranks
const fetchRanks = async () => {
  try {
    const response = await rotateDataService('/settings/jxFetchRanks')
    ranks.value = response.data || []
  } catch (e) {
    console.error(e)
  }
}

fetchRanks()
</script>