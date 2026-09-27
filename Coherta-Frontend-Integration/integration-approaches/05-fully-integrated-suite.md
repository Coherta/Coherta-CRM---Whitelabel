# 5. Fully integrated version: Lead Monitoring, Workflow, Messages and Statistics

Make all four capabilities part of native host journeys and records. A user
monitors a lead, reviews a signal, enrolls the lead in a workflow, handles the
resulting conversation and sees the outcome in reporting without switching
products or losing the lead's identity.

This includes the user experience of version 4 and a deeper data and operational
integration. Lead Monitoring, Workflow, Messages and Statistics are all required.

## Module scope and source map

| Module | Integrated behavior | Source starting points in the ZIP |
| --- | --- | --- |
| Lead Monitoring | Watchlists/signals on host lead/company records, configurable actions and visible monitoring state | `app/stakeholder-monitoring.php`, `app/assets/js/stakeholder-monitoring.js`, `app/company-activity.php`, `app/assets/js/company-activity.js`, `app/leads.php`, `app/leads_people.php`, `app/lead.php` |
| Workflow | Host records enroll in approved workflows; progress and controls appear in host journeys | `app/campaign/campaigns-workflow-builder.php`, `campaigns-dashboard.php`, `screening-results.js` |
| Messages | Conversations link to the same host lead and sender, with role-aware assignment and replies | `app/campaign/campaigns-messages.php`, `campaigns-messages-endpoint.php` |
| Statistics | Monitoring, enrollment, outreach and response outcomes appear in consistent host reporting | `app/campaign/campaigns-statistics.php`, `app/interaction-scoreboard.php` |

Paths abbreviated in the same table cell share that cell's preceding directory.
Inspect the actual monitoring pages with the recipient: “Lead Monitoring” is the
target product module and may require combining or extending existing stakeholder
monitoring and company-activity views. Do not assume every requested signal or
metric is available through the current backend.

## Architecture and data ownership

`Host UI → host integration service → verified Coherta APIs`

`Verified service events or polling → tenant-scoped reconciliation → host records/reporting`

Build explicit mappings for tenant, user, lead/contact, company, workflow,
enrollment, conversation/message and signal/event IDs. Decide ownership per field,
including what the host may edit and what is a service-derived value. Maintain
stable external IDs and source timestamps. Prefer the host's existing database,
queue and observability conventions.

Treat event/webhook names and schemas as a design task to agree with the service
owner; this ZIP provides no confirmed public event contract. If only polling is
available, record its frequency, pagination/cursor strategy and freshness limits.
Use idempotent processing, deduplication, retry/reconciliation and deletion
propagation. Make disconnect/reconnect and partial failures visible to operators.

## Additional questions

1. Which monitoring signals/watchlists and lead/company fields are required?
   What makes a lead eligible, and should enrollment be manual or rules-driven?
2. What is the canonical lead/contact identity? Which system owns each editable
   field, and how are merges, duplicates, external IDs and deletions handled?
3. How should a signal create a notification, task or workflow enrollment? Who
   approves automation and how are repeat enrollment and suppression prevented?
4. Which workflow node types, schedules, execution controls, sender permissions
   and run-state transitions must appear in host records?
5. How do conversations map to leads, sender accounts and assigned teammates?
   What reply/draft, unread, attachment and delivery/error behaviors are required?
6. Which metrics and conversion definitions should reporting use? Define event
   timestamps, reporting timezone, filters, attribution windows and denominators.
7. Which verified APIs/events support all four modules, and where are capability
   gaps? What are the acceptable freshness, rate and reconciliation limits?
8. Which historical records must be backfilled? What retention, deletion, audit,
   residency and access rules apply to messages and monitored data?
9. Which team owns the integration service, queues, outages and reconciliation?
   What staged rollout and rollback preserve existing records and prevent sends?

## Partner ID and direct signup

Ask for the Coherta Partner ID (PartnerId) for each environment, the host's
customer signup/provisioning flow and any multi-partner domain/tenant mapping.
Follow the ZIP's `docs/PARTNER-SIGNUP.md`. Persist the verified customer-to-partner
relationship at creation and use it consistently across Lead Monitoring,
Workflow, Messages and Statistics. Direct signup must work without referral
state. Define separate policies for existing customers, migration, invitations
and account linking so normal sign-in cannot change partner ownership.

## Implementation sequence

1. Complete the common and module-specific brief. Create an explicit capability
   matrix: verified API, mock only, or backend work required for each behavior.
2. Establish identity, tenants, permissions, brand tokens and the host service
   boundary. Agree on ID mappings, field ownership and versioned data contracts.
3. Implement Lead Monitoring on host records and the intended signal review flow.
4. Connect eligible leads to workflow enrollment with duplicate prevention,
   permission checks and agreed approval/suppression rules.
5. Integrate conversation history, sender context and permitted replies in the
   host inbox and lead detail. Show service delivery/failure states accurately.
6. Build shared reporting definitions and reconcile totals against the agreed
   source for a known fixture set and date window.
7. Add event/polling recovery, backfill and deletion propagation. Roll out by test
   tenant/module flag and document operations and remaining backend dependencies.

## Acceptance

- A synthetic lead can move through monitoring, signal review, workflow enrollment,
  conversation and measured outcome with one consistent host identity.
- Each module is accessible through native host navigation and record context;
  agreed data changes appear within the stated freshness target.
- Duplicate or out-of-order events/retries do not duplicate leads, enrollments,
  replies or metric counts. Reconciliation recovers from interrupted processing.
- Role and tenant boundaries hold across screens, exports, background jobs and
  direct API calls. Sender access and reply permissions are enforced server-side.
- Known fixtures produce expected totals for the agreed metric definitions,
  reporting timezone and filters; unavailable metrics are marked explicitly.
- Deletion, disconnection, session expiry and service outages have tested outcomes.
- Documentation lists the real contracts used, capability gaps, ownership,
  deployment configuration and any behavior that remains mocked.
- All signup/provisioning methods pass `docs/PARTNER-SIGNUP.md`; a direct signup
  is saved against the configured partner and all four modules resolve the same
  authorized customer/partner context without crossing partner boundaries.

## Tradeoff

This produces the most cohesive product but requires the strongest coordination
between frontend, identity, data, backend and operations teams. Budget the work
around verified service capabilities. A reskinned collection of pages alone does
not meet version 5's shared-record and end-to-end acceptance criteria.

Subscription Center is excluded from this partner package and must not be
restored for partner customers. Preserve entitlements and use the agreed partner
account/billing flow where needed.

## Codex starting prompt

> Use version 5, Fully integrated Lead Monitoring, Workflow, Messages and Statistics.
> Read the package AGENTS.md and ask all missing common and module-specific
> questions, including colors, logo, domain, Partner ID, signup/provisioning flow,
> host stack, identity, field ownership, events, reporting definitions and
> deployment. Follow docs/PARTNER-SIGNUP.md to verify saved customer attribution.
> Save the brief, verify backend
> capabilities, then build and test the complete lead-to-outcome journey.
