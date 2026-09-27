# Integration discovery questionnaire

Follow the question rules in `../AGENTS.md`: always ask in English, one question
at a time through the supported interactive prompt/answer tool, and wait for the
answer before asking the next. This questionnaire is an internal checklist;
split compound entries into separate prompts. Reuse information already provided
and skip the confirmed facts in `../AGENTS.md`. The selected approach
adds specific questions in its own guide. All applicable decisions must be
recorded before implementing the behavior they control.

## 1. Version, product and destination

1. Which version (1–5) should we build? What user journey should work first?
2. What is the product name, company name and audience? Is this Coherta-branded,
   co-branded, or white-label? Should “Powered by Coherta” appear, and where?
3. What is the host platform's name, repository path and framework/runtime?
   Are there existing components, navigation patterns, design files or guidelines?
4. Should this delivery be an interactive prototype with synthetic data, a
   sandbox-connected integration, or production-ready code? What deadline and
   success criteria apply, and who owns the product decisions?

## 2. Visual identity

1. What primary, secondary and accent colors should we use (hex values if known)?
   What colors should indicate success, warning and error? Is dark mode required?
2. What is your logo? Provide the local file path or accessible asset URL, and
   light/dark versions, icon-only mark, favicon and usage guidelines if available.
   If assets are missing, may we use a temporary text mark, and what should it say?
3. Which fonts, font weights, button shapes, corner radii and layout density fit
   your platform? Should we reuse your existing design tokens and components?
4. What languages, default locale, timezone, date/number formats and currency are
   required? What mobile layouts and accessibility requirements should we follow?
5. What support email/URL, help links, terms and privacy URLs should the UI use?
   Which existing Coherta copy, illustrations and tracking should be replaced?

## 3. Domain, navigation and hosting

1. What should the domain name be? Specify development, staging and production
   origins, and the intended path such as `/outreach` or `/workspace/growth`.
2. Which organization controls DNS, TLS, hosting and deployment? What runtime,
   reverse proxy, CDN and CI/CD conventions already exist?
3. What should the entry URL, default screen, deep links and return-to-platform
   destination be? Must links preserve the selected lead, company or workflow?
4. For embedding, what parent origins may host the module? Does the platform
   prefer an iframe, a separately deployed frontend module, or native components?
   Are same-origin hosting and authenticated embedding feasible?
5. What are the approved external services, analytics and error-reporting tools?
   Which outbound network calls are allowed in development and production?

## 4. Accounts, permissions and tenants

1. Which system owns sign-in: a separate outreach account or the host identity
   provider? What supported sign-in/session exchange mechanism exists today?
2. How do host organizations/workspaces map to Coherta companies and users? Can
   a user belong to multiple tenants, and how is the active tenant selected?
3. Which roles exist (owner, admin, operator, viewer, etc.)? Who can edit workflows,
   enroll leads, send messages, export data, manage senders or change billing?
4. What should invitation, provisioning, logout, expired-session recovery and
   account deletion do? Is account linking/migration required for existing users?
5. Who owns subscriptions, entitlements, seats and usage limits? Should the host
   hide or replace Coherta checkout/account screens, and what enforces access?

## 5. Partner ID and direct customer signup (all versions)

1. What is your Coherta Partner ID (PartnerId) for development, staging and
   production? Who can confirm these identifiers and their intended partner
   accounts? Is the deployment for one partner or several?
2. How should a new customer sign up directly with the partner: a partner-hosted
   form, embedded form, Coherta-hosted signup redirect, invitation or supported
   SSO provisioning? Which entry pages and alternative signup methods are required?
3. Should signup create a new customer organization or join an existing one? What
   fields, initial role, onboarding steps, plan/trial and post-signup URL are needed?
4. Which backend contract assigns the new customer to the Partner ID? Where does
   the ID belong in the request, who validates it, and how can we verify the saved
   customer-to-partner relationship in the sandbox?
5. How should the partner context survive onboarding, email verification and
   cross-domain/social-login redirects? What should happen if it is missing,
   invalid, disabled, conflicting or does not match the authenticated tenant?
6. How should existing users, invitations and duplicate signup submissions behave?
   When is account linking allowed, and how is existing partner ownership retained?
7. If multiple partners are supported, which server-owned domain/tenant mapping
   selects the Partner ID? Who may configure it, and what rule resolves conflicts
   with referral parameters or affiliate tracking?
8. Who owns the partner signup implementation and backend verification? Which
   sandbox partner and synthetic customer can demonstrate correct attribution?

Read `PARTNER-SIGNUP.md` for implementation requirements and acceptance checks.
Ask these questions even for version 1. If self-service signup is disabled, record
that choice and define how any invitation/provisioning flow assigns new customers
to the partner. A missing Partner ID blocks real registration, not discovery or
an explicitly agreed mock preview.

## 6. Modules and outreach behavior

1. Which screens and features are included at launch? Name what should be hidden.
   Version 5 must cover Lead Monitoring, Workflow, Messages and Statistics.
2. Which outreach channels and sender-account connection flows are required?
   Who supplies approved sender accounts and connection permissions?
3. Who creates and approves workflows and templates? Which node types, schedules,
   timezones, enrollment rules, pause/stop controls and sending limits are needed?
4. Which message views, assignments, drafts, reply permissions, unread states and
   delivery/error statuses should the integration expose?
5. Which monitoring signals, watchlists, lead fields, notifications and metrics
   matter? What should happen when a monitored event qualifies a lead?

## 7. Data and service contracts

1. Is an approved Coherta sandbox/API contract available? Where are the endpoint
   documentation, sample responses, scopes, rate limits and service-owner contact?
   Provide secret-store references or environment-variable names, never values.
2. Which system owns leads, companies, workflows, conversations, message history,
   monitoring settings and reporting? Define ownership at field level where shared.
3. Which identifiers link records? What data is transferred, in which direction,
   how often, and how are duplicates, conflicting edits and deleted records handled?
4. What events or webhooks actually exist? If unavailable, is polling or explicit
   refresh acceptable? What freshness and latency does the user experience need?
5. What retry, idempotency, timeout and recovery rules are needed? Who operates
   failures, and which logs/status pages should expose sync health?
6. What retention, deletion, audit, consent/suppression and data-residency rules
   apply to the exchanged data? What existing platform controls should be reused?
7. Is historical import needed? Which records and date range, and who validates
   counts, relationships and deduplication before enabling write operations?

## 8. Delivery and remaining unknowns

1. Who supplies missing brand assets, host access, API contracts and infrastructure?
   Which milestone is blocked by each missing item?
2. Which end-to-end journeys and browsers/devices constitute acceptance? Who will
   provide test tenants and sender accounts, and who will review the integration?
3. How should the feature roll out (test tenant, flag, staged release), and what
   should rollback disable without losing records or duplicating outreach?

Persist the decisions in `integration-brief.md`. “Use the existing host default”
is valid when the recipient explicitly chooses it and the default is identifiable.
“Unknown” needs an owner, a blocked milestone and an agreed prototype fallback.
