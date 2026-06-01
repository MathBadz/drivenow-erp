# DriveNow ERP - Car Rental Management Platform

A microservices-based Enterprise Resource Planning (ERP) system for a single-branch car
rental business, **DriveNow Car Rental Services** (Bacolod City, Philippines). Built for
**ITSAR2 - Endterm Culminating Practical**.

The platform is composed of **eight independently deployable services** (a required
authentication service plus seven functional modules), fronted by a single API gateway and
backed by **one isolated PostgreSQL database per service**. It also includes a public,
customer-facing portal.

---

## Team - BSIT-3B

| Name | Role |
|------|------|
| Math Auric Ros Badajos | Lead Developer |
| Nathaniel Villalobos | Project Manager |
| Maika Zambra | System Analyst |
| Rea Nicole Yanson | Quality Assurance Tester |
| Shirley Ann Salazar | Researcher |

**Course Instructor:** Joao Roumil Vergara

---

## System Overview

DriveNow ERP digitizes and connects the core operations of a car-rental business behind a
single, role-secured system:

- **Operations Hub (Auth)** - identity, roles, global settings, audit log, and single sign-on.
- **Fleet** - vehicle inventory, categories, status and daily rates.
- **Rentals** - reservation lifecycle (pending → approved → active → completed / cancelled) with date-conflict checking.
- **Customers (CRM)** - customer profiles, loyalty tiers, feedback and blacklist.
- **Billing** - invoices, payments (cash / GCash / card), penalties and refunds.
- **Maintenance** - vehicle maintenance records (inspection / repair / scheduled / damage).
- **Analytics** - KPI dashboards and date-range reports with CSV export.
- **Client Portal** - public website for browsing vehicles and self-service bookings.

**Architecture highlights**

- **Microservices** - each service owns its codebase, HTTP API and database; no shared databases.
- **API Gateway** - a single nginx entry point routes by host/subdomain to each service.
- **Authentication & Authorization** - stateless **JWT** single sign-on (a signed `drivenow_token` cookie) plus **role-based access control** (admin / staff / maintenance / customer). Internal APIs are versioned under `/api/v1` and protected by a shared service token.
- **Containerization & Orchestration** - every service is a Docker container, orchestrated with Docker Compose.

### Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 13 (PHP 8.4) |
| Frontend | Vue 3 + TypeScript via Inertia.js, Tailwind CSS |
| Database | PostgreSQL (one database per service) |
| Auth | Laravel Fortify + HS256 JWT single sign-on |
| Gateway | nginx |
| Containerization | Docker, Docker Compose |

### Services & Ports (local)

| Service | Port | Database |
|---------|------|----------|
| auth-service (Operations Hub) | 8001 | drivenow_auth |
| fleet-service | 8002 | drivenow_fleet |
| rental-service | 8003 | drivenow_rental |
| crm-service | 8004 | drivenow_crm |
| billing-service | 8005 | drivenow_billing |
| maintenance-service | 8006 | drivenow_maintenance |
| analytics-service | 8007 | drivenow_analytics |
| client-service (Client Portal) | 8008 | drivenow_client |
| gateway | 8080 | - |

---

## Default Credentials

The same seeded accounts work **locally and on the live deployment** (the demo data is seeded
automatically on first deploy). **Password for every account: `password`**

### Operations Hub - `…-auth` / `localhost:8001`

| Email | Role | Access |
|-------|------|--------|
| `admin@drivenow.test` | Administrator | Full access - all modules, system settings, user management |
| `staff@drivenow.test` | Front-Desk Staff | Rentals, customers, billing, fleet |
| `maintenance@drivenow.test` | Maintenance Staff | Maintenance records |

### Client Portal - `…-client` / `localhost:8008`

| Email | Role | Access |
|-------|------|--------|
| `customer@drivenow.test` | Customer | Browse vehicles, create and track bookings, manage profile |

You may also register a new customer account directly on the Client Portal.

---

## Prerequisites

**To run with Docker (recommended) - only these are required:**

- [Docker](https://docs.docker.com/get-docker/)
- Docker Compose (included with Docker Desktop)
- Git

The PHP, Composer and Node tooling are handled inside the containers, so you do **not** need
them installed on your machine for the Docker workflow.

**To run a single service without Docker (optional):** PHP 8.4, Composer, Node.js 22, npm,
and a PostgreSQL 18 instance.

---

## Running the System Locally (Docker)

From the repository root:

```bash
# 1. Clone the repository
git clone <your-repo-url> drivenow
cd drivenow

# 2. Generate the shared secrets (creates the .env file)
#    Windows (PowerShell):
pwsh ./scripts/generate-secrets.ps1
#    macOS / Linux:
./scripts/generate-secrets.sh

# 3. For the FIRST run, enable demo-data seeding in the generated .env:
#       APP_AUTO_SEED=true

# 4. Build and start the whole stack (8 services + PostgreSQL + gateway)
docker compose up -d --build
```

The first build takes a few minutes (it installs dependencies and builds the front-end for
each service). Once it finishes:

- **Operations Hub (admin):** http://localhost:8001 - sign in with `admin@drivenow.test` / `password`
- **Client Portal (public):** http://localhost:8008 - sign in with `customer@drivenow.test` / `password`
- Other modules: http://localhost:8002 - http://localhost:8007
- API gateway: http://localhost:8080

> Single sign-on works across all admin modules: log in once at the Operations Hub and the
> sidebar links open each service already authenticated.

**Useful commands**

```bash
docker compose ps                 # service status
docker compose logs -f auth-service   # tail a service's logs
docker compose down               # stop the stack
docker compose down -v            # stop and wipe all database data
```

After the first successful run, set `APP_AUTO_SEED=false` in `.env` to avoid re-seeding on
restart.

---

## Documentation

Formal project documentation is in the [`docs/`](docs/) directory:

- **`docs/Technical_Documentation.pdf`** - architecture, microservices, API, database design, security, deployment.
- **`docs/Business_Documentation.pdf`** - company profile, processes, roles & permissions, business rules.

---

## Note on Repository History and Development Setup

During development, the team's original project repository was lost - the working copy and
its history were destroyed and could not be recovered. To meet the submission deadline, the
**Lead Developer (Math Auric Ros Badajos)** rebuilt the entire system from the team's designs
and notes, and also handled the containerization and deployment.

Additionally, the other team members did not have personal laptops or devices available for
coding. All development work was done on the **Lead Developer's single laptop**, with the full
team present and contributing - through design decisions, requirements analysis, testing, and
review - throughout every phase of the project.

For this reason, the recent Git commit history is concentrated under a single member's account.
This does **not** reflect the actual division of work: the project was a team effort throughout,
with the System Analyst leading the business/domain analysis and data design, Quality Assurance
verifying every module and workflow, the Researcher covering the technology stack, security and
deployment options, and the Project Manager coordinating scope, schedule and deliverables (see
the team table above and the weekly progress report submitted with this project).

---

*ITSAR2 - Endterm Culminating Practical · BSIT-3B · DriveNow Car Rental Services*
