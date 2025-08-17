<template>
  <div class="relative inline-block">
    <!-- Hamburger Button -->
    <button
      ref="buttonRef"
      data-dropdown
      @click="toggleDropdown"
      class="p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
      :title="`Actions for ${pilot.name}`"
    >
      <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
      </svg>
    </button>

    <!-- Dropdown Menu - Teleported to body -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        data-dropdown
        :style="dropdownStyle"
        class="fixed w-48 bg-white rounded-md shadow-lg z-[9999] border border-gray-200 transform origin-top-right"
      >
      <div class="py-1">
        <!-- Edit Action -->
        <button
          v-if="user.permissions.includes('edit-user')"
          @click="editPilot"
          class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
        >
          <EditIcon class="w-4 h-4 mr-3 text-blue-600" />
          Edit Pilot
        </button>

        <!-- Toggle Status Action -->
        <button
          v-if="user.permissions.includes('edit-user') && user.id != pilot.id"
          @click="togglePilotStatus"
          class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
        >
          <ShieldMinusIcon v-if="pilot.status == '1'" class="w-4 h-4 mr-3 text-red-600" />
          <ShieldCheckIcon v-else class="w-4 h-4 mr-3 text-green-600" />
          {{ pilot.status == '1' ? 'Deactivate' : 'Activate' }} Pilot
        </button>

        <!-- Delete Action -->
        <button
          v-if="user.permissions.includes('delete-user') && user.id != pilot.id"
          @click="deletePilot"
          class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
        >
          <TrashIcon class="w-4 h-4 mr-3 text-red-600" />
          Delete Pilot
        </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { EditIcon, TrashIcon, ShieldCheckIcon, ShieldMinusIcon } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'
import { inject } from 'vue'

const showToast = inject('showToast')
const page = usePage()
const user = page.props.auth.user

// Props
const props = defineProps({
  pilot: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['edit', 'delete', 'toggle-status'])

// Reactive data
const isOpen = ref(false)
const buttonRef = ref(null)
const dropdownStyle = ref({})

// Computed properties
const hasMultipleActions = computed(() => {
  let count = 0
  if (user.permissions.includes('edit-user')) count++
  if (user.permissions.includes('delete-user') && user.id != props.pilot.id) count++
  if (user.permissions.includes('edit-user') && user.id != props.pilot.id) count++
  return count > 1
})

// Methods
const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    updateDropdownPosition()
  }
}

const updateDropdownPosition = () => {
  if (buttonRef.value) {
    const rect = buttonRef.value.getBoundingClientRect()
    dropdownStyle.value = {
      top: `${rect.bottom + 8}px`,
      right: `${window.innerWidth - rect.right}px`
    }
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const editPilot = () => {
  closeDropdown()
  emit('edit', props.pilot)
}

const deletePilot = () => {
  closeDropdown()
  emit('delete', props.pilot)
}

const togglePilotStatus = () => {
  closeDropdown()
  emit('toggle-status', props.pilot)
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (isOpen.value && !event.target.closest('[data-dropdown]')) {
    closeDropdown()
  }
}

// Lifecycle hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
