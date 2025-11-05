# Tech Stack Document: digital-materi-portal

This document explains, in everyday language, the choices of technologies for the Teacher and Admin Portals of the Portal Digitalisasi Materi project. It shows how each piece fits together to give a smooth, reliable experience for both users and developers.

## 1. Frontend Technologies
These are the tools we use to build everything the user sees and interacts with.

- **Next.js 15 (App Router)**
  - Provides fast page loads by rendering pages on the server first (Server-Side Rendering), then turns into a single-page app in the browser.
  - Organizes routes and data fetching in a clear folder structure.
- **React 19 & TypeScript**
  - React lets us build the interface in reusable pieces (components).  
  - TypeScript adds simple checks to make sure we’re passing the right data around, reducing bugs.
- **Tailwind CSS & shadcn/ui**
  - Tailwind gives us building-block CSS classes (like “padding-4” or “text-center”) for rapid styling.  
  - shadcn/ui provides a ready set of styled components (forms, buttons, tables) that match Tailwind’s style guide.
- **Data Fetching & State Management**
  - **Axios or Fetch API** for making HTTP calls to our backend.  
  - **React Query** (or **SWR**) to handle loading states, caching, and background updates, especially useful for polling job statuses.
- **Charting Library**
  - **Recharts** or **Chart.js** to turn raw analytics data (from the Gemini API) into interactive graphs and charts.
- **Code Quality & Developer Tools**
  - **openapi-typescript** to generate TypeScript types from the Laravel API’s OpenAPI schema, ensuring type safety end-to-end.
  - **ESLint** and **Prettier** to keep code style consistent and catch errors early.

## 2. Backend Technologies
These services handle data, business logic, and workflows behind the scenes.

- **Laravel API**
  - A PHP framework that exposes RESTful endpoints for creating materials, checking job status, and fetching analytics.
  - Manages user sessions, roles (teacher vs. admin), and secures data access.
- **n8n Workflows**
  - A workflow automation tool triggered by Laravel to generate and schedule materials (via the `W-Generate-Digital` workflow).
- **Google Sheets**
  - Serves as the primary data store (the `gsheet-catalog-materi`), controlled exclusively by Laravel and n8n.  
  - Allows non-technical staff to view and edit catalogs directly in a familiar spreadsheet interface.
- **Google Gemini API**
  - Provides AI analysis on materials. Laravel calls this API, stores the `result_json`, and the frontend visualizes it.
- **Optional Real-Time Updates**
  - A WebSocket solution (e.g., Laravel Echo with Soketi) for pushing job-status updates instantly to the portal, reducing or replacing polling.

## 3. Infrastructure and Deployment
This section covers how we host, version, and continuously deliver the application.

- **Version Control: Git & GitHub**
  - All code lives in a Git repository on GitHub, allowing collaboration, pull requests, and code reviews.
- **Continuous Integration & Deployment (CI/CD)**
  - **GitHub Actions** runs automated checks (tests, lint) on every pull request.  
  - On merge, it builds and deploys the frontend and backend images.
- **Containerization: Docker**
  - The frontend is packaged in a Docker container, ensuring the same environment in development, staging, and production.
- **Hosting Platforms**
  - **Frontend**: Deploy to Vercel, Netlify, or a Docker-friendly platform (AWS ECS, DigitalOcean App Platform).  
  - **Backend**: Host Laravel and n8n on a VPS or container service (e.g., AWS, Heroku, DigitalOcean).
- **Environment Variables**
  - Credentials (OAuth keys, API URLs) are stored securely and injected at build/runtime, keeping secrets out of code.

## 4. Third-Party Integrations
These outside services bring extra power without having to build everything from scratch.

- **Google OAuth 2.0**  
  - Allows users to sign in with their Google accounts.  
  - We exchange the Google token with Laravel to establish a secure session.
- **Google Sheets API**  
  - Accessed by Laravel to read and write the material catalog.
- **Google Gemini API**  
  - Provides AI-powered analysis of materials, turning text into structured insights.
- **n8n**  
  - Triggers and manages automated workflows for content generation and scheduling.
- **Charting Library (Recharts/Chart.js)**  
  - Renders analytics data from Gemini in interactive charts within the portal.

## 5. Security and Performance Considerations
How we keep data safe and the app fast.

- **Authentication & Authorization**
  - Google OAuth ensures a trusted sign-in flow.  
  - Laravel enforces role-based access (teachers vs. admins) on every endpoint.
- **Backend-for-Frontend (BFF) Pattern**
  - Next.js API routes act as a secure middle layer, hiding backend URLs and credentials from the browser.
- **Data Validation & Sanitization**
  - Both frontend (TypeScript types, form validation) and Laravel (request validation rules) protect against bad or malicious input.
- **HTTPS Everywhere**
  - All traffic is encrypted with TLS to keep user data and tokens safe in transit.
- **Caching & Polling Optimization**
  - React Query’s caching reduces unnecessary requests.  
  - Polling intervals are tuned to balance timeliness and server load.
- **Code Splitting & Lazy Loading**  
  - Next.js automatically splits code by route, so users only download what they need.

## 6. Conclusion and Overall Tech Stack Summary
Every technology in this stack was chosen to meet key goals: a fast, user-friendly portal for teachers and admins; a clear separation between frontend and backend; and simple, maintainable integrations with automated workflows and AI analysis.

- **Frontend**: Next.js + React + TypeScript + Tailwind + shadcn/ui for a modern, responsive UI.
- **Backend**: Laravel handles business logic, n8n runs content workflows, and Google Sheets stores catalog data.
- **Infrastructure**: Docker for consistency, GitHub/GitHub Actions for collaboration and CI/CD, and standard hosting choices for scalability.
- **Integrations**: Google OAuth for sign-in, Google Gemini for AI insights, and charting libraries to visualize data.

This combination ensures you can build, deploy, and run the Portal Digitalisasi Materi with confidence, giving end users a polished experience and developers a clear, maintainable codebase.