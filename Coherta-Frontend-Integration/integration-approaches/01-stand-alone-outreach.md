# 1. Stand alone outreach tool

Build a separate branded outreach application with its own URL, navigation and
login. A user signs in, connects an approved sender account, creates a campaign,
enrolls leads and follows conversations and results in that application.

This is the smallest integration scope. It suits a partner who wants an outreach
product without connecting its existing platform. It still needs a working
outreach backend; the supplied frontend ZIP does not contain one.

## Product boundary

- Include campaign overview, workflow editing, sender/settings, messages and
  campaign statistics according to the agreed launch scope.
- Use a separate account and tenant lifecycle. Brand the logo, colors, copy,
  domain and support links for the recipient.
- Let the outreach service own campaign execution and outreach records.
- Lead import may be manual/CSV if supported by the approved backend contract.
- Host-platform login, record sync and Lead Monitoring are optional expansions,
  not implicit parts of version 1.

## Architecture and source starting points

`Browser → standalone frontend/server → approved Coherta service adapter`

Retaining the PHP frontend is a reasonable starting point if the deployment
supports it. Otherwise port the selected views to the recipient's chosen stack.
Keep API access, credentials and environment configuration behind a server adapter
where possible. Do not expose a shared service token in browser code.

Start with `app/base.php`, `app/login.php`, `app/partial/header.php`,
`app/partial/sidebar.php`, `app/partial/style.php`, `app/partial/scripts.php`, and
the `app/campaign/campaigns-*.php` pages. Consult the ZIP's `docs/SOURCE-MAP.md`
and `docs/DEPENDENCIES.md` before attempting to run the pages.

## Additional questions

After the common questionnaire, ask:

1. Is this invite-only or self-service? Who can create an organization or invite
   colleagues, and which account recovery experience is required?
2. Which domain/subdomain should host the application, and who owns deployment?
3. Which channels, workflow nodes and lead-import methods are needed at launch?
4. Will Coherta or the partner manage billing, plans and account activation?
5. Should monitoring, recruiter, CRM connector, onboarding and account-management
   screens be visible, or should the initial navigation contain only outreach?

## Partner ID and direct signup

Ask for the Coherta Partner ID (PartnerId) for each environment and the complete
standalone signup flow, including who creates the customer organization, which
plan/trial applies and the post-signup destination. Follow the ZIP's
`docs/PARTNER-SIGNUP.md`, even though this version has no host-platform integration.
New customers arriving directly at the branded application must be registered
against the configured partner without needing a referral link or browser storage.
Verify the saved association in the sandbox before enabling real registration.

## Implementation sequence

1. Save the selected scope and branding in `integration-brief.md`.
2. Establish a local preview with synthetic data and a configurable service
   adapter; resolve the missing includes/assets and extensionless routing.
3. Apply product name, design tokens, logo, favicon, localized copy and base paths.
4. Implement the agreed account/tenant and sender-account lifecycle against the
   approved sandbox. Replace existing tracking and checkout behavior as agreed.
5. Connect campaign, workflow, messages and statistics screens to verified service
   contracts. Label unsupported features rather than inventing backend behavior.
6. Verify the required journeys and document setup, configuration and deployment.

## Acceptance

- The application works at the chosen origin/base path, including refresh and
  deep links, with the agreed branding and responsive layouts.
- An authorized test user can sign in, select the correct organization, create
  and save a workflow, enroll a synthetic test lead, pause it and inspect results.
- Inbox and statistics handle loading, empty and failed responses clearly.
- Role boundaries and tenant isolation hold on API operations; logout clears the
  local session and returning to a protected page requests authentication.
- No host-platform account or sync connection is required for the agreed flow.
- Every enabled signup/provisioning path passes `docs/PARTNER-SIGNUP.md`:
  configured partner attribution survives direct entry and redirects, tampered
  IDs cannot override it, and existing-account ownership remains intact.
- Backend-dependent actions are verified in a sandbox, or explicitly reported as
  mocked if the recipient selected a prototype delivery.

## Tradeoff and extension path

The product can be delivered independently, but users manage a second workspace
and records do not automatically stay aligned. Add explicit lead handoff and
status synchronization later to move toward version 2.

Subscription Center is excluded from this partner package and must not be
restored for partner customers. Preserve entitlements and use the agreed partner
account/billing flow where needed.

## Codex starting prompt

> Use version 1, Stand alone outreach tool, from this Coherta handoff. Read the
> package AGENTS.md, ask all unanswered common and version-specific questions,
> including colors, logo, domain, Partner ID and direct signup flow, and record
> the answers in integration-brief.md. Follow docs/PARTNER-SIGNUP.md so each new
> customer is registered against the configured partner.
> Build the agreed standalone experience using the supplied app source and the
> approved sandbox or explicitly agreed mock delivery mode.
