<template>
  <div class="rotate-upgrade-component">
    <!-- Upgrade Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg p-4 sm:p-6 text-white">
      <!-- Mobile Layout -->
      <div class="block sm:hidden">
        <div class="flex flex-col space-y-4">
          <!-- Header with Icon and Title -->
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-base font-semibold">
                {{ title || 'Upgrade Your Plan' }}
              </h3>
            </div>
          </div>
          
          <!-- Message -->
          <p class="text-blue-100 text-sm leading-relaxed">
            {{ message || 'This feature is available on higher tier plans. Upgrade now to unlock all features!' }}
          </p>
          
          <!-- Plan Features -->
          <div v-if="features && features.length > 0" class="space-y-2">
            <p class="text-xs text-blue-200 font-medium">Available in {{ requiredPlan }}:</p>
            <ul class="text-xs text-blue-100 space-y-1">
              <li v-for="feature in features" :key="feature" class="flex items-start">
                <svg class="w-3 h-3 mr-2 mt-0.5 text-green-300 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="leading-relaxed">{{ feature }}</span>
              </li>
            </ul>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex flex-col space-y-2 pt-2">
            <button
              @click="handleUpgrade"
              class="w-full bg-white text-blue-600 px-4 py-3 rounded-md font-medium text-sm hover:bg-blue-50 transition-colors duration-200 flex items-center justify-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              {{ upgradeButtonText || 'Upgrade Now' }}
            </button>
            
            <button
              v-if="showLearnMore"
              @click="handleLearnMore"
              class="w-full text-blue-100 hover:text-white text-sm transition-colors duration-200 py-2"
            >
              {{ learnMoreText || 'Learn More' }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- Desktop Layout -->
      <div class="hidden sm:flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <!-- Upgrade Icon -->
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
          </div>
          
          <!-- Content -->
          <div class="flex-1">
            <h3 class="text-lg font-semibold mb-1">
              {{ title || 'Upgrade Your Plan' }}
            </h3>
            <p class="text-blue-100 text-sm">
              {{ message || 'This feature is available on higher tier plans. Upgrade now to unlock all features!' }}
            </p>
            
            <!-- Plan Features -->
            <div v-if="features && features.length > 0" class="mt-3">
              <p class="text-xs text-blue-200 mb-2">Available in {{ requiredPlan }}:</p>
              <ul class="text-xs text-blue-100 space-y-1">
                <li v-for="feature in features" :key="feature" class="flex items-center">
                  <svg class="w-3 h-3 mr-2 text-green-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                  {{ feature }}
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex-shrink-0 flex flex-col space-y-2">
          <button
            @click="handleUpgrade"
            class="bg-white text-blue-600 px-4 py-2 rounded-md font-medium text-sm hover:bg-blue-50 transition-colors duration-200 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            {{ upgradeButtonText || 'Upgrade Now' }}
          </button>
          
          <button
            v-if="showLearnMore"
            @click="handleLearnMore"
            class="text-blue-100 hover:text-white text-sm transition-colors duration-200"
          >
            {{ learnMoreText || 'Learn More' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RotateUpgradeComponent',
  
  props: {
    // Required props
    requiredPlan: {
      type: String,
      required: true,
      default: 'Pro'
    },
    
    // Optional props with defaults
    title: {
      type: String,
      default: 'Upgrade Your Plan'
    },
    
    message: {
      type: String,
      default: 'This feature is available on higher tier plans. Upgrade now to unlock all features!'
    },
    
    features: {
      type: Array,
      default: () => []
    },
    
    upgradeButtonText: {
      type: String,
      default: 'Upgrade Now'
    },
    
    learnMoreText: {
      type: String,
      default: 'Learn More'
    },
    
    dismissText: {
      type: String,
      default: 'Not now'
    },
    
    // Configuration props
    dismissible: {
      type: Boolean,
      default: true
    },
    
    showLearnMore: {
      type: Boolean,
      default: true
    },
    
    // Custom styling
    variant: {
      type: String,
      default: 'default', // 'default', 'compact', 'minimal'
      validator: value => ['default', 'compact', 'minimal'].includes(value)
    }
  },
  
  emits: ['upgrade', 'learn-more', 'dismiss'],
  
  methods: {
    handleUpgrade() {
      this.$emit('upgrade', {
        requiredPlan: this.requiredPlan,
        features: this.features
      })
    },
    
    handleLearnMore() {
      this.$emit('learn-more', {
        requiredPlan: this.requiredPlan,
        features: this.features
      })
    },
    
    handleDismiss() {
      this.$emit('dismiss')
    }
  }
  }
</script>

<style scoped>
.rotate-upgrade-component {
  width: 100%;
}

/* Mobile-specific styles */
@media (max-width: 640px) {
  .rotate-upgrade-component .bg-gradient-to-r {
    padding: 1rem;
  }
  
  .rotate-upgrade-component h3 {
    font-size: 1rem;
    line-height: 1.5rem;
  }
  
  .rotate-upgrade-component p {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
  
  .rotate-upgrade-component button {
    width: 100%;
  }
}

/* Variant styles */
.rotate-upgrade-component.compact .bg-gradient-to-r {
  padding: 1rem;
}

.rotate-upgrade-component.compact .text-lg {
  font-size: 1rem;
  line-height: 1.5rem;
}

.rotate-upgrade-component.minimal .bg-gradient-to-r {
  padding: 0.75rem;
}

.rotate-upgrade-component.minimal .text-lg {
  font-size: 0.875rem;
  line-height: 1.25rem;
}

.rotate-upgrade-component.minimal .text-sm {
  font-size: 0.75rem;
  line-height: 1rem;
}

/* Ensure proper touch targets on mobile */
@media (max-width: 640px) {
  .rotate-upgrade-component button {
    min-height: 44px;
  }
}
</style>