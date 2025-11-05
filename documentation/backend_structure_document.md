# Backend Structure Document for `digital-materi-portal`

## 1. Backend Architecture

Overall, the backend is built around two main services:

• **Laravel API (PHP)**
  • Follows the Model-View-Controller (MVC) pattern to separate concerns.
  • Uses Eloquent ORM for database interactions and a Repository pattern for clean code organization.
  • Exposes RESTful endpoints for all core operations (materials, jobs, user management).

• **n8n Workflow Engine**
  • Automates content generation and scheduling via visual workflows.
  • Triggered by Laravel through webhooks or scheduled jobs.

Additional design patterns and practices:

• **Backend-for-Frontend (BFF)**
  • Next.js API routes act as a secure proxy between the browser and the Laravel API.
  • Centralizes authentication headers, error handling, and simplifies client code.

• **OAuth2 (Google)** for user authentication.

How it supports key goals:

• **Scalability:** Containerized services (Docker on AWS ECS Fargate) let us add more instances on demand.
• **Maintainability:** Clear separation of layers (API, workflows, data store) makes updates and debugging straightforward.
• **Performance:** Caching (Redis) and CDN (CloudFront) reduce load times and backend pressure.

---

## 2. Database Management

We use both a traditional SQL database and a cloud‐based spreadsheet:

• **PostgreSQL (SQL)**
  • Hosted on AWS RDS for managed backups and failover.
  • Stores user accounts, roles, and job records.
  • Accessed via Laravel Eloquent models.

• **Google Sheets (NoSQL-style)**
  • The `gsheet-catalog-materi` sheet acts as our primary material catalog.
  • Laravel connects to it through the Google Sheets API client.

Best practices in place:

• Regular backups of both RDS and Google Sheets exports.
• Data validation and sanitization in Laravel before reads/writes.
• Version control for database migrations and workflow definitions.

---

## 3. Database Schema

### PostgreSQL (SQL) Tables

Users table:
- **id**: auto-increment primary key
- **google_id**: string, unique (links to Google account)
- **email**: string
- **name**: string
- **role**: enum (`teacher`, `admin`)
- **created_at**, **updated_at**: timestamps

Jobs table:
- **id**: UUID primary key
- **user_id**: foreign key to Users table
- **status**: enum (`PROCESSING`, `COMPLETED`, `FAILED`)
- **result_json**: JSONB to store Gemini analysis output
- **created_at**, **updated_at**: timestamps

### Google Sheets (`gsheet-catalog-materi`)

Columns (each column is a field):
- SUBJECT_NAME
- MATERIAL_TITLE
- MATERIAL_DESC
- MATERIAL_TYPE
- MATERIAL_LEVEL
- MATERIAL_THEME
- MATERIAL_SUBTHEME
- …and any additional metadata fields

---

## 4. API Design and Endpoints

All endpoints live under `/api` and follow REST principles. Requests and responses use JSON.

**Authentication**
- `POST /api/auth/google`  – Exchange Google OAuth token and create a session.
- `POST /api/auth/refresh` – Renew session token.

**Materials**
- `GET /api/materials`          – List all material catalog entries.
- `POST /api/materials`         – Add a new entry to Google Sheets and create a job.
- `GET /api/materials/{id}`     – Fetch one material entry.
- `PUT /api/materials/{id}`     – Update an existing entry.

**Jobs**
- `GET /api/jobs`               – List all jobs for the current user.
- `GET /api/jobs/{id}`          – Get status and result for a single job.

**Users (Admins only)**
- `GET /api/users`              – List all users.
- `PATCH /api/users/{id}/role`  – Change a user’s role.

**n8n Webhook**
- `POST /api/webhook/n8n`       – Receive workflow callbacks to update job status.

These endpoints are consumed by the Next.js frontend (via BFF routes) and by any other clients.

---

## 5. Hosting Solutions

We host everything on AWS for reliability and cost-effectiveness:

• **AWS ECS Fargate**
  • Runs Docker containers for both Laravel API and n8n.
  • Autoscaling based on CPU/memory usage.

• **AWS RDS (PostgreSQL)**
  • Managed database with automated backups and multi-AZ replication.

• **AWS ElastiCache (Redis)**
  • In-memory caching for sessions and frequent queries (e.g., job status).

• **AWS S3 + CloudFront**
  • Stores and delivers static assets (images, CSS, JavaScript) via CDN.

• **AWS Route 53**
  • DNS management for custom domains.

Benefits:

• High availability and fault tolerance.
• Pay-as-you-go billing keeps costs tied to usage.
• Integrated security and monitoring tools.

---

## 6. Infrastructure Components

Here’s how the parts fit together:

• **Application Load Balancer (ALB)**
  • Distributes incoming traffic across Docker tasks in ECS.

• **ECS Fargate**
  • Runs Laravel and n8n services in isolated containers.

• **RDS & ElastiCache**
  • Provide persistent storage and caching.

• **S3 + CloudFront**
  • Serve static frontend assets with low latency.

• **Security Groups & VPC**
  • Network controls to isolate services and restrict access.

• **Secrets Manager**
  • Securely stores API keys, database credentials, OAuth secrets.

---

## 7. Security Measures

Multiple layers of protection keep data and services safe:

• **TLS Everywhere**
  • HTTPS enforced on ALB and CloudFront.

• **OAuth2 (Google)**
  • Ensures only real users can sign in.

• **Token-Based Sessions**
  • Laravel Sanctum (or Passport) issues short-lived tokens.

• **Role-Based Access Control**
  • Teachers vs. admins have different API permissions.

• **Input Validation & Sanitization**
  • All requests validated in Laravel before processing.

• **Encrypted Data**
  • At rest (RDS, S3) and in transit (TLS).

• **Firewall & WAF**
  • AWS WAF rules to block common web threats.

---

## 8. Monitoring and Maintenance

Keeping the system healthy involves:

• **AWS CloudWatch**
  • Aggregates logs and metrics for ECS, RDS, Redis.
  • Sends alerts on high error rates or resource exhaustion.

• **Application Performance Monitoring**
  • Optional: Datadog or New Relic integrated into Laravel.

• **Laravel Telescope (Dev/QA)**
  • In-app insights for queries, requests, exceptions.

• **Regular Backups & Exports**
  • Automated RDS snapshots and Google Sheets exports.

• **CI/CD Pipelines**
  • GitHub Actions for automated testing, deployments, and database migrations.

• **Dependency Updates**
  • Scheduled checks and patching of Laravel, n8n, and container images.

---

## 9. Conclusion and Overall Backend Summary

This backend setup brings together:

• A **Laravel API** for business logic and data management.
• **n8n** for flexible, no-code workflow automation.
• **Google Sheets** as a simple, collaborative material catalog.
• **PostgreSQL** for user accounts and job tracking.
• A **containerized, AWS-powered infrastructure** for reliability and scalability.

By combining these components with secure authentication, clear API design, and robust monitoring, the system meets the goals of fast development, easy maintenance, and a smooth user experience for both teachers and administrators.