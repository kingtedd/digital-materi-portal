# Project Requirements Document (PRD)

## 1. Project Overview

**Digitization of Educational Materials**
The `digital-materi-portal` is a web-based Teacher and Admin Portal for your “Portal Digitalisasi Materi” project. Its core purpose is to give teachers a streamlined interface to create, manage, and track the generation of digital materials. Behind the scenes, it connects to a Laravel API, triggers n8n workflows, stores catalog entries in Google Sheets, and visualizes analysis results from the Gemini API.

We’re building this portal to replace manual spreadsheet edits and scattered email updates with a unified, user-friendly dashboard. Teachers will be able to submit material requests, watch real-time job progress, and dive into interactive analytics. Administrators will get an enhanced interface for user management, workflow oversight, and system-wide reporting. Success means: fast, secure logins; zero confusion around job status; and clear, actionable analytics—measured by user adoption and reduced support tickets.

## 2. In-Scope vs. Out-of-Scope

**In-Scope (Version 1)**
- Google OAuth sign-in integrated with Laravel backend for both Teacher and Admin roles.
- Protected `/dashboard` route featuring:
  - Material Catalog page (list, filter, sort entries from `gsheet-catalog-materi`).
  - Create/Edit Material form that posts to Next.js BFF and on to Laravel/n8n.
  - Job Monitor page polling the Laravel `jobs/{id}` endpoint for status updates (`PROCESSING`, `COMPLETED`, `FAILED`).
  - Analytics Dashboard converting Gemini JSON into charts.
- Admin Portal pages for basic user management (view list, change roles).
- Next.js API routes (BFF) centralizing calls to Laravel API, handling authentication headers, errors.
- React Query (or SWR) for data fetching, caching, and polling.
- Charting library (Recharts or Chart.js) for interactive graphs.
- Docker configuration for consistent local/dev/prod environments.

**Out-of-Scope (Phase 2+)**
- Direct database or Google Sheets access from frontend.
- WebSocket or push-based real-time updates (we’ll rely on polling initially).
- Bulk import/export of materials via CSV.
- Offline mode or mobile-only app.
- Advanced analytics (ML insights beyond Gemini JSON visualization).

## 3. User Flow

**Teacher Journey**
A teacher lands on the `/sign-in` page and clicks “Sign in with Google.” After granting permissions, they return to the `/dashboard` home. The left sidebar displays “Materials,” “Analytics,” and “Job Monitor.” Clicking “Materials” shows a searchable table of existing entries. They click “New Material,” fill out a form (subject, title, description), and submit. The portal calls `/api/materials`, which forwards to Laravel/n8n. On success, the teacher is redirected to “Job Monitor,” where they see real-time progress of their material generation job.

**Admin Journey**
An admin signs in the same way and lands on a slightly different `/dashboard`. The sidebar now includes “User Management” and “System Analytics.” Under “User Management,” they see a list of all accounts with roles. They can click “Edit” to change a teacher to an admin (or vice versa). Selecting “System Analytics” brings up a high-level chart of total jobs by status and user activity, rendered from Gemini-powered JSON. All actions respect role-based access, hiding teacher-only pages.

## 4. Core Features

- **Authentication & Authorization**: Google OAuth via NextAuth; session tokens exchanged with Laravel; roles (`teacher`, `admin`) fetched on login.
- **Backend-for-Frontend (BFF) API Routes**: Next.js routes under `/app/api` that proxy requests to Laravel, centralize headers, error handling, and type-safe responses.
- **Material Catalog**: Table view with sorting, filtering, pagination; data fetched via React Query.
- **Create/Edit Material Form**: Client-side validation (required fields, length limits); POST to `/api/materials`; graceful error display.
- **Job Monitor**: Polling mechanism (interval ~5s) querying `/api/jobs/{jobId}`; status badges; timestamped logs.
- **Analytics Dashboard**: Fetch Gemini JSON from `/api/analytics`; render line/bar charts; tooltips and legends for clarity.
- **Admin User Management**: List users; change roles; remove accounts; confirmation dialogs.
- **Role-Based Access Control**: Conditional rendering of pages/components based on role in session context.

## 5. Tech Stack & Tools

- **Frontend**
  - Next.js 15 (App Router)
  - React 19 with TypeScript
  - Tailwind CSS & shadcn/ui for UI components
  - NextAuth.js for Google OAuth
  - Axios or built-in fetch for HTTP
  - React Query (or SWR) for data fetching and polling
  - Recharts or Chart.js for charts
  - openapi-typescript to generate TypeScript types from Laravel OpenAPI spec
  - Docker for containerization

- **Backend (External Services)**
  - Laravel API (PHP) with OpenAPI/Swagger docs
  - n8n workflows for material generation and scheduling
  - Google Sheets as primary catalog store
  - Gemini API for content analysis

- **Developer Tools**
  - VS Code with Cursor or Windsurf plugin (optional)
  - ESLint / Prettier for code formatting

## 6. Non-Functional Requirements

- **Performance**: Initial page load <1 second; SPA route changes <200 ms. Polling interval configurable (default 5 s).  
- **Security**: All traffic over HTTPS. OAuth 2.0 flows with PKCE. BFF protects API keys/credentials. HTTP-only, secure cookies. Role checks on both client and server.  
- **Compliance**: GDPR data handling (users can delete their data).  
- **Usability**: WCAG AA accessibility; responsive design for tablets and desktops; form error messaging.  
- **Reliability**: 99.9% uptime; retry logic on transient HTTP errors; circuit breaker pattern for n8n/Google API failures.

## 7. Constraints & Assumptions

- **Laravel API** is fully available with documented endpoints and OpenAPI spec.  
- **n8n** workflows triggers and webhooks exist (`W-Generate-Digital`).  
- **Google OAuth** client ID/secret and redirect URIs configured in Laravel and NextAuth.  
- **No direct DB access**; all data through API.  
- **API rate limits** on Google Sheets and Gemini manageable within polling strategy.  
- **Environment Variables** set for API_BASE_URL, OAUTH_CLIENT_ID, OAUTH_SECRET, etc.

## 8. Known Issues & Potential Pitfalls

- **API Rate Limits**: Frequent polling may hit rate limits on Laravel or Google APIs. Mitigation: exponential backoff, adjustable intervals.  
- **Workflow Delays**: n8n jobs may queue, causing long waits. Mitigation: display ETA and allow user to cancel/retry.  
- **Role Sync Errors**: If role changes in Laravel aren’t reflected in session, UI may show incorrect pages. Mitigation: refresh session on critical actions.  
- **Schema Drift**: If Laravel API changes without updating TypeScript types, front end breaks. Mitigation: schedule type regeneration in CI pipeline.  
- **Chart Performance**: Large datasets in analytics may slow rendering. Mitigation: paginate/aggregate data server-side before sending to client.

---

This PRD lays out a clear, unambiguous blueprint for the AI to generate subsequent technical documents: tech stack details, frontend guidelines, backend structure, app flow, file structure, and IDE rules. Everything needed for implementation is defined here without guesswork.