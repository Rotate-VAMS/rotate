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
              v-for="field in visibleFields"
              :key="field.name || field.group"
              class="space-y-1"
            >
              <!-- Handle grouped fields -->
              <div v-if="field.group" class="space-y-2">
                <label class="text-sm font-medium text-gray-700">
                  {{ field.group }}
                </label>
                <div class="grid grid-cols-2 gap-4">
                  <div
                    v-for="subField in field.fields"
                    :key="subField.name"
                    class="space-y-1"
                  >
                    <label :for="subField.name" class="text-sm font-medium text-gray-700">
                      {{ subField.label }}
                      <span v-if="subField.required" class="text-red-500">*</span>
                    </label>
                    
                    <!-- Render sub-field based on type -->
                    <input
                      v-if="subField.type === 'text' || subField.type === 'number' || subField.type === 'rank_time' || subField.type === 'float'"
                      :type="subField.type === 'number' || subField.type === 'rank_time' || subField.type === 'float' ? 'number' : 'text'"
                      :id="subField.name"
                      :value="formData[subField.name]"
                      :readonly="subField.readonly"
                      :disabled="subField.disabled"
                      :placeholder="subField.placeholder"
                      :min="subField.min"
                      :max="subField.max"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                      :class="{ 'bg-gray-100': subField.readonly || subField.disabled }"
                      @input="handleInput($event, subField)"
                    />

                    <input
                      v-else-if="subField.type === 'time'"
                      type="time"
                      :id="subField.name"
                      v-model="formData[subField.name]"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />

                    <input
                      v-else-if="subField.type === 'datetime-local'"
                      type="datetime-local"
                      :id="subField.name"
                      v-model="formData[subField.name]"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />

                    <textarea
                      v-else-if="subField.type === 'textarea'"
                      :id="subField.name"
                      v-model="formData[subField.name]"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    ></textarea>

                    <select
                      v-else-if="subField.type === 'select'"
                      :id="subField.name"
                      v-model="formData[subField.name]"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                      <option disabled value="">Select...</option>
                      <option
                        v-for="option in subField.options"
                        :key="option.id"
                        :value="option.id"
                      >
                        {{ option.name }}
                      </option>
                    </select>

                    <div
                      v-else-if="subField.type === 'checkbox'"
                      class="space-y-2"
                    >
                      <div class="flex items-center space-x-3">
                        <label :for="subField.name" class="inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            :id="subField.name"
                            v-model="formData[subField.name]"
                            class="sr-only peer"
                          >
                          <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                        </label>
                        <span class="text-sm font-medium text-gray-700">{{ subField.label }}</span>
                      </div>
                      <p v-if="subField.helperText" class="text-xs text-gray-500 ml-14">{{ subField.helperText }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Handle regular fields -->
              <div v-else>
                <label :for="field.name" class="text-sm font-medium text-gray-700">
                  {{ field.label }}
                  <span v-if="field.conditional?.required" class="text-red-500">*</span>
                </label>
                <!-- change background to grey if readonly -->
                <input
                  v-if="field.type === 'text' || field.type === 'number' || field.type === 'rank_time' || field.type === 'float'"
                  :type="field.type === 'number' || field.type === 'rank_time' || field.type === 'float' ? 'number' : 'text'"
                  :id="field.name"
                  :value="formData[field.name]"
                  :readonly="field.readonly"
                  :disabled="field.disabled"
                  :placeholder="field.placeholder"
                  :min="field.min"
                  :max="field.max"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md"
                  :class="{ 'bg-gray-100': field.readonly || field.disabled }"
                  @input="handleInput($event, field)"
                />

                <input
                  v-else-if="field.type === 'time'"
                  type="time"
                  :id="field.name"
                  v-model="formData[field.name]"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md"
                />

                <input
                  v-else-if="field.type === 'datetime-local'"
                  type="datetime-local"
                  :id="field.name"
                  v-model="formData[field.name]"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md"
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

                <!-- Searchable single select dropdown -->
                <div
                  v-else-if="field.type === 'searchable-select'"
                  class="relative"
                >
                  <!-- Selected item display -->
                  <div class="min-h-[42px] p-2 border border-gray-300 rounded-md bg-white">
                    <div class="flex items-center justify-between">
                      <span v-if="getSelectedOptionName(field.name)" class="text-sm">
                        {{ getSelectedOptionName(field.name) }}
                      </span>
                      <span v-else class="text-gray-400 text-sm">
                        {{ field.placeholder || 'Select...' }}
                      </span>
                      <button
                        type="button"
                        @click="toggleSearchableDropdown(field.name)"
                        class="text-gray-400 hover:text-gray-600"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- Dropdown -->
                  <div
                    v-if="searchableDropdownOpen[field.name]"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
                  >
                    <!-- Search input -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 p-2">
                      <input
                        type="text"
                        :placeholder="field.searchPlaceholder || 'Search...'"
                        v-model="searchableSearchQuery[field.name]"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @input="filterSearchableOptions(field.name)"
                      />
                    </div>

                    <!-- Options list -->
                    <div class="py-1">
                      <div
                        v-for="option in filteredSearchableOptions[field.name]"
                        :key="option.id"
                        @click="selectSearchableOption(field.name, option)"
                        class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-100"
                        :class="{ 'bg-blue-50': isSearchableOptionSelected(field.name, option) }"
                      >
                        {{ option.name }}
                      </div>
                      <div
                        v-if="filteredSearchableOptions[field.name].length === 0"
                        class="px-3 py-2 text-sm text-gray-500"
                      >
                        No options found
                      </div>
                    </div>
                  </div>

                  <!-- Click outside to close -->
                  <div
                    v-if="searchableDropdownOpen[field.name]"
                    class="fixed inset-0 z-40"
                    @click="closeSearchableDropdown(field.name)"
                  ></div>
                </div>

                <!-- Multi-select dropdown with search -->
                <div
                  v-else-if="field.type === 'multiselect'"
                  class="relative"
                >
                  <!-- Selected items display -->
                  <div class="min-h-[42px] p-2 border border-gray-300 rounded-md bg-white">
                    <div class="flex flex-wrap gap-1">
                      <div
                        v-for="(item, index) in formData[field.name] || []"
                        :key="index"
                        class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-md"
                      >
                        <span>{{ item }}</span>
                        <button
                          type="button"
                          @click="removeSelectedItem(field.name, index)"
                          class="text-blue-600 hover:text-blue-800"
                        >
                          <XIcon class="w-3 h-3" />
                        </button>
                      </div>
                      <input
                        v-if="!dropdownOpen[field.name]"
                        type="text"
                        :placeholder="field.placeholder || 'Search and select...'"
                        class="flex-1 min-w-0 border-none outline-none text-sm"
                        @focus="openDropdown(field.name)"
                        readonly
                      />
                    </div>
                  </div>

                  <!-- Dropdown -->
                  <div
                    v-if="dropdownOpen[field.name]"
                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
                  >
                    <!-- Search input -->
                    <div class="sticky top-0 bg-white border-b border-gray-200 p-2">
                      <input
                        type="text"
                        :placeholder="field.searchPlaceholder || 'Search...'"
                        v-model="searchQuery[field.name]"
                        class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @input="filterOptions(field.name)"
                      />
                    </div>

                    <!-- Options list -->
                    <div class="py-1">
                      <div
                        v-for="option in filteredOptions[field.name]"
                        :key="option"
                        @click="toggleSelection(field.name, option)"
                        class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 flex items-center gap-2"
                        :class="{ 'bg-blue-50': isSelected(field.name, option) }"
                      >
                        <div class="w-4 h-4 border border-gray-300 rounded flex items-center justify-center">
                          <div
                            v-if="isSelected(field.name, option)"
                            class="w-2 h-2 bg-blue-600 rounded-sm"
                          ></div>
                        </div>
                        <span>{{ option }}</span>
                      </div>
                      <div
                        v-if="filteredOptions[field.name].length === 0"
                        class="px-3 py-2 text-sm text-gray-500"
                      >
                        No options found
                      </div>
                    </div>
                  </div>

                  <!-- Click outside to close -->
                  <div
                    v-if="dropdownOpen[field.name]"
                    class="fixed inset-0 z-40"
                    @click="closeDropdown(field.name)"
                  ></div>
                </div>

                <div
                  v-else-if="field.type === 'checkbox'"
                  class="space-y-2"
                >
                  <div class="flex items-center space-x-3">
                    <label :for="field.name" class="inline-flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        :id="field.name"
                        v-model="formData[field.name]"
                        class="sr-only peer"
                      >
                      <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                    </label>
                    <span class="text-sm font-medium text-gray-700">{{ field.label }}</span>
                  </div>
                  <p v-if="field.helperText" class="text-xs text-gray-500 ml-14">{{ field.helperText }}</p>
                </div>

                <!-- File Upload Dropzone -->
                <div
                  v-else-if="field.type === 'file'"
                  class="flex items-center justify-center w-full"
                >
                  <label :for="field.name" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <Upload class="w-8 h-8 mb-4 text-gray-500" />
                      <p class="mb-2 text-sm text-gray-500">
                        <span class="font-semibold">Click to upload</span> or drag and drop
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ field.acceptedTypes || 'All file types' }} 
                        {{ field.maxSize ? `(MAX. ${field.maxSize})` : '' }}
                      </p>
                      <div v-if="formData[field.name]" class="mt-2 text-sm text-green-600">
                        File selected: {{ getFileName(formData[field.name]) }}
                      </div>
                    </div>
                    <input 
                      :id="field.name" 
                      type="file" 
                      class="hidden" 
                      :accept="field.acceptedTypes"
                      @change="handleFileChange($event, field)"
                    />
                  </label>
                </div>
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
  import { ref, watch, reactive, computed } from 'vue'
  import { XIcon, Upload } from 'lucide-vue-next'
  
  const props = defineProps({
    title: { type: String, default: 'Item' },
    fields: { type: Array, required: true }, // [{ name: 'title', label: 'Title', type: 'text' }]
    initialData: { type: Object, default: () => ({}) },
    isEditMode: { type: Boolean, default: false },
    visible: { type: Boolean, default: false },
  })
  
  const emit = defineEmits(['submit', 'close'])
  
  const formData = reactive({})
  
  // Multi-select dropdown state
  const dropdownOpen = reactive({})
  const searchQuery = reactive({})
  const filteredOptions = reactive({})

  // Searchable single select dropdown state
  const searchableDropdownOpen = reactive({})
  const searchableSearchQuery = reactive({})
  const filteredSearchableOptions = reactive({})

  // Computed property to filter fields based on conditional logic
  const visibleFields = computed(() => {
    return props.fields.filter(field => {
      // If no conditional logic, show the field
      if (!field.conditional) {
        return true
      }

      const { field: conditionalField, value, show } = field.conditional
      
      // Get the value of the conditional field
      const conditionalValue = formData[conditionalField]
      
      // Check if the condition is met
      if (show === true) {
        // Show when condition is met
        return conditionalValue === value
      } else if (show === false) {
        // Hide when condition is met
        return conditionalValue !== value
      }
      
      // Default behavior: show when condition is met
      return conditionalValue === value
    })
  })
  
  const closeDrawer = () => {
    emit('close')
  }
  
  const handleSubmit = () => {
    emit('submit', { ...formData })
    closeDrawer()
  }

  const handleInput = (event, field) => {
    let value = event.target.value
    
    // Block text input for number and rank_time fields
    if (field.type === 'number' || field.type === 'rank_time' || field.type == 'float') {
      // Remove any non-numeric characters except decimal point
      const numericValue = value.replace(/[^0-9.]/g, '')
      // Ensure only one decimal point
      const parts = numericValue.split('.')
      if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('')
      } else {
        value = numericValue
      }
    }
    
    // Apply transform function if provided
    if (field.transform && typeof field.transform === 'function') {
      value = field.transform(value)
    }
    
    // Update the form data with the processed value
    formData[field.name] = value
  }

  // Multi-select dropdown functions
  const openDropdown = (fieldName) => {
    dropdownOpen[fieldName] = true
    // Initialize filtered options if not already done
    if (!filteredOptions[fieldName]) {
      const field = props.fields.find(f => f.name === fieldName)
      if (field && field.options) {
        filteredOptions[fieldName] = [...field.options]
      }
    }
  }

  const closeDropdown = (fieldName) => {
    dropdownOpen[fieldName] = false
    searchQuery[fieldName] = ''
    // Reset filtered options to show all
    const field = props.fields.find(f => f.name === fieldName)
    if (field && field.options) {
      filteredOptions[fieldName] = [...field.options]
    }
  }

  const filterOptions = (fieldName) => {
    const field = props.fields.find(f => f.name === fieldName)
    if (field && field.options) {
      const query = searchQuery[fieldName] || ''
      filteredOptions[fieldName] = field.options.filter(option =>
        option.toLowerCase().includes(query.toLowerCase())
      )
    }
  }

  const toggleSelection = (fieldName, option) => {
    if (!formData[fieldName]) {
      formData[fieldName] = []
    }
    
    const index = formData[fieldName].indexOf(option)
    if (index > -1) {
      formData[fieldName].splice(index, 1)
    } else {
      formData[fieldName].push(option)
    }
  }

  const removeSelectedItem = (fieldName, index) => {
    if (formData[fieldName]) {
      formData[fieldName].splice(index, 1)
    }
  }

  const isSelected = (fieldName, option) => {
    return formData[fieldName] && formData[fieldName].includes(option)
  }

  // Searchable single select dropdown functions
  const toggleSearchableDropdown = (fieldName) => {
    searchableDropdownOpen[fieldName] = !searchableDropdownOpen[fieldName]
    if (searchableDropdownOpen[fieldName]) {
      const field = props.fields.find(f => f.name === fieldName)
      if (field && field.options) {
        filteredSearchableOptions[fieldName] = [...field.options]
      }
    }
  }

  const closeSearchableDropdown = (fieldName) => {
    searchableDropdownOpen[fieldName] = false
    searchableSearchQuery[fieldName] = ''
    // Reset filtered options to show all
    const field = props.fields.find(f => f.name === fieldName)
    if (field && field.options) {
      filteredSearchableOptions[fieldName] = [...field.options]
    }
  }

  const filterSearchableOptions = (fieldName) => {
    const field = props.fields.find(f => f.name === fieldName)
    if (field && field.options) {
      const query = searchableSearchQuery[fieldName] || ''
      filteredSearchableOptions[fieldName] = field.options.filter(option =>
        option.name.toLowerCase().includes(query.toLowerCase())
      )
    }
  }

  const selectSearchableOption = (fieldName, option) => {
    formData[fieldName] = option.id
    closeSearchableDropdown(fieldName)
  }

  const isSearchableOptionSelected = (fieldName, option) => {
    return formData[fieldName] === option.id
  }

  const getSelectedOptionName = (fieldName) => {
    const field = props.fields.find(f => f.name === fieldName)
    if (!field || !field.options) return null
    const selectedOption = field.options.find(option => option.id === formData[fieldName])
    return selectedOption ? selectedOption.name : null
  }

  const handleFileChange = (event, field) => {
    const file = event.target.files[0]
    if (file) {
      // Validate file type if specified
      if (field.acceptedTypes) {
        const acceptedTypes = field.acceptedTypes.split(',').map(type => type.trim())
        const fileType = file.type
        const isValidType = acceptedTypes.some(type => {
          if (type.startsWith('.')) {
            // File extension check
            return file.name.toLowerCase().endsWith(type.toLowerCase())
          } else {
            // MIME type check
            return fileType === type || fileType.startsWith(type.split('/')[0] + '/')
          }
        })
        
        if (!isValidType) {
          alert(`Invalid file type. Accepted types: ${field.acceptedTypes}`)
          event.target.value = ''
          formData[field.name] = null
          return
        }
      }

      // Validate file size if specified
      if (field.maxSize) {
        const maxSizeInBytes = parseFileSize(field.maxSize)
        if (file.size > maxSizeInBytes) {
          alert(`File too large. Maximum size: ${field.maxSize}`)
          event.target.value = ''
          formData[field.name] = null
          return
        }
      }

      formData[field.name] = file
    } else {
      formData[field.name] = null
    }
  }

  const getFileName = (file) => {
    if (!file) return ''
    return file.name || 'Selected file'
  }

  const parseFileSize = (sizeString) => {
    const units = {
      'B': 1,
      'KB': 1024,
      'MB': 1024 * 1024,
      'GB': 1024 * 1024 * 1024
    }
    
    const match = sizeString.match(/^(\d+(?:\.\d+)?)\s*(B|KB|MB|GB)$/i)
    if (match) {
      const size = parseFloat(match[1])
      const unit = match[2].toUpperCase()
      return size * units[unit]
    }
    
    // Default to bytes if no unit specified
    return parseInt(sizeString) || 0
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