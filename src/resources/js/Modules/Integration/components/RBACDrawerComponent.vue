<template>
  <transition name="fade">
    <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div
        class="bg-white rounded-xl shadow-lg w-full max-w-md md:max-w-xl relative flex flex-col"
        style="max-height: 90vh; min-width: 90vw; overflow: hidden;"
      >
        <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" @click="onClose">
          <span class="text-2xl">&times;</span>
        </button>
        <div class="p-6 flex-1 flex flex-col overflow-hidden">
          <h2 class="text-lg font-semibold mb-4 text-center">Configure Permissions for {{ role?.name }}</h2>
          <div class="flex-1 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div v-for="perm in permissions" :key="perm.id" class="flex items-center justify-between border-b py-2">
                <span class="capitalize text-sm">{{ perm.name.replace(/[-_]/g, ' ') }}</span>
                <input
                  type="checkbox"
                  :checked="rolePermissions.includes(perm.id)"
                  :disabled="isAdmin"
                  @change="onToggle(perm.id, !$event.target.checked ? false : true)"
                  class="form-checkbox h-5 w-5 text-indigo-600"
                />
              </div>
            </div>
            <div v-if="isAdmin" class="text-xs text-gray-500 mt-4 text-center">Admin role has all permissions and cannot be changed.</div>
          </div>
          <div class="flex justify-end mt-6">
            <button class="btn btn-secondary" @click="onClose">Close</button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  visible: Boolean,
  role: Object,
  permissions: Array,
  rolePermissions: Array,
  onClose: Function,
  onToggle: Function,
  isAdmin: Boolean
})
</script> 