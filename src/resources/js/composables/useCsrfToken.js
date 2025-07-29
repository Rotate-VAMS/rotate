import { usePage } from '@inertiajs/vue3'

export function useCsrfToken() {
  const page = usePage()
  
  const getCsrfToken = () => {
    return page.props.csrf_token || null
  }
  
  return {
    getCsrfToken
  }
} 