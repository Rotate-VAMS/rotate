<template>
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div
        v-for="(toast, index) in toasts"
        :key="index"
        :class="[
          'p-3 rounded shadow text-white',
          toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'
        ]"
      >
        {{ toast.message }}
      </div>
    </div>
</template>
  
<script setup>
  import { ref, onMounted } from 'vue'
  
  const toasts = ref([])
  
  onMounted(() => {
    window.addEventListener('toast', (e) => {
      toasts.value.push(e.detail)
  
      // auto-remove after 3s
      setTimeout(() => {
        toasts.value.shift()
      }, 3000)
    })
  })
</script>