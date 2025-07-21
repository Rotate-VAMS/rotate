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
            @click="removeCustomValue(field.id, value, index)"
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
import { ref, computed, onMounted } from 'vue'
import { XIcon, PlusIcon, TrashIcon } from 'lucide-vue-next'
import rotateDataService from '@/rotate.js'

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
const loading = ref(false)

// Fetch current dropdown config and options on mount
const fetchDropdownConfig = async () => {
  if (!props.field?.id) return
  loading.value = true
  try {
    // Fetch the field to get dropdown_value_type
    // (Assume field already has dropdown_value_type if passed from parent, else fetch all fields and find it)
    selectedValueType.value = props.field.dropdown_value_type || ''
    // Fetch options if custom input
    if (selectedValueType.value === 7 || selectedValueType.value === '7') {
      const response = await rotateDataService('/settings/jxFetchCustomFieldOptions?field_id=' + props.field.id)
      if (!response.hasErrors && Array.isArray(response.data)) {
        customValues.value = response.data.map(label => ({ label, value: label }))
      } else {
        customValues.value = []
      }
    } else {
      customValues.value = []
    }
  } catch (e) {
    customValues.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDropdownConfig()
})

// Computed
const canSave = computed(() => {
  if (selectedValueType.value === 7 || selectedValueType.value === '7') {
    return customValues.value.length > 0 && customValues.value.every(v => v.label && v.label.trim())
  }
  return selectedValueType.value !== ''
})

// Methods
const handleValueTypeChange = async () => {
  if (selectedValueType.value === 7 || selectedValueType.value === '7') {
    customValues.value = []
  } else {
    customValues.value = []
  }
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

const addCustomValue = () => {
  customValues.value.push({
    label: '',
    value: ''
  })
}

const removeCustomValue = async (field_id, value, index) => {
  // If the value exists in backend, delete it
  const response = await rotateDataService('/settings/jxDeleteCustomFieldOption', { value: value.value, field_id: field_id })
  if (!response.hasErrors) {
    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Option deleted' } }))
  } else {
    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: response.errors?.[0] || 'Delete failed' } }))
  }
  customValues.value.splice(index, 1)
}

const saveConfiguration = async () => {
  loading.value = true
  try {
    let payload = {
      field_id: props.field.id,
      dropdown_value_type: Number(selectedValueType.value),
      options: []
    }
    if (Number(selectedValueType.value) === 7) {
      payload.options = customValues.value.map(v => v.label)
    } else {
      payload.options = []
    }
    const response = await rotateDataService('/settings/jxCreateEditCustomFieldOptions', payload)
    if (!response.hasErrors) {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: response.message || 'Configuration saved' } }))
      emit('save', payload)
    } else {
      window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: response.errors?.[0] || 'Save failed' } }))
    }
  } catch (e) {
    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Save failed' } }))
  } finally {
    loading.value = false
  }
}
</script>