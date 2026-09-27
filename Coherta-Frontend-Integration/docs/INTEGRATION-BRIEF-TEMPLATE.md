# Coherta integration brief

Copy this file to `integration-brief.md` at the package/project root and complete
it during discovery. Never put secrets here.

## Selected delivery

- Version (1–5) and matching guide:
- Product/platform name, audience and first user journey:
- Delivery mode (mock prototype / sandbox-connected / production-ready code):
- Host repository, stack and design system:
- Included modules and explicitly excluded features:
- Product owner, deadline and success criteria:

## Branding and content

- Brand mode and Coherta attribution:
- Primary / secondary / accent / semantic colors:
- Logo / dark logo / icon / favicon paths or URLs:
- Accepted temporary assets, if any:
- Fonts, typography, density, radii and dark mode:
- Languages, locale, timezone, formats and currency:
- Accessibility, mobile and browser requirements:
- Support, help, terms and privacy links:
- Analytics/tracking decision:

## Architecture and environments

- Retain PHP / port selected views / native host implementation:
- Embedding method and navigation/deep-link behavior:
- Development origin + base path:
- Staging origin + base path:
- Production origin + base path:
- Allowed parent origins and redirect destinations:
- Hosting, DNS/TLS, runtime and CI/CD owners:
- Local build, preview and test commands (complete after implementation):

## Identity and commercial model

- Identity provider / separate login / supported session exchange:
- User and tenant mapping, including multi-tenant selection:
- Role-to-capability matrix:
- Provisioning, invitation, logout, renewal and account deletion behavior:
- Billing owner, subscription/seat/usage entitlements and enforcement:
- Sender connection owner and approved channels:

## Partner attribution and signup (required for all versions)

- Partner name and approved Coherta Partner ID (PartnerId):
- Development / staging / production Partner IDs and confirmation owner:
- Single-partner deployment or server-owned domain/tenant-to-partner mapping:
- Trusted configuration location and who may manage it:
- Enabled signup entry pages and methods; explicitly disabled methods:
- New customer organization vs. existing organization membership behavior:
- Required signup fields, initial role, plan/trial, onboarding and return URL:
- Verified account-creation endpoint, exact Partner ID field path/type and auth:
- Server-side validation and persisted relationship verification method:
- Partner context across email verification, SSO and cross-domain redirects:
- Precedence over referral parameters, cookies and local storage:
- Missing/invalid/disabled/conflicting Partner ID behavior:
- Existing-user linking, ownership preservation and duplicate-submit policy:
- Invitation/admin/SSO provisioning behavior, if used:
- Sandbox partner/test customer and attribution evidence for each signup path:
- Real signup enabled only after verification; remaining blockers and owner:

## Data and modules

- Sandbox documentation/contact and secret-store references (names only):
- Field ownership and record identifier mappings:
- Sync directions, frequency, events/polling and conflict handling:
- Duplicate prevention, retries, deletion, retention and audit:
- Lead Monitoring: signals, watchlists, scoring and enrollment policy:
- Workflow: nodes, approval, schedules, limits and execution owner:
- Messages: channels, threading, assignment, drafts and replies:
- Statistics: metric definitions, attribution, timezone and freshness:
- Migration/backfill scope and validation:
- Answers to every selected guide's additional questions:

## Acceptance and rollout

- Required end-to-end journeys:
- Test tenants/accounts and sandbox constraints:
- Feature flags, rollout owner and rollback behavior:
- Evidence collected and remaining mocked behavior:

## Open decisions

| Question | Answer / accepted default / N/A reason / deferred | Owner | Milestone blocked | Agreed fallback |
| --- | --- | --- | --- | --- |
| Complete for every unresolved applicable item | | | | |
