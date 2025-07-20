<template>
  <div class="h-full bg-white shadow-xl overflow-y-auto">
    <div class="p-6">
      <div class="flex flex-row justify-between items-center mb-4">
        <div>
          <h2 class="text-lg font-semibold">Configure Dropdown Options</h2>
          <p class="text-sm text-gray-600 mt-1">{{ field.field_name }}</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-800"
        >
          <XIcon class="w-5 h-5" />
        </button>
      </div>

    <!-- Value Type Selection -->
    <div class="mb-6">
      <label class="text-sm font-medium text-gray-700 mb-2 block">
        Select Value Type
      </label>
      <select
        v-model="selectedValueType"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        @change="handleValueTypeChange"
      >
        <option value="">Select a value type...</option>
        <option :value="1">Routes</option>
        <option :value="2">Users</option>
        <option :value="3">Events</option>
        <option :value="4">Fleet</option>
        <option :value="5">PIREPs</option>
        <option :value="6">Ranks</option>
        <option :value="7">Custom Input</option>
      </select>
    </div>

    <!-- Custom Values Section -->
    <div v-if="selectedValueType === 7" class="space-y-4">
      <div class="flex justify-between items-center">
        <h3 class="text-md font-medium text-gray-700">Custom Values</h3>
        <button
          @click="addCustomValue"
          class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-sm rounded-md px-3 py-1 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Add Value
        </button>
      </div>

      <div v-if="customValues.length === 0" class="text-gray-400 text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
        <p>No custom values added yet.</p>
        <p class="text-sm">Click "Add Value" to start adding options.</p>
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="(value, index) in customValues"
          :key="index"
          class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200"
        >
          <div class="flex-1">
            <input
              v-model="value.label"
              type="text"
              :placeholder="`Option ${index + 1}`"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <button
            @click="removeCustomValue(index)"
            class="text-red-500 hover:text-red-700 p-1"
            title="Remove this value"
          >
            <TrashIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Preview Section -->
    <div v-if="selectedValueType === 7 && customValues.length > 0" class="mt-6 p-4 bg-blue-50 rounded-lg">
      <h4 class="text-sm font-medium text-blue-800 mb-2">Preview</h4>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="(value, index) in customValues"
          :key="index"
          class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-md"
        >
          {{ value.label || `Option ${index + 1}` }}
        </span>
      </div>
    </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-200">
        <button
          @click="$emit('close')"
          class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50"
        >
          Cancel
        </button>
        <button
          @click="saveConfiguration"
          :disabled="!canSave"
          class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
        >
          Save Configuration
        </button>
      </div>

      <!-- RotateFormComponent removed as it's no longer needed -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { XIcon, PlusIcon, TrashIcon } from 'lucide-vue-next'

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  visible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'save'])

// State
const selectedValueType = ref('')
const customValues = ref([])

// Computed
const canSave = computed(() => {
  if (selectedValueType.value === 7) {
    return customValues.value.length > 0 && customValues.value.every(v => v.label.trim())
  }
  return selectedValueType.value !== ''
})

// Methods
const handleValueTypeChange = () => {
  if (selectedValueType.value === 7) {
    // Custom input - values will be managed in this component
    customValues.value = []
  }
  // For other value types, no form needed - handled by backend
}

const getValueTypeName = (valueType) => {
  const names = {
    1: 'Routes',
    2: 'Users', 
    3: 'Events',
    4: 'Fleet',
    5: 'PIREPs',
    6: 'Ranks',
    7: 'Custom Input'
  }
  return names[valueType] || 'Unknown'
}

// Removed form-related methods as they're no longer needed

const addCustomValue = () => {
  customValues.value.push({
    label: '',
    value: ''
  })
}

const removeCustomValue = (index) => {
  customValues.value.splice(index, 1)
}

const saveConfiguration = () => {
  const configuration = {
    fieldId: props.field.id,
    valueType: selectedValueType.value,
    customValues: selectedValueType.value === 7 ? customValues.value : null
  }
  
  emit('save', configuration)
}
</script>