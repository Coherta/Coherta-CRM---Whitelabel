# Partner ID and direct customer signup

This requirement applies to all five integration versions. Customers who create
an account directly through the partner's platform must be registered against
that partner, including when they arrive without a referral link, cookie or saved
browser state. The agent must collect the Partner ID and signup-flow decisions,
implement attribution in the recipient's integration, and verify the saved
relationship. This document does not change the supplied application source.

## Ask before building signup

Follow the question rules in `../AGENTS.md`: always ask in English, one question
at a time through the supported interactive prompt/answer tool, and wait for the
answer before asking the next. Split compound entries into separate prompts,
skip already known answers and confirmed facts, and record the answers in
`integration-brief.md`:

1. What is your approved Coherta Partner ID (PartnerId)? Which values belong to
   development, staging and production, and who can confirm the partner accounts?
2. Is there one partner per deployment, or several? For several partners, which
   trusted server-side domain/workspace mapping selects the correct Partner ID?
3. Where should customers sign up: a partner-hosted form, embedded form, a
   Coherta-hosted signup redirect, invitations or supported SSO provisioning?
   Which email/password, passwordless or social-login methods are actually supported?
4. Should signup create a new customer/company, or add a user to an existing
   customer? Which fields, starting role, plan/trial, onboarding steps and
   post-signup destination are required?
5. Which registration API contract accepts partner attribution, including the
   exact field path and value type? Does the backend validate the configured
   partner, and how can we inspect the saved association in a sandbox?
6. How does partner context survive email verification, onboarding and any
   cross-domain or identity-provider redirects? What happens when that context
   expires or conflicts with a referral link or existing account?
7. What should happen for an existing email, invited colleague, already-linked
   account or duplicate submit? Who may explicitly approve an ownership change?
8. Which sandbox partner/customer will be used to verify every signup path, and
   who owns any missing API, identity or provisioning work?

A Partner ID is an attribution/configuration identifier. It is not an API
credential, an organization/user ID, a LinkedIn advertising partner ID, or an
affiliate-network transaction ID. Do not invent one or copy numeric IDs from the
snapshot. Ask for secret-store references separately if authentication is needed.

## Source evidence and contract verification

The supplied `app/setup-funnel.php` calls `getAffiliateAttribution()`, which reads
an affiliate query value and can fall back to local storage. Its account-creation
code builds `affiliatePayload.partner_id` and spreads that object into the JSON
body before posting to `/users/create`. The observed request field is therefore
top-level `partner_id` (snake case); `PartnerId` is the discovery label, not the
observed wire-field spelling. This is evidence of frontend usage, not a verified
public partner API contract. Confirm the field, type, authorization, validation
and saved relationship with the backend owner before implementing the adapter.

The supplied `app/sign-up.php` has a separate `createUser(payload)` flow. Review
every enabled registration/provisioning path; changing one screen does not prove
that all new customers receive partner attribution. Existing numeric IDs and
referral-specific plan rules are not defaults for a new partner.

## Required implementation behavior

1. Put the confirmed Partner ID in environment-specific server configuration,
   such as `COHERTA_PARTNER_ID`, or an authorized multi-partner mapping. The
   packaged `.env.example` proposes names only; the snapshot does not read it.
2. Resolve the partner on the server from trusted deployment or authenticated
   tenant context. A query string, hidden form field, cookie or local-storage
   value must not be able to replace that partner assignment arbitrarily.
3. Validate the selected partner and construct attribution using the verified
   backend request contract at customer creation/provisioning time. Reuse the
   same rule across every enabled signup method.
4. Direct visits and clean browsers must still receive the configured Partner
   ID. If a hosted/SSO flow requires redirects, use the supported server-bound
   state/session mechanism and allowlisted return URLs to preserve context.
5. Missing, invalid, disabled or conflicting partner configuration must block
   real signup with an actionable error. Do not silently omit attribution and
   create an unassigned customer. An agreed mock preview can continue separately.
6. Preserve the partner through retries and onboarding. Handle duplicate submits
   using the backend's supported idempotency/duplicate-detection mechanism so
   they do not create duplicate customers or conflicting assignments.
7. Signing into an existing account must not reassign it. Invitations to an
   existing organization inherit its authorized context. Account linking or
   ownership transfers need an explicit supported policy and permissions.
8. Verify the persisted customer/company-to-partner relationship through an
   approved sandbox API or partner/admin view. Seeing the ID in a request or
   receiving a successful HTTP response is insufficient evidence by itself.
9. Keep registration disabled until the configuration and verification are
   complete. Document remaining backend work and who owns it. Do not report a
   successful mock as verified real attribution.

For a deployment without self-service signup, record that choice and apply the
same attribution rules to any invitation/admin/SSO provisioning that creates
new customers. Partner attribution requirements are not waived by hiding the form.

## Acceptance checks

Run these with synthetic customers and the agreed sandbox, for each enabled
signup method. Do not create production customers as part of handoff verification.

| Scenario | Expected result |
| --- | --- |
| Direct signup in a clean browser, no referral parameters/storage | New customer is saved against the configured Partner ID |
| A conflicting query, cookie, local-storage or hidden-form Partner ID | Cannot override the server-selected partner or cross tenant boundaries |
| Verification, onboarding, hosted signup or SSO redirects | Partner context remains bound to the same signup and saved account |
| Missing, malformed, disabled or unknown Partner ID | Registration is blocked clearly; no unattributed account is created |
| API timeout, retry or double submit | At most one intended customer association; recoverable errors are visible |
| Existing customer logs in or signs up again | Existing ownership is preserved; supported linking rules are followed |
| Invited colleague joins an existing organization | Membership uses the organization's authorized partner context |
| Two configured partners use the same platform | Each receives only its own customers; mappings cannot be forged |
| Development/staging/production configuration | Each environment uses its approved ID; no example IDs reach real signup |
| Read back the created customer or view it in the partner portal | Persisted association matches the expected partner |

Record the tested method, environment, synthetic customer reference and verified
Partner ID in the integration evidence. Use no passwords, access tokens or
personal customer data in logs, screenshots or the handoff documentation.
