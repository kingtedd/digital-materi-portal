# Frontend Guidelines for digital-materi-portal

This document outlines the frontend setup, design principles, and technologies used in the `digital-materi-portal` project. It’s written in plain language so anyone—technical or not—can understand how the portal is built, styled, and organized.

## 1. Frontend Architecture

### Overall Structure
- **Framework**: Next.js 15 using the App Router. This gives us both server-side rendering (fast first load) and a single-page app feeling for smooth interactions.
- **Language**: React 19 with TypeScript, ensuring consistent types across components and API calls.
- **UI Library**: Tailwind CSS for utility-first styling, plus shadcn/ui for pre-built React components (forms, buttons, tables).
- **BFF Layer**: Next.js API routes act as a Backend-for-Frontend. They proxy requests to your Laravel API, keeping credentials safe and centralizing error handling.
- **Containerization**: Docker files are included so you can run the frontend in an isolated, reproducible environment.

### Scalability, Maintainability & Performance
1. **Server Components vs Client Components**: Static parts render on the server; interactive parts run on the client. This splits work where it’s most efficient.
2. **Type Safety**: Types generated (via openapi-typescript) from your Laravel API keep frontend and backend in sync.
3. **Modular APIs**: Central API client (`/lib/api-client.ts`) handles all calls—changing endpoints or headers in one place updates the whole app.
4. **Dockerization**: Ensures the same environment across teams and deployment stages.

## 2. Design Principles

1. **Usability**: Clear navigation with a sidebar, consistent button labels, and form feedback (errors, success messages).
2. **Accessibility**: Semantic HTML elements, proper ARIA attributes on custom components, and keyboard-navigable forms and menus.
3. **Responsiveness**: Mobile-first design; layouts adapt from phone to tablet to desktop. Tailwind utilities make breakpoints simple.
4. **Consistency**: Using a shared color palette, typography scale, and spacing system guarantees a uniform look.
5. **Feedback and Clarity**: Loading spinners on data fetch, status badges for jobs (`PROCESSING`, `COMPLETED`, `FAILED`), and clear error messages keep users informed.

## 3. Styling and Theming

### Approach & Methodology
- **Utility-First**: Tailwind CSS handles most styling via classes, reducing custom CSS.
- **Component Styles**: shadcn/ui components come with default styles that follow our theme.
- **Dark/Light Mode**: Toggle support using Tailwind’s `dark:` variants and a simple React context or Next.js middleware to persist user preference.

### Visual Style
- **Overall Style**: Modern flat design—clean surfaces, minimal shadows, and clear typographic hierarchy.
- **Color Palette**:
  • Primary: indigo-600 (#4F46E5)
  • Secondary: pink-500 (#EC4899)
  • Accent: emerald-500 (#10B981)
  • Background (light): gray-50 (#F9FAFB)
  • Surface (cards, modals): white (#FFFFFF)
  • Text (dark mode): gray-100 (#F3F4F6)
  • Text (light mode): gray-900 (#111827)
  • Border: gray-200 (#E5E7EB)
  • Info: blue-500 (#3B82F6)
  • Success: green-500 (#10B981)
  • Warning: yellow-500 (#F59E0B)
  • Error: red-500 (#EF4444)

### Typography
- **Font Family**: Inter, with system-fallback (`font-sans` in Tailwind).
- **Scale**: 14px base, headings at 24px/20px/16px, paragraphs at 14px, small text at 12px.

## 4. Component Structure

- **Folder Organization**: `/components/ui` for shared UI bits (Button, Input, Table, Chart). Feature pages live under `/app/dashboard/materials`, `/app/dashboard/jobs`, `/app/dashboard/analytics`.
- **Reusability**: Each component has a single responsibility (e.g., `MaterialForm`, `JobStatusBadge`, `AnalyticsChart`). Props control behavior and appearance.
- **Naming**: PascalCase for component files, kebab-case for folder names.
- **Isolation**: Styles scoped via Tailwind classes—no global CSS collisions.

## 5. State Management

- **Library**: React Query (or SWR) for data fetching and caching. It handles background polling, stale-while-revalidate, and loading/error states out of the box.
- **Local State**: React’s `useState` and `useReducer` for small interactive bits (e.g., form inputs, modals).
- **Global State**: Context API for theme (dark/light), authentication status, and user role (teacher vs admin).

## 6. Routing and Navigation

- **Routing**: Next.js App Router (`/app` directory). File-based routes map to URLs.
- **Protected Routes**: Middleware checks for a valid session token before allowing `/dashboard/*` pages.
- **Side Navigation**: A fixed sidebar component uses Next.js’s `Link` component for client-side transitions.
- **Dynamic Routes**: For example, `/dashboard/materials/[id]` for editing materials.

## 7. Performance Optimization

1. **Code Splitting**: Automatic with Next.js—only the code needed for each page is sent to the browser.
2. **Lazy Loading**: Components like charts and large tables load dynamically with `next/dynamic`.
3. **Image Optimization**: Next.js `<Image>` component handles resizing and lazy loading.
4. **Asset Minification**: Built-in at compile time for JS/CSS.
5. **Caching**: React Query caches API data; static assets served with long cache times.

## 8. Testing and Quality Assurance

- **Unit Tests**: Jest + React Testing Library for components and hooks (e.g., testing form validation, button clicks).
- **Integration Tests**: Next.js built-in support or Cypress to test interactions across multiple components (e.g., filling a form and seeing a new table row).
- **End-to-End Tests**: Cypress for full flows—sign in with Google OAuth (stubbed), create material, check job status.
- **Linting & Formatting**: ESLint with TypeScript rules, Prettier for code style, and Tailwind CSS IntelliSense for class validation.
- **Continuous Integration**: GitHub Actions runs tests, linting, and type checks on each pull request.

## 9. Conclusion and Overall Frontend Summary

The `digital-materi-portal` frontend uses modern tools (Next.js, React, TypeScript, Tailwind) and a clear folder/API structure to build two portals: one for teachers and one for admins. We separate UI from business logic by using Next.js API routes as a BFF to your Laravel/n8n backend. The design focuses on usability, accessibility, and performance. With established patterns for component design, state management, routing, and testing, this setup ensures a maintainable, scalable, and pleasant experience for both developers and end users.