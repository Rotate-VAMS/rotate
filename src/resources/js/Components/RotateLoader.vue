<template>
  <div class="rotate-loader" v-if="loading">
    <div class="loader-container">
      <div class="plane text-purple-600">
        <svg width="60" height="60" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M35 20L25 15L20 10L15 5L10 10L15 15L20 20L15 25L10 30L15 35L20 30L25 25L35 20Z" fill="currentColor"/>
        </svg>
      </div>
      <div class="loading-text">Loading...</div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, watch } from 'vue';

const loading = ref(false);
const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
});

watch(() => props.loading, (newVal) => {
  loading.value = newVal;
}, { immediate: true });

</script>

<style scoped>
.rotate-loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(0.1px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loader-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.plane {
  animation: fly 2s ease-in-out infinite;
  transform-origin: center;
}

.plane svg {
  filter: drop-shadow(0 4px 8px rgba(59, 130, 246, 0.3));
}

@keyframes fly {
  0% {
    transform: translateX(-100px) rotate(-15deg) scale(0.8);
    opacity: 0;
  }
  20% {
    opacity: 1;
  }
  50% {
    transform: translateX(0) rotate(0deg) scale(1);
  }
  80% {
    opacity: 1;
  }
  100% {
    transform: translateX(100px) rotate(15deg) scale(0.8);
    opacity: 0;
  }
}

.loading-text {
  font-size: 16px;
  font-weight: 500;
  color: #6b7280;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.6;
  }
  50% {
    opacity: 1;
  }
}

/* Responsive design */
@media (max-width: 768px) {
  .plane svg {
    width: 40px;
    height: 40px;
  }
  
  .loading-text {
    font-size: 18px;
  }
}
</style>