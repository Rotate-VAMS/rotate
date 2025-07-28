const RotateDataService = {
  install(app) {
    app.config.globalProperties.$apiPost = async function (url, data = {}) {
      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document
              .querySelector('meta[name="csrf-token"]')
              .getAttribute('content'),
          },
          body: JSON.stringify(data),
        })

        if (!response.ok) throw new Error(`HTTP ${response.status}`)
        return await response.json()
      } catch (error) {
        app.config.globalProperties.$toast.error(error.message)
        throw error
      }
    }

    app.config.globalProperties.$toast = {
      success(message) {
        window.dispatchEvent(
          new CustomEvent('toast', { detail: { type: 'success', message } })
        )
      },
      error(message) {
        window.dispatchEvent(
          new CustomEvent('toast', { detail: { type: 'error', message } })
        )
      },
    }
  },

  // Get CSRF token with multiple fallbacks
  getCsrfToken() {
    // Try to get from DOM meta tag first (most reliable)
    const metaTag = document.querySelector('meta[name="csrf-token"]')
    if (metaTag) {
      return metaTag.getAttribute('content')
    }
    
    // Try to get from Inertia props if available
    if (window.Inertia && window.Inertia.props && window.Inertia.props.csrf_token) {
      return window.Inertia.props.csrf_token
    }
    
    return null
  },

  // Service methods that can be used directly
  async get(url, retryCount = 0) {
    try {
      const csrfToken = this.getCsrfToken()
      const headers = {
        'Content-Type': 'application/json',
      }
      
      if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken
      }

      const response = await fetch(url, {
        method: 'GET',
        headers,
      })

      if (response.status === 419 && retryCount < 1) {
        // Token expired, try to refresh page to get new token
        console.warn('CSRF token expired, refreshing page...')
        window.location.reload()
        return
      }

      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      return await response.json()
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  },

  async post(url, data = {}, retryCount = 0) {
    try {
      const csrfToken = this.getCsrfToken()
      const headers = {
        'Content-Type': 'application/json',
      }
      
      if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken
      }

      const response = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(data),
      })

      if (response.status === 419 && retryCount < 1) {
        // Token expired, try to refresh page to get new token
        console.warn('CSRF token expired, refreshing page...')
        window.location.reload()
        return
      }

      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      return await response.json()
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  }
}

// Create a function that can be called directly
const rotateDataService = (url, payload = null) => {
  if (payload) {
    return RotateDataService.post(url, payload)
  }
  return RotateDataService.get(url)
}

export default rotateDataService