export const useApi = () => {
  const config = useRuntimeConfig()
  const { token, logout } = useAuth()

  // Create axios instance with base configuration
  const api = $fetch.create({
    baseURL: config.public.apiBase,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    retry: 1,
    retryDelay: 1000,
    onRequestError({ error }) {
      console.error('API Request Error:', error)
    },
    onResponseError({ response, error }) {
      console.error('API Response Error:', response?.status, error)

      // Handle 401 Unauthorized - token expired or invalid
      if (response?.status === 401) {
        logout()
        return
      }

      // Handle 403 Forbidden - insufficient permissions
      if (response?.status === 403) {
        showError({
          statusCode: 403,
          statusMessage: 'Anda tidak memiliki izin untuk mengakses halaman ini',
        })
        return
      }

      // Handle 404 Not Found
      if (response?.status === 404) {
        showError({
          statusCode: 404,
          statusMessage: 'Halaman atau data tidak ditemukan',
        })
        return
      }

      // Handle 422 Validation Error
      if (response?.status === 422) {
        return response._data
      }

      // Handle 500 Server Error
      if (response?.status >= 500) {
        showError({
          statusCode: response?.status || 500,
          statusMessage: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
        })
        return
      }
    },
  })

  // Add authorization header when token is available
  if (token.value) {
    api.defaults = {
      ...api.defaults,
      headers: {
        ...api.defaults?.headers,
        Authorization: `Bearer ${token.value}`,
      },
    }
  }

  // Helper methods for common API patterns
  const get = async <T = any>(url: string, options: any = {}): Promise<T> => {
    return await api(url, {
      method: 'GET',
      ...options,
    })
  }

  const post = async <T = any>(url: string, data?: any, options: any = {}): Promise<T> => {
    return await api(url, {
      method: 'POST',
      body: data,
      ...options,
    })
  }

  const put = async <T = any>(url: string, data?: any, options: any = {}): Promise<T> => {
    return await api(url, {
      method: 'PUT',
      body: data,
      ...options,
    })
  }

  const patch = async <T = any>(url: string, data?: any, options: any = {}): Promise<T> => {
    return await api(url, {
      method: 'PATCH',
      body: data,
      ...options,
    })
  }

  const del = async <T = any>(url: string, options: any = {}): Promise<T> => {
    return await api(url, {
      method: 'DELETE',
      ...options,
    })
  }

  // File upload helper
  const upload = async <T = any>(url: string, file: File, options: any = {}): Promise<T> => {
    const formData = new FormData()
    formData.append('file', file)

    // Add additional data if provided
    if (options.data) {
      Object.keys(options.data).forEach(key => {
        formData.append(key, options.data[key])
      })
    }

    return await api(url, {
      method: 'POST',
      body: formData,
      headers: {
        // Don't set Content-Type for FormData - browser will set it with boundary
        'Accept': 'application/json',
      },
      ...options,
    })
  }

  // Multiple files upload helper
  const uploadMultiple = async <T = any>(url: string, files: File[], options: any = {}): Promise<T> => {
    const formData = new FormData()

    files.forEach((file, index) => {
      formData.append(`files[${index}]`, file)
    })

    // Add additional data if provided
    if (options.data) {
      Object.keys(options.data).forEach(key => {
        formData.append(key, options.data[key])
      })
    }

    return await api(url, {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json',
      },
      ...options,
    })
  }

  // API endpoint specific helpers
  const materials = {
    index: (params?: any) => get<MaterialResponse>('/materials', { query: params }),
    show: (materialId: string) => get<MaterialDetailResponse>(`/materials/${materialId}`),
    create: (data: CreateMaterialRequest) => post<CreateMaterialResponse>('/materials', data),
    generateDigital: (materialId: string) => post<JobResponse>(`/materials/${materialId}/generate-digital`),
  }

  const jobs = {
    index: (params?: any) => get<JobsResponse>('/jobs', { query: params }),
    show: (jobId: number) => get<JobResponse>(`/jobs/${jobId}`),
    retry: (jobId: number) => post<JobResponse>(`/jobs/${jobId}/retry`),
  }

  const analytics = {
    dashboard: () => get<DashboardAnalyticsResponse>('/analytics/dashboard'),
    quizAnalysis: (materialId: string) => get<QuizAnalysisResponse>(`/analytics/materials/${materialId}/quiz-analysis`),
    materialPerformance: (materialId: string) => get<MaterialPerformanceResponse>(`/analytics/materials/${materialId}/performance`),
  }

  const admin = {
    users: {
      index: (params?: any) => get<UsersResponse>('/admin/users', { query: params }),
      show: (userId: number) => get<UserResponse>(`/admin/users/${userId}`),
      create: (data: CreateUserRequest) => post<UserResponse>('/admin/users', data),
      update: (userId: number, data: UpdateUserRequest) => put<UserResponse>(`/admin/users/${userId}`, data),
      delete: (userId: number) => del(`/admin/users/${userId}`),
    },
    emailTemplates: {
      index: (params?: any) => get<EmailTemplatesResponse>('/admin/email-templates', { query: params }),
      show: (templateId: number) => get<EmailTemplateResponse>(`/admin/email-templates/${templateId}`),
      create: (data: CreateEmailTemplateRequest) => post<EmailTemplateResponse>('/admin/email-templates', data),
      update: (templateId: number, data: UpdateEmailTemplateRequest) => put<EmailTemplateResponse>(`/admin/email-templates/${templateId}`, data),
      delete: (templateId: number) => del(`/admin/email-templates/${templateId}`),
    },
    assignmentTemplates: {
      index: (params?: any) => get<AssignmentTemplatesResponse>('/admin/assignment-templates', { query: params }),
      show: (templateId: number) => get<AssignmentTemplateResponse>(`/admin/assignment-templates/${templateId}`),
      create: (data: CreateAssignmentTemplateRequest) => post<AssignmentTemplateResponse>('/admin/assignment-templates', data),
      update: (templateId: number, data: UpdateAssignmentTemplateRequest) => put<AssignmentTemplateResponse>(`/admin/assignment-templates/${templateId}`, data),
      delete: (templateId: number) => del(`/admin/assignment-templates/${templateId}`),
    },
    system: {
      status: () => get<SystemStatusResponse>('/admin/system/status'),
      triggerSync: () => post<SyncResponse>('/admin/system/trigger-sync'),
    },
    auditLogs: (params?: any) => get<AuditLogsResponse>('/admin/audit-logs', { query: params }),
  }

  return {
    // Base methods
    api,
    get,
    post,
    put,
    patch,
    del,
    upload,
    uploadMultiple,

    // API helpers
    materials,
    jobs,
    analytics,
    admin,
  }
}