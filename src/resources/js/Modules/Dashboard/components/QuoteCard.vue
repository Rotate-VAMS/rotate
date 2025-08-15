<template>
  <div class="relative">
    <!-- Quote Card -->
    <div class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 rounded-xl p-4 md:p-5 border border-indigo-100 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
      <!-- Decorative Elements -->
      <div class="absolute top-4 left-4 text-indigo-200">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
          <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
      </div>
      
      <div class="absolute bottom-4 right-4 text-pink-200">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
      </div>

      <!-- Quote Content -->
      <div class="relative z-10">
        <blockquote class="text-sm md:text-base lg:text-lg font-medium text-gray-800 leading-relaxed mb-3 italic">
          "{{ parsedQuote.text }}"
        </blockquote>
        
        <!-- Author -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 md:w-8 md:h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
              <svg class="w-3 h-3 md:w-4 md:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div>
              <cite class="text-xs font-semibold text-gray-700 not-italic">
                {{ parsedQuote.author }}
              </cite>
              <div class="text-xs text-gray-500 hidden md:block">
                {{ parsedQuote.category || 'Inspirational' }}
              </div>
            </div>
          </div>
          
          <!-- Share Button -->
          <button 
            @click="shareQuote"
            class="p-1 text-gray-400 hover:text-indigo-600 transition-colors duration-200 rounded-full hover:bg-indigo-50"
            title="Share this quote"
          >
            <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Animated Background Elements -->
      <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
        <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-br from-indigo-200/20 to-purple-200/20 rounded-full blur-xl"></div>
        <div class="absolute -bottom-4 -left-4 w-20 h-20 bg-gradient-to-tr from-pink-200/20 to-purple-200/20 rounded-full blur-xl"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  quote: {
    type: String,
    required: true
  }
})

// Parse the quote string to extract text, author, and category
const parsedQuote = computed(() => {
  const quoteStr = props.quote || ''
  
  // Remove formatting tags and parse the quote
  let cleanQuote = quoteStr
    .replace(/<options=bold>/g, '')
    .replace(/<\/>/g, '')
    .replace(/<fg=gray>/g, '')
    .replace(/<\/>/g, '')
    .trim()
  
  // Extract quote text and author
  const parts = cleanQuote.split('—')
  if (parts.length >= 2) {
    const text = parts[0].replace(/^["\s]+|["\s]+$/g, '') // Remove leading/trailing quotes and spaces
    const author = parts[1].trim()
    
    return {
      text: text,
      author: author,
      category: 'Inspirational'
    }
  }
  
  // Fallback if parsing fails
  return {
    text: cleanQuote,
    author: 'Unknown',
    category: 'Inspirational'
  }
})

const shareQuote = () => {
  const shareText = `"${parsedQuote.value.text}" — ${parsedQuote.value.author}`
  
  if (navigator.share) {
    navigator.share({
      title: 'Daily Quote',
      text: shareText,
      url: window.location.href
    }).catch(() => {
      // Fallback to clipboard if sharing fails
      copyToClipboard(shareText)
    })
  } else {
    // Fallback: copy to clipboard
    copyToClipboard(shareText)
  }
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    // Show a simple notification
    showNotification('Quote copied to clipboard!')
  } catch (err) {
    console.error('Failed to copy quote:', err)
    showNotification('Failed to copy quote', 'error')
  }
}

const showNotification = (message, type = 'success') => {
  // Create a simple toast notification
  const toast = document.createElement('div')
  toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white text-sm font-medium transition-all duration-300 transform translate-x-full ${
    type === 'success' ? 'bg-green-500' : 'bg-red-500'
  }`
  toast.textContent = message
  
  document.body.appendChild(toast)
  
  // Animate in
  setTimeout(() => {
    toast.classList.remove('translate-x-full')
  }, 100)
  
  // Remove after 3 seconds
  setTimeout(() => {
    toast.classList.add('translate-x-full')
    setTimeout(() => {
      document.body.removeChild(toast)
    }, 300)
  }, 3000)
}
</script>

<style scoped>
/* Add some subtle animations */
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-5px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}
</style>
