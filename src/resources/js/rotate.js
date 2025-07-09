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

  // Service methods that can be used directly
  async get(url) {
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content'),
        },
      })

      if (!response.ok) throw new Error(`HTTP ${response.status}`)
      return await response.json()
    } catch (error) {
      console.error('API Error:', error)
      throw error
    }
  },

  async post(url, data = {}) {
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