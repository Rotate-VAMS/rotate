<template>
    <div>
      <!-- Drawer Panel -->
      <Transition name="slide">
        <div
          v-if="visible"
          class="fixed top-[64px] right-0 z-[9999] h-[calc(100vh-64px)] w-full lg:w-1/2 bg-white shadow-xl overflow-y-auto transition-all duration-300"
        >
          <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">
              {{ isEditMode ? 'Edit' : 'Create' }} {{ title }}
            </h2>
            <button @click="closeDrawer" class="text-gray-500 hover:text-gray-800">
              <XIcon class="w-5 h-5" />
            </button>
          </div>
  
          <form @submit.prevent="handleSubmit" class="p-4 space-y-4">
            <div
              v-for="field in fields"
              :key="field.name"
              class="space-y-1"
            >
              <label :for="field.name" class="text-sm font-medium text-gray-700">
                {{ field.label }}
              </label>
  
              <input
                v-if="field.type === 'text' || field.type === 'number' || field.type === 'rank_time'"
                :type="field.type === 'number' || field.type === 'rank_time' ? 'number' : 'text'"
                :id="field.name"
                v-model="formData[field.name]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
                @input="handleInput($event, field)"
              />
  
              <textarea
                v-else-if="field.type === 'textarea'"
                :id="field.name"
                v-model="formData[field.name]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
              ></textarea>
  
              <select
                v-else-if="field.type === 'select'"
                :id="field.name"
                v-model="formData[field.name]"
                class="w-full px-3 py-2 border border-gray-300 rounded-md"
              >
                <option disabled value="">Select...</option>
                <option
                  v-for="option in field.options"
                  :key="option.id"
                  :value="option.id"
                >
                  {{ option.name }}
                </option>
              </select>

              <div
                v-else-if="field.type === 'checkbox'"
                class="flex items-center space-x-3 py-2"
              >
                <label :for="field.name" class="inline-flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    :id="field.name"
                    v-model="formData[field.name]"
                    class="sr-only peer"
                  >
                  <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                </label>
              </div>
            </div>
  
            <div class="flex justify-end gap-2 pt-4">
              <button
                type="button"
                @click="closeDrawer"
                class="px-4 py-2 rounded-md border text-sm hover:bg-gray-100"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700"
              >
                {{ isEditMode ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
</template>
  
<script setup>
  import { ref, watch, reactive } from 'vue'
  import { XIcon } from 'lucide-vue-next'
  
  const props = defineProps({
    title: { type: String, default: 'Item' },
    fields: { type: Array, required: true }, // [{ name: 'title', label: 'Title', type: 'text' }]
    initialData: { type: Object, default: () => ({}) },
    isEditMode: { type: Boolean, default: false },
    visible: { type: Boolean, default: false },
  })
  
  const emit = defineEmits(['submit', 'close'])
  
  const formData = reactive({})
  
  const closeDrawer = () => {
    emit('close')
  }
  
  const handleSubmit = () => {
    emit('submit', { ...formData })
    closeDrawer()
  }

  const handleInput = (event, field) => {
    // Block text input for number and rank_time fields
    if (field.type === 'number' || field.type === 'rank_time') {
      const value = event.target.value
      // Remove any non-numeric characters except decimal point
      const numericValue = value.replace(/[^0-9.]/g, '')
      // Ensure only one decimal point
      const parts = numericValue.split('.')
      if (parts.length > 2) {
        event.target.value = parts[0] + '.' + parts.slice(1).join('')
      } else {
        event.target.value = numericValue
      }
      // Update the form data with the cleaned value
      formData[field.name] = event.target.value
    }
  }
  
  watch(
    () => props.initialData,
    (newVal) => {
      Object.assign(formData, newVal)
    },
    { immediate: true, deep: true }
  )
</script>
  
<style scoped>
  .slide-enter-active,
  .slide-leave-active {
    transition: transform 0.3s ease;
  }
  .slide-enter-from,
  .slide-leave-to {
    transform: translateX(100%);
  }
</style>