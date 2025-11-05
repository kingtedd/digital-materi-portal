# Security Guidelines for 

**Project**: digital-materi-portal  
**Stack**: Next.js (App Router), React, TypeScript, Tailwind CSS, shadcn/ui, Next-Auth (Google OAuth), BFF API routes, Docker  
**Backend**: Laravel API, n8n workflows, Google Sheets (managed exclusively by backend)

---

## 1. Authentication & Access Control

- **Google OAuth Integration**  
  • Use a proven library (e.g., next-auth) to handle the OAuth 2.0 flow.  
  • Validate Google ID tokens on the server (BFF) using Google’s public keys.  
  • Exchange the Google token for a session JWT issued by your Laravel backend.

- **Secure Session Management**  
  • Store session tokens in cookies with `HttpOnly`, `Secure`, and `SameSite=Strict` attributes.  
  • Enforce short-lived access tokens and refresh tokens with rotation.  
  • Implement idle and absolute session timeouts, and provide a logout endpoint that revokes tokens.

- **Role-Based Access Control (RBAC)**  
  • Fetch user roles (`teacher`, `admin`) from the Laravel API on login.  
  • Enforce authorization checks in every Next.js API route and on critical pages.  
  • Hide or disable UI elements in the frontend based on role claims—never rely solely on client code.

---

## 2. Input Handling & Processing

- **Front-end & Server-side Validation**  
  • Define strict schemas (e.g., with Zod or Joi) for all form inputs and API route payloads.  
  • Validate again in BFF routes before proxying requests to Laravel.  
  • Reject or sanitize any unexpected fields.

- **Prevent Injection & XSS**  
  • Do not interpolate user data directly into HTML—use React’s escaping by default.  
  • For any rich-text or HTML inputs, employ a sanitization library such as DOMPurify.  
  • Use parameterized queries in Laravel (Eloquent or Query Builder) to prevent SQL injection.

- **Secure File Uploads (if added)**  
  • Restrict by MIME type and file size.  
  • Store uploads outside the public directory or on a secure object store (e.g., AWS S3 with pre-signed URLs).  
  • Scan for malware and strip any embedded scripts.

---

## 3. BFF & API Security

- **HTTPS / TLS Enforcement**  
  • All BFF routes and backend API endpoints must use TLS 1.2+.  
  • HSTS header: `Strict-Transport-Security: max-age=63072000; includeSubDomains; preload`.

- **CORS & Rate Limiting**  
  • Configure CORS to allow only trusted origins (e.g., your portal domain).  
  • Implement per-IP and per-user rate limiting on API routes (e.g., 100 req/min) to mitigate brute-force and DoS attempts.

- **Error Handling & Secrets**  
  • Do not leak stack traces or internal errors to clients; return generic error messages.  
  • Load secrets (OAuth credentials, API keys) from a secrets manager or environment variables—never commit them.

---

## 4. Data Protection & Privacy

- **Encryption**  
  • Enforce HTTPS for all client↔server communication.  
  • Encrypt any sensitive data at rest in backend services (Laravel database).  
  • Use strong hashing (bcrypt or Argon2) for any additional passwords or tokens stored.

- **PII Handling**  
  • Minimize PII collected in the frontend; only transmit what is necessary.  
  • Mask or redact PII in logs.  
  • Comply with regulations (e.g., GDPR) for data retention and deletion.

---

## 5. Web Application Security Hygiene

- **Security Headers**  
  • Content-Security-Policy: restrict scripts, styles, and frame ancestors to trusted sources.  
  • X-Frame-Options: `DENY` to prevent clickjacking.  
  • X-Content-Type-Options: `nosniff` to block MIME sniffing.

- **CSRF Protection**  
  • Next.js API routes: require and validate anti-CSRF tokens for state-changing requests.  
  • Use double-submit cookies or Synchronizer Token Pattern.

- **Cookie Security**  
  • All session or refresh tokens set with `HttpOnly`, `Secure`, `SameSite=Strict`.  
  • Avoid storing tokens in localStorage or sessionStorage.

---

## 6. Infrastructure & Configuration Management

- **Docker Hardening**  
  • Use minimal, up-to-date base images (e.g., `node:slim`).  
  • Run the application as a non-root user inside the container.  
  • Scan container images regularly with a CVE scanner.

- **Server & Network**  
  • Expose only needed ports (e.g., 443 for HTTPS).  
  • Disable or remove default credentials on any management interfaces.  
  • Keep host OS, Docker daemon, and dependencies patched.

- **Configuration**  
  • Separate configuration from code: use environment variables or a config service.  
  • Version and audit all environment and secret changes.

---

## 7. Dependency Management & CI/CD Security

- **Secure Dependencies**  
  • Maintain a lockfile (`package-lock.json`) for deterministic installs.  
  • Periodically run automated vulnerability scans (e.g., npm audit, Snyk, GitHub Dependabot).  
  • Remove unused packages to reduce attack surface.

- **CI/CD Pipeline**  
  • Store secrets (API keys, SSH keys) securely in your CI system’s secret vault.  
  • Enforce code reviews and automated linting, formatting, and security tests.  
  • Run SAST/DAST tools as part of the build; block deployments on critical/high findings.

---

By adhering to these guidelines, the digital-materi-portal will be built with security as a first-class cornerstone, ensuring a resilient, trustworthy, and compliant user-facing application.