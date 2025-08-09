<template>
  <div class="relative">
    <button 
      @click="toggleDropdown"
      class="text-gray-500 hover:text-gray-700 p-1 rounded"
      title="Actions"
      ref="triggerButton"
    >
      <MoreVerticalIcon class="w-4 h-4" />
    </button>
    
    <!-- Dropdown Menu -->
    <Teleport to="body">
      <div 
        v-if="isOpen"
        :style="dropdownStyle"
        class="fixed z-50 w-48 bg-white border border-gray-200 rounded-md shadow-lg"
        @click.stop
        ref="dropdown"
      >
        <div class="py-1">
          <button 
            v-if="user.permissions.includes('create-pirep')"
            @click="handleCreatePirep"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
          >
            <PlaneIcon class="w-4 h-4 mr-3" />
            File Pirep
          </button>
          <button 
            v-if="user.permissions.includes('edit-route')"
            @click="handleEdit"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
          >
            <EditIcon class="w-4 h-4 mr-3" />
            Edit Route
          </button>
          <button 
            v-if="user.permissions.includes('edit-route')"
            @click="handleToggleStatus"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
          >
            <BadgeCheckIcon v-if="!route.status" class="w-4 h-4 mr-3" />
            <BadgeXIcon v-else class="w-4 h-4 mr-3" />
            {{ route.status ? 'Deactivate Route' : 'Activate Route' }}
          </button>
          <div class="border-t border-gray-100 my-1"></div>
          <button 
            v-if="user.permissions.includes('delete-route')"
            @click="handleDelete"
            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
          >
            <TrashIcon class="w-4 h-4 mr-3" />
            Delete Route
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { MoreVerticalIcon, EditIcon, TrashIcon, BadgeCheckIcon, BadgeXIcon, PlaneIcon } from 'lucide-vue-next'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth.user

// Props
const props = defineProps({
  route: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['edit', 'delete', 'toggle-status', 'create-pirep'])

// Refs
const triggerButton = ref(null)
const dropdown = ref(null)
const isOpen = ref(false)
const dropdownStyle = ref({})

// Methods
const toggleDropdown = async () => {
  if (isOpen.value) {
    closeDropdown()
  } else {
    await openDropdown()
  }
}

const openDropdown = async () => {
  isOpen.value = true
  await nextTick()
  positionDropdown()
}

const closeDropdown = () => {
  isOpen.value = false
}

const positionDropdown = () => {
  if (!triggerButton.value || !dropdown.value) return

  const triggerRect = triggerButton.value.getBoundingClientRect()
  const dropdownRect = dropdown.value.getBoundingClientRect()
  const viewportWidth = window.innerWidth
  const viewportHeight = window.innerHeight

  let left = triggerRect.right - dropdownRect.width
  let top = triggerRect.bottom + 4

  // Adjust if dropdown would go off the right edge
  if (left < 8) {
    left = triggerRect.left
  }

  // Adjust if dropdown would go off the left edge
  if (left + dropdownRect.width > viewportWidth - 8) {
    left = viewportWidth - dropdownRect.width - 8
  }

  // Adjust if dropdown would go off the bottom edge
  if (top + dropdownRect.height > viewportHeight - 8) {
    top = triggerRect.top - dropdownRect.height - 4
  }

  // Ensure dropdown doesn't go above the viewport
  if (top < 8) {
    top = 8
  }

  dropdownStyle.value = {
    left: `${left}px`,
    top: `${top}px`
  }
}

// Action handlers
const handleEdit = () => {
  emit('edit', props.route)
  closeDropdown()
}

const handleCreatePirep = () => {
  emit('create-pirep', props.route)
  closeDropdown()
}

const handleDelete = () => {
  emit('delete', props.route)
  closeDropdown()
}

const handleToggleStatus = () => {
  emit('toggle-status', props.route)
  closeDropdown()
}

// Handle clicking outside to close dropdown
const handleClickOutside = (event) => {
  if (!triggerButton.value?.contains(event.target) && 
      !dropdown.value?.contains(event.target)) {
    closeDropdown()
  }
}

// Handle window resize to reposition dropdown
const handleResize = () => {
  if (isOpen.value) {
    positionDropdown()
  }
}

// Handle escape key to close dropdown
const handleKeydown = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', handleResize)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('keydown', handleKeydown)
})
</script>