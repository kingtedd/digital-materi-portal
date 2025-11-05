export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()

  // Public routes that don't require authentication
  const publicRoutes = ['/', '/login', '/auth/google/callback', '/health']

  // Check if current route is public
  const isPublicRoute = publicRoutes.includes(to.path) ||
                        to.path.startsWith('/_') ||
                        to.path.startsWith('/api')

  // If route is public, allow access
  if (isPublicRoute) {
    return
  }

  // If user is not authenticated, redirect to login
  if (!auth.isAuthenticated.value) {
    return navigateTo('/login')
  }

  // Role-based access control
  const { path } = to

  // Admin routes
  if (path.startsWith('/admin')) {
    if (!auth.isAdmin.value) {
      throw createError({
        statusCode: 403,
        statusMessage: 'Anda tidak memiliki izin untuk mengakses halaman admin',
      })
    }
  }

  // Teacher routes
  if (path.startsWith('/teacher')) {
    if (!auth.isTeacher.value && !auth.isAdmin.value) {
      throw createError({
        statusCode: 403,
        statusMessage: 'Anda tidak memiliki izin untuk mengakses halaman guru',
      })
    }
  }

  // Check specific permissions for sensitive routes
  if (path.includes('/create') && !auth.hasPermission('materials.create')) {
    throw createError({
      statusCode: 403,
      statusMessage: 'Anda tidak memiliki izin untuk membuat materi',
    })
  }

  if (path.includes('/edit') && !auth.hasPermission('materials.update')) {
    throw createError({
      statusCode: 403,
      statusMessage: 'Anda tidak memiliki izin untuk mengedit materi',
    })
  }

  if (path.includes('/delete') && !auth.hasPermission('materials.delete')) {
    throw createError({
      statusCode: 403,
      statusMessage: 'Anda tidak memiliki izin untuk menghapus materi',
    })
  }
})