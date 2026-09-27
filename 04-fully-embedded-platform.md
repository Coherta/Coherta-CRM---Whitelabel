# 4. Fully embedded into your own platform

Put the complete agreed outreach experience inside the host platform's navigation:
campaign overview, workflow editing, messages, statistics and necessary settings.
Users stay in the host shell and use the host's branding and session experience.

## Product boundary

- Provide a cohesive outreach area with several screens, consistent navigation,
  deep links, permissions and account context.
- Remove redundant standalone headers/sidebars and avoid a second sign-in screen
  when a supported shared-session mechanism is available.
- Keep Coherta as the outreach system of record. The host can retain its existing
  lead views, reporting model and workflows outside the embedded outreach area.
- Billing, sender connection and organization management may remain service-owned
  or move into the host according to the agreed scope.
- Lead Monitoring is optional here. Cross-module native record ownership and a
  joined lead-to-outcome data model are the additional scope of version 5.

## Architecture and source starting points

`Host shell / identity → embedded outreach routes → server adapter → outreach engine`

Choose a unified embedded application, separately deployed frontend modules, or
native host views based on the host stack. Keep route, theme, session and API
configuration centralized. An iframe-only delivery still needs a designed route
and context protocol; changing the logo in a full standalone page is insufficient.

Use the campaign dashboard, builder, messages, messages endpoint and statistics
pages in `app/campaign/`; review `app/partial/header.php`, `sidebar.php`,
`topbar.php`, `style.php`, `scripts.php`, and `app/base.php` for shell coupling.
Sender/account screens include `app/connect_linkedin.php`,
`app/connect_office365.php`, `app/connect_whatsapp.php` and `app/my-account.php`.
Inclusion of those screens depends on the selected channels and ownership.

## Additional questions

1. Which outreach screens belong in host navigation and what should their names,
   order, default route, breadcrumbs and deep-link URLs be?
2. Which host session, organization selector and permissions should every screen
   share? How should logout, renewal and tenant switching propagate?
3. Which shell/embedding method should serve all routes? How will browser back,
   refresh, embedded modals, focus and mobile navigation work?
4. Who manages sender connections, invitations, billing, seats and limits?
   Which original settings/account/upgrade screens should be replaced?
5. Which host summaries or notifications need outreach status, without creating
   a broader shared data model? What freshness is acceptable?
6. Should the original standalone application remain available as a fallback,
   and what should happen if an embedded module is temporarily unavailable?

## Partner ID and direct signup

Ask for the Coherta Partner ID (PartnerId) for each environment and the unified
host signup/provisioning flow. Follow the ZIP's `docs/PARTNER-SIGNUP.md`. All
enabled embedded entry points must share the same trusted partner context across
registration, identity-provider callbacks and onboarding. Define where customer
organizations are created and how existing organizations are linked without
changing their ownership. Verify saved attribution before enabling real signup.

## Implementation sequence

1. Complete the common and version-specific brief, including a route/module map.
2. Establish a shared theme, authenticated tenant context and service adapter.
3. Remove the standalone shell from selected screens or port them to host
   components, preserving business behavior and separating CSS/global scripts.
4. Implement campaign, workflow, inbox, statistics and agreed settings routes.
5. Add synchronized session lifecycle, deep links, notifications and consistent
   loading/empty/error behavior across screens.
6. Verify all routes under multiple roles/tenants and complete staged rollout
   configuration without bypassing entitlement enforcement.

## Acceptance

- The agreed outreach screens run inside the host navigation with consistent
  colors, logo, typography, route behavior and responsive layout.
- One authenticated identity and selected tenant govern every embedded screen;
  forbidden actions remain forbidden when their API calls are made directly.
- A user can move from campaign to workflow, lead enrollment, messages and
  statistics without losing context or encountering redundant shell navigation.
- Back/forward navigation, refresh, deep links, modals and keyboard focus work.
- Logout/expiry and tenant switching invalidate the correct caches and session
  context. One failed module does not crash the host shell.
- Service-owned data and any host status summaries have documented ownership;
  a fully unified host reporting/data model is not claimed by this version.
- Every signup/provisioning route passes `docs/PARTNER-SIGNUP.md`; new customers
  register against the configured partner through direct and redirected entry,
  without attribution drifting between embedded modules.

## Tradeoff and extension path

The product feels like part of the host while outreach remains operationally
separate. Tight shell/session coordination increases maintenance effort. Version 5
adds native Lead Monitoring and joined records/events/reporting across modules.

Subscription Center is excluded from this partner package and must not be
restored for partner customers. Preserve entitlements and use the agreed partner
account/billing flow where needed.

## Codex starting prompt

> Use version 4, Fully embedded into our platform. Read the package AGENTS.md;
> collect branding, logo, domain, host routes, embedding method, identity, tenant,
> permissions, Partner ID, signup/provisioning flow and settings/billing ownership.
> Follow docs/PARTNER-SIGNUP.md to verify that new customers are registered against
> the configured partner. Save the integration brief and embed
> the agreed outreach screens as one coherent host experience.
