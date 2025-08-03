<template>
  <div v-if="isVisible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
      <div class="flex items-center mb-4">
        <div class="flex-shrink-0">
          <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
        <div class="ml-3">
          <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
        </div>
      </div>
      
      <div class="mb-4">
        <p class="text-sm text-gray-700">{{ message }}</p>
        <p v-if="details" class="text-xs text-gray-500 mt-2">{{ details }}</p>
      </div>
      
      <div class="flex justify-end space-x-3">
        <button 
          v-if="showRetry"
          @click="$emit('retry')"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          Retry
        </button>
        <button 
          @click="$emit('close')"
          class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  isVisible: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Error'
  },
  message: {
    type: String,
    default: 'An error occurred'
  },
  details: {
    type: String,
    default: ''
  },
  showRetry: {
    type: Boolean,
    default: false
  }
})

defineEmits(['close', 'retry'])
</script> 