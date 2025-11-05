export const useAuth = () => {
  const { $api } = useNuxtApp()
  const config = useRuntimeConfig()

  // State
  const user = useState('auth-user', () => null)
  const token = useState('auth-token', () => null)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isTeacher = computed(() => user.value?.role === 'teacher')

  // Methods
  const loginWithGoogle = async () => {
    try {
      isLoading.value = true
      error.value = null

      // Redirect to Google OAuth
      const redirectUrl = `${config.public.apiUrl}/login`
      window.location.href = redirectUrl

    } catch (err: any) {
      error.value = err.message || 'Gagal login dengan Google'
      console.error('Google login error:', err)
    } finally {
      isLoading.value = false
    }
  }

  const handleAuthCallback = async () => {
    try {
      isLoading.value = true
      error.value = null

      // This will be called after Google OAuth callback
      // The Laravel backend should set a session and we can get user info
      const response = await $api('/user', {
        headers: {
          'Accept': 'application/json',
        },
      })

      if (response.success) {
        user.value = response.data
        // Set token if provided (for API calls)
        if (response.data.token) {
          token.value = response.data.token
        }

        // Store auth data in localStorage for persistence
        if (process.client) {
          localStorage.setItem('auth_user', JSON.stringify(response.data))
          if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token)
          }
        }

        return true
      } else {
        throw new Error(response.message || 'Authentication failed')
      }

    } catch (err: any) {
      error.value = err.message || 'Gagal mendapatkan data pengguna'
      console.error('Auth callback error:', err)
      return false
    } finally {
      isLoading.value = false
    }
  }

  const logout = async () => {
    try {
      isLoading.value = true

      // Call backend logout endpoint
      if (token.value) {
        await $fetch('/logout', {
          method: 'POST',
          baseURL: config.public.apiUrl,
          headers: {
            'Accept': 'application/json',
          },
        })
      }

    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      // Clear local state regardless of API call success
      user.value = null
      token.value = null
      error.value = null

      // Clear localStorage
      if (process.client) {
        localStorage.removeItem('auth_user')
        localStorage.removeItem('auth_token')
      }

      // Clear axios default header
      if (process.client) {
        delete $api.defaults.headers.common['Authorization']
      }

      isLoading.value = false

      // Redirect to home page
      await navigateTo('/')
    }
  }

  const refreshToken = async () => {
    try {
      const response = await $api('/refresh-token', {
        method: 'POST',
      })

      if (response.success && response.data.token) {
        token.value = response.data.token

        if (process.client) {
          localStorage.setItem('auth_token', response.data.token)
        }

        return true
      }

      return false

    } catch (err) {
      console.error('Token refresh error:', err)
      return false
    }
  }

  const initializeAuth = async () => {
    // Only run on client side
    if (!process.client) return false

    try {
      // Check if we have stored auth data
      const storedUser = localStorage.getItem('auth_user')
      const storedToken = localStorage.getItem('auth_token')

      if (storedUser && storedToken) {
        user.value = JSON.parse(storedUser)
        token.value = storedToken

        // Verify token is still valid by calling user endpoint
        const response = await $api('/user', {
          headers: {
            'Authorization': `Bearer ${token.value}`,
          },
        })

        if (response.success) {
          user.value = response.data
          return true
        } else {
          // Token is invalid, clear auth data
          await logout()
          return false
        }
      }

      return false

    } catch (err) {
      console.error('Auth initialization error:', err)
      // Clear potentially corrupted auth data
      await logout()
      return false
    }
  }

  const hasPermission = (permission: string) => {
    if (!user.value) return false

    // Admin has all permissions
    if (user.value.role === 'admin') return true

    // Define permission matrix
    const permissions = {
      teacher: [
        'materials.create',
        'materials.read',
        'materials.update',
        'materials.delete',
        'analytics.view',
        'jobs.view',
      ],
      admin: [
        'materials.create',
        'materials.read',
        'materials.update',
        'materials.delete',
        'analytics.view',
        'jobs.view',
        'users.create',
        'users.read',
        'users.update',
        'users.delete',
        'system.configure',
        'templates.manage',
        'audit.view',
      ],
    }

    return permissions[user.value.role]?.includes(permission) || false
  }

  // Initialize auth on app startup
  onMounted(async () => {
    await initializeAuth()
  })

  // Watch for token changes and update API headers
  watch(token, (newToken) => {
    if (process.client && newToken) {
      // Set default authorization header for API calls
      $api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
    } else if (process.client) {
      delete $api.defaults.headers.common['Authorization']
    }
  })

  return {
    // State
    user: readonly(user),
    token: readonly(token),
    isLoading: readonly(isLoading),
    error: readonly(error),

    // Computed
    isAuthenticated,
    isAdmin,
    isTeacher,

    // Methods
    loginWithGoogle,
    handleAuthCallback,
    logout,
    refreshToken,
    initializeAuth,
    hasPermission,
  }
}