<template>
  <div
    class="rotate-toast"
    :class="[
      type,
      { active, overlay }
    ]"
    :style="overlay ? 'position:fixed;top:32px;right:32px;z-index:9999;margin-bottom:0;' : ''"
  >
    <span class="icon">
      <svg v-if="type === 'success'" width="28" height="28" viewBox="0 0 28 28" fill="none"><circle cx="14" cy="14" r="14" fill="#1E2B4A"/><path d="M9 14.5L12.5 18L19 11" stroke="#2ECC40" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      <svg v-else-if="type === 'alert'" width="28" height="28" viewBox="0 0 28 28" fill="none"><circle cx="14" cy="14" r="14" fill="#1E2B4A"/><circle cx="14" cy="14" r="7" stroke="#FFB300" stroke-width="2.2"/><rect x="13" y="10" width="2" height="5" rx="1" fill="#FFB300"/><rect x="13" y="17" width="2" height="2" rx="1" fill="#FFB300"/></svg>
      <svg v-else-if="type === 'error'" width="28" height="28" viewBox="0 0 28 28" fill="none"><circle cx="14" cy="14" r="14" fill="#1E2B4A"/><circle cx="14" cy="14" r="7" stroke="#FF4D4F" stroke-width="2.2"/><path d="M11.8 11.8L16.2 16.2M16.2 11.8L11.8 16.2" stroke="#FF4D4F" stroke-width="2.2" stroke-linecap="round"/></svg>
    </span>
    <div class="content">
      <div class="header">{{ type }}</div>
      <div class="message">{{ message }}</div>
    </div>
    <button class="close-btn" @click="$emit('close')" aria-label="Close">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6 6L18 18M18 6L6 18" stroke="#A0AEC0" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'success',
    validator: v => ['success', 'alert', 'error'].includes(v)
  },
  message: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  },
  overlay: {
    type: Boolean,
    default: false
  }
});

defineEmits(['close']);
</script>

<style scoped>
.rotate-toast {
  display: flex;
  align-items: center;
  background: #25325A;
  border-radius: 12px;
  box-shadow: 0 2px 8px 0 rgba(30, 43, 74, 0.10);
  padding: 12px 18px;
  margin-bottom: 16px;
  position: relative;
  min-width: 220px;
  max-width: 340px;
  border: 2px solid transparent;
  transition: border 0.2s;
}
.rotate-toast.active {
  border-color: #2D9CFF;
}
.rotate-toast.overlay {
  position: fixed !important;
  top: 24px;
  right: 24px;
  z-index: 9999;
  margin-bottom: 0 !important;
}
.rotate-toast .icon {
  margin-right: 10px;
  display: flex;
  align-items: center;
}
.rotate-toast .icon svg {
  width: 22px;
  height: 22px;
}
.rotate-toast .content {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.rotate-toast .header {
  color: #fff;
  font-size: 1.08rem;
  font-weight: 500;
  margin-bottom: 0;
}
.rotate-toast .message {
  color: #fff;
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 0;
}
.rotate-toast.success .icon svg {
  /* green check */
}
.rotate-toast.alert .icon svg {
  /* yellow info */
}
.rotate-toast.error .icon svg {
  /* red error */
}
.rotate-toast .close-btn {
  background: none;
  border: none;
  outline: none;
  cursor: pointer;
  margin-left: 10px;
  display: flex;
  align-items: center;
  padding: 0;
  transition: opacity 0.2s;
}
.rotate-toast .close-btn svg {
  width: 18px;
  height: 18px;
}
.rotate-toast .close-btn:hover {
  opacity: 0.7;
}
@media (max-width: 600px) {
  .rotate-toast {
    min-width: 0;
    max-width: 96vw;
    padding: 10px 8px;
    font-size: 0.98rem;
  }
  .rotate-toast.overlay {
    top: 8px;
    right: 4px;
    left: 4px;
    max-width: unset;
    width: auto;
  }
}
</style>
