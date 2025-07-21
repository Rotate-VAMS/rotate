<template>
    <div class="p-6 bg-white rounded-xl shadow-sm relative">
      <div class="flex flex-row justify-between items-center mb-2">
        <h2 class="text-lg font-semibold">Roles</h2>
        <button
          @click="openDrawerForCreate"
          class="btn-primary bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-bold rounded-md px-4 py-2 flex items-center gap-2"
        >
          <PlusIcon class="w-4 h-4" /> Create New Role
        </button>
      </div>
      <p>Manage your roles and their configurations.</p>
  
      <!-- Ranks Card Grid -->
      <div v-if="roles.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        <div
          v-for="role in roles"
          :key="role.id"
          class="bg-gray-50 border border-gray-200 rounded-lg p-5 flex flex-col justify-between shadow-sm"
        >
          <div>
            <h3 class="font-semibold text-base mb-1">{{ role.name }}</h3>
          </div>
          <div class="flex gap-2" v-if="role.name !== 'admin'">
            <button class="btn btn-sm flex items-center gap-2" @click="editRole(role)">
              <EditIcon class="w-4 h-4" />
              <span class="text-sm">Edit</span>
            </button>
            <button class="btn btn-sm flex items-center gap-2" @click="deleteRole(role)">
              <TrashIcon class="w-4 h-4" />
              <span class="text-sm">Delete</span>
            </button>
            <button class="btn btn-sm flex items-center gap-2" @click="configureRole(role)">
              <SettingsIcon class="w-4 h-4" />
              <span class="text-sm">Configure</span>
            </button>
          </div>
          <div class="flex gap-2" v-else>
            <span class="text-sm text-gray-500">Admin Role - Cannot be edited or deleted</span>
          </div>
        </div>
      </div>
      <div v-else class="text-gray-400 text-center mt-8">
        No roles found.
      </div>
  
      <!-- Inject RotateFormComponent drawer -->
      <RotateFormComponent
        :visible="showDrawer"
        :title="formMode === 'create' ? 'Create Role' : 'Edit Role'"
        :fields="formFields"
        :initialData="formData"
        :isEditMode="formMode === 'edit'"
        @close="showDrawer = false"
        @submit="submitForm"
      />
    </div>
  
    <!-- Modular RBAC Drawer -->
    <RBACDrawerComponent
      :visible="showRBACDrawer"
      :role="selectedRole"
      :permissions="rbacPermissions"
      :rolePermissions="rbacRolePermissions"
      :onClose="() => showRBACDrawer = false"
      :onToggle="togglePermission"
      :isAdmin="selectedRole?.name === 'admin'"
    />
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import { PlusIcon, EditIcon, TrashIcon, SettingsIcon } from 'lucide-vue-next'
  import RotateFormComponent from '@/Components/RotateFormComponent.vue'
  import RBACDrawerComponent from './RBACDrawerComponent.vue'
  import rotateDataService from '@/rotate.js'
  
  // Drawer state
  const showDrawer = ref(false)
  const showRBACDrawer = ref(false)
  const formMode = ref('create')
  const formData = ref({})
  const selectedRole = ref(null)
  
  // Store fetched roles
  const roles = ref([])
  const permissions = ref([])

  // --- RBAC Permission State ---
  const rbacPermissions = ref([]) // All permissions
  const rbacRolePermissions = ref([]) // Permissions for selected role
  
  // Form fields structure with computed properties to include dynamic options
  const formFields = computed(() => {
    if (formMode.value === 'create') {
      return [
        { 
          name: 'name', 
          label: 'Role Name', 
          type: 'text', 
          required: true,
        }
      ]
    } else {
      // Edit mode - only minimum rank is editable
      return [
        { 
          name: 'name', 
          label: 'Role Name', 
          type: 'text', 
          required: true,
        }
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
  const editRole = (role) => {
    formMode.value = 'edit'
    formData.value = { 
      ...role,
    }
    showDrawer.value = true
  }
  const deleteRole = async (role) => {
    if (confirm(`Delete "${role.name}"?`)) {
      const response = await rotateDataService('/settings/jxDeleteRole', { id: role.id })
      if (!response.hasErrors) {
        alert(response.message)
        fetchRoles()
      }
    }
  }

  // Open RBAC drawer for a role
  const configureRole = (role) => {
    selectedRole.value = role
    rbacRolePermissions.value = role.permissions ? role.permissions.map(p => p.id) : []
    showRBACDrawer.value = true
  }
  
  // Submit handler
  const submitForm = async (payload) => {
    try {
      const response = await rotateDataService('/settings/jxCreateEditRole', payload)
      // Optional: show success toast, refresh list
      showDrawer.value = false
      fetchRoles()
    } catch (e) {
      console.error(e)
    }
  }
  
  // Fetch roles
  const fetchRoles = async () => {
    try {
      const response = await rotateDataService('/settings/jxFetchRoles')
      roles.value = response.data || []
    } catch (e) {
      console.error(e)
    }
  }

  // Fetch all permissions (for RBAC grid)
  const fetchPermissions = async () => {
    try {
      const response = await rotateDataService('/settings/jxFetchPermissions')
      permissions.value = response.data || []
      rbacPermissions.value = response.data || []
    } catch (e) {
      console.error(e)
    }
  }

  // Toggle permission for a role
  const togglePermission = async (permissionId, enabled) => {
    if (!selectedRole.value) return
    const roleId = selectedRole.value.id
    try {
      if (enabled) {
        await rotateDataService('/settings/jxGiveRolePermissions', { role_id: roleId, permission_id: permissionId })
        rbacRolePermissions.value.push(permissionId)
      } else {
        await rotateDataService('/settings/jxRevokeRolePermissions', { role_id: roleId, permission_id: permissionId })
        rbacRolePermissions.value = rbacRolePermissions.value.filter(id => id !== permissionId)
      }
      // Optionally, refresh roles to update their permissions
      fetchRoles()
    } catch (e) {
      console.error(e)
    }
  }

  fetchRoles()
  fetchPermissions()
  </script>