# 2. Slightly connected outreach tool, connected but still stand alone

Keep outreach in a separate branded application, with a deliberate handoff from
the host platform. A user opens a lead in the host, chooses “Open in outreach,”
works in the separate application and returns to the original record. Selected
statuses or outcomes can flow back to the host.

## Product boundary

- Retain standalone outreach navigation, deployment and execution.
- Add agreed launch links, a validated return path, lead/contact handoff and a
  limited status summary in the host.
- Optional SSO/account linking requires a supported identity mechanism. The ZIP
  does not establish that a Coherta partner SSO endpoint exists.
- Define each synchronized field and its owner. Do not silently synchronize the
  entire CRM or make both systems authoritative for the same field.
- Full host-native inbox, workflow editor and analytics remain outside this scope.

## Architecture and source starting points

`Host lead page → host server integration → separate outreach application`

`Outreach outcomes → verified events or scheduled polling → host status summary`

Use a server-managed mapping of host tenant/record IDs to outreach tenant/record
IDs. Prefer an opaque, short-lived handoff reference that the server resolves;
do not put access tokens, email addresses or a whole lead record in launch URLs.
Allowlist return destinations. If SSO is available, bind its exchange to the user,
tenant and intended destination, with expiration and replay protection.

Start with the standalone sources in version 1 plus `app/leads.php`,
`app/leads_people.php`, `app/lead.php`, `app/plugin.php` and the campaign pages.
Existing connector views are examples of Coherta behavior, not a general partner
integration API. Confirm contracts with the service owner.

## Additional questions

1. Which host pages launch outreach, with which selected records or filters?
2. Should users authenticate separately, link an existing account, or use a
   verified SSO flow? Which provider/contract is already available?
3. Which fields travel from the host to outreach and which outcomes return? What
   are the record IDs, ownership rules and update frequency?
4. What should happen when a record already exists, changes in both systems,
   is deleted, or belongs to another tenant?
5. Where should the “Back to platform” link go, and which destinations are allowed?
6. Are webhooks supported, or should we use polling/manual refresh? Who handles
   failed transfers and reconnection?

## Partner ID and direct signup

Ask for the Coherta Partner ID (PartnerId) for each environment and whether new
customers register in the host, in the separate outreach app, or through a hosted
signup/SSO flow. Follow the ZIP's `docs/PARTNER-SIGNUP.md` for every entry point.
Carry the trusted partner context through account creation and handoff, including
direct visits without referral state. Define how existing-account linking differs
from creating a new customer; logging in must not overwrite existing ownership.

## Implementation sequence

1. Complete the common questionnaire and define the standalone product scope.
2. Add configurable launch/return navigation and host branding.
3. Define identifier mappings and a narrow handoff contract using synthetic
   records. Implement record-level authorization on both sides.
4. Implement the supported login/account-linking experience and lead handoff.
5. Add only the agreed outcome synchronization, with idempotent writes, bounded
   retries, deduplication and a visible last-sync/error state.
6. Test first-time linking, repeat launches, expired links, conflicts and outages.

## Acceptance

- Opening outreach from a host lead resolves the intended record and tenant;
  returning takes the user to the allowlisted host destination.
- Repeating a handoff does not create duplicate leads or enrollments.
- Agreed status changes appear in the host within the accepted freshness target.
- Expired/replayed handoffs, unauthorized records and external return URLs fail
  safely; no token or sensitive lead payload appears in the URL.
- When the link/sync service is unavailable, the separate outreach app remains
  usable and the host reports the sync problem without losing data.
- Separate sign-in and account-linking behavior are clear to the user if SSO is
  unavailable. Unsupported SSO/webhooks are not presented as completed features.
- Every enabled signup/provisioning path passes `docs/PARTNER-SIGNUP.md`, including
  direct registration, the host handoff, saved partner attribution and preservation
  of existing-account ownership.

## Tradeoff and extension path

This reduces repeated data entry while preserving independent deployment, but
users still move between applications and may see delayed status updates.
The identity and record mappings can later support version 3 or version 4.

Subscription Center is excluded from this partner package and must not be
restored for partner customers. Preserve entitlements and use the agreed partner
account/billing flow where needed.

## Codex starting prompt

> Use version 2, Slightly connected but still stand alone. Read the package
> AGENTS.md and collect missing branding, logo, domain, host platform, identity,
> Partner ID, direct signup/provisioning flow, lead-handoff, return-navigation and
> synchronization decisions. Follow docs/PARTNER-SIGNUP.md to verify that new
> customers belong to the configured partner. Record the brief
> before building the standalone outreach app and its narrowly defined host links.
