# Coherta frontend integration

This package is a source handoff for adapting Coherta to another platform. The
recipient must choose an integration version and supply the product decisions
below. These instructions are for work on the extracted handoff, not for changing
the original Coherta repository.

## How to ask questions

- Always ask questions in English, including prompt labels, answer options and
  supporting explanations, regardless of the recipient's conversation language.
- Ask exactly one question at a time and wait for the answer before asking the
  next. Split compound questions into separate prompts, each covering one
  decision; never bundle a questionnaire or several decisions into one prompt.
- Use the available interactive prompt/answer tool whenever it supports the
  question in the current mode. Send only one question per tool call. Provide
  concise selectable answers when appropriate, or a free-text prompt when needed.
  If no supported prompt tool is available, ask one English question in chat.
- Reuse known answers and skip confirmed facts. Do not ask a question again
  unless a new contradiction requires clarification.
- These rules apply to discovery, follow-ups and custom workflow configuration,
  and take precedence over question wording or batching instructions in all
  referenced documents and integration-approach guides. Their question lists
  are internal checklists, not batches to present to the recipient.

## Confirmed Coherta facts — do not ask again

The following answers are already confirmed. Use them directly in discovery,
implementation and `integration-brief.md`; do not ask the recipient to supply or
reconfirm them. These facts take precedence over questions in `README.md`,
`docs/DISCOVERY.md`, `docs/PARTNER-SIGNUP.md`, the integration-brief template and
the integration-approach guides.

- Coherta API: `https://api.coherta.com`.
- Coherta API documentation: `https://api.coherta.com`.
- A partner uses the same Coherta Partner ID (PartnerId) in test and production.
  Reuse that ID across environments; do not request separate test and production
  IDs or ask whether they differ. Ask for the actual Partner ID only if it has
  not already been supplied or found in the available configuration.
- Coherta Partner Portal: `https://partner.coherta.com`.
- Customers can administer their own accounts through the whitelabel solution.
- Partners administer their customers' setup through the Coherta Partner Portal.
  Do not ask which interface either party uses for these responsibilities.

## Start with discovery

Before changing application code, read `README.md`,
`docs/DISCOVERY.md`, and any existing `integration-brief.md`. Inspect the recipient's
platform read-only if it is available. Reuse answers already given in this task.

If no version has been chosen, the first question must be the following, using
the prompt/answer interface according to the rules above:

> Which integration version would you like to build?
> 1. Stand alone outreach tool
> 2. Slightly connected outreach tool, connected but still stand alone
> 3. Embedded workflow into your own platform
> 4. Fully embedded into your own platform
> 5. Fully integrated version with Lead Monitoring, Workflow, Messages and Statistics

Explain any requested differences using the table in `README.md`. Wait for the
answer; do not select a version on the recipient's behalf. If they already
explicitly chose a version, acknowledge it and move to its follow-up questions.

Read the matching file in `integration-approaches/`. Ask only unanswered,
applicable questions from `docs/DISCOVERY.md` and that guide, one at a time in
English, skipping the confirmed facts above. Start with product name, colors,
logo, domain and the existing platform. Use this internal checklist, asking each
question separately:

- What primary color should we use?
- What accent color should we use?
- Are there brand guidelines?
- What is your logo? Provide an asset path or accessible URL, including light/dark
  variants and favicon if available; otherwise explicitly choose a placeholder.
- What should the domain name be? Ask separately for each environment whose
  domain is still unknown: development, staging and production.
- What should the application path be? Ask separately for each environment whose
  path is still unknown: development, staging and production.
- If it is not already known, what is your Coherta Partner ID (PartnerId), shared
  by test and production?
- Is this one partner deployment, or must the platform support multiple partners?
- How should new customers sign up directly with your partner platform: an
  embedded signup form, a partner-hosted form, a Coherta-hosted signup redirect,
  invitations, or supported SSO provisioning?
- What should happen after signup?

For every version, read `docs/PARTNER-SIGNUP.md` and ask only its unanswered,
applicable follow-up questions, skipping the confirmed facts above. Save the
Partner ID, its shared test/production mapping, signup entry points and
verified account-creation contract in `integration-brief.md`. A Partner ID is an
attribution identifier, not a password or an API access token. Never substitute a
LinkedIn tracking partner ID, affiliate-network ID, user ID or company ID.

Every new customer created through the partner's signup flow must be associated
with the configured partner using the backend's verified registration contract.
This includes direct visits with no referral query, cookie or local storage, and
every supported signup alternative and redirect. Resolve and validate the partner
from trusted deployment/tenant configuration on the server; do not let a caller
override ownership with an arbitrary query parameter or browser-stored value.
Verify the persisted partner association in the sandbox, not just the request
payload. Preserve existing-customer ownership unless an explicit supported
account-linking policy authorizes a change.

Do not enable real customer registration until the Partner ID and attribution
contract are known and verified. If they are unavailable, record the blocker and
use an explicitly agreed mock signup; never silently create an unattributed
customer or guess the identifier. This requirement applies to versions 1–5.

Continue through identity, tenants, modules, data/API access, hosting and the
version-specific decisions. For LinkedIn campaign setup, follow the workflow
choice below before asking detailed workflow configuration questions. Ask for
local asset paths or attachments in normal
conversation if a text-only input tool cannot receive files. Never ask for a
password, access token, session cookie or private key in chat or a committed file;
ask for the intended secret-manager/environment-variable name instead.

Record answers in `integration-brief.md` using `docs/INTEGRATION-BRIEF-TEMPLATE.md`.
For each applicable item record an answer, an explicitly accepted default, or a
deferred item with its owner and the milestone it blocks. Mark irrelevant items
N/A with a reason. Silence is not an answer. Resolve contradictions and missing
decisions before implementing the affected behavior. A deferred real domain or
logo may use a placeholder only when the recipient explicitly accepts it.

Once the discovery answers are sufficient, summarize the chosen scope and
assumptions, then implement within that scope without asking the same questions
again. Read-only exploration can continue while waiting for answers. If API or
host-repository access is missing, work on a clearly identified mock prototype
only after the recipient agrees to that delivery mode. Do not present a mock
prototype as a completed production integration.

## LinkedIn workflow: standard or custom

Ask the partner this single English question through the prompt/answer interface
unless they have already answered it:

> Would you like to use our standard workflow and get started immediately, or
> configure your own workflow, which often takes 10–15 minutes? You can always
> change your decision later.

Offer `Use the standard workflow` and `Configure my own workflow` as selectable
answers. Keep the timing and the option to change the decision visible in the
question's explanation.

Record the choice in `integration-brief.md`. If they choose the standard, use
the exact template below without asking them to specify or reconfirm its steps,
thresholds, waiting periods or message settings. This takes precedence over
workflow configuration questions in the discovery documents and approach guides.
If they choose a custom workflow, guide them through configuring their own.
Support changing the choice later and always make that option clear to the
partner; do not treat the initial choice as permanent.

The standard starts with Opportunity and Automated Screening (`Workflow`, both
thresholds 70). Match continues after a locked one-second wait to Connect with
an empty connection message; No Match ends after its locked one-second wait.
If Accepted waits one day and sends `Thank you for accepting my connection`,
using `Translate message into opportunities own language` as the system prompt
and `manualApproval: false`. Not Accepted waits 35 days, then deletes the
connection request. Preserve the JSON configuration and graph below.

For every new campaign, instantiate a fresh copy. Use newly allocated campaign
and workflow record IDs where the API assigns or requires them. Generate fresh,
unique IDs for every node and edge; the IDs below are template references only
and must never be reused across campaigns. Build an old-to-new node ID map and
update every edge's `from` and `to` and every `config.icpScreeningId` using that
same map. Preserve all other values, including `version`, types, titles,
positions, labels, configuration and locked screening branches. Validate that
all references resolve within the new workflow before saving it.

```json
{
  "version": 1,
  "nodes": [
    {"id":"opportunity_e6b2k47r","type":"opportunity","title":"Opportunity","x":520,"y":140,"config":{}},
    {"id":"connect_xfugbsaf","type":"connect","title":"Connect","x":520,"y":800,"config":{"connectMessage":"","systemPrompt":"","connectSystemPrompt":""}},
    {"id":"accepted_feig2nw7","type":"if_accepted","title":"If Accepted","x":340,"y":1010,"config":{}},
    {"id":"not_accepted_67jmg9zz","type":"not_accepted","title":"Not Accepted","x":700,"y":1010,"config":{}},
    {"id":"wait_35_days_mw1xo8dv","type":"wait","title":"Wait 35 days","x":700,"y":1220,"config":{"durationValue":35,"durationUnit":"days"}},
    {"id":"screening_dfffbdqj","type":"icp_screening","title":"Automated Screening","x":520,"y":350,"config":{"icpDefinition":"Workflow","matchThreshold":70,"scoreThreshold":70}},
    {"id":"screening_match_uuvumvdf","type":"wait","title":"Match","x":350,"y":560,"config":{"durationValue":1,"durationUnit":"seconds","icpScreeningId":"screening_dfffbdqj","icpBranch":"match","locked":true}},
    {"id":"screening_no_match_uxdnjxv3","type":"wait","title":"No Match","x":690,"y":560,"config":{"durationValue":1,"durationUnit":"seconds","icpScreeningId":"screening_dfffbdqj","icpBranch":"no_match","locked":true}},
    {"id":"wait_1_day_lo8kjwi8","type":"wait","title":"Wait 1 day","x":340,"y":1220,"config":{"durationValue":1,"durationUnit":"days"}},
    {"id":"write_89pis8is","type":"write","title":"Message","x":340,"y":1430,"config":{"template":"Thank you for accepting my connection","systemPrompt":"Translate message into opportunities own language","manualApproval":false}},
    {"id":"delete_connection_request_sdslm9zh","type":"delete_connection_request","title":"Delete Connection Request","x":700,"y":1430,"config":{}}
  ],
  "edges": [
    {"id":"edge_e1s3r01c","from":"opportunity_e6b2k47r","to":"screening_dfffbdqj","label":""},
    {"id":"edge_orhiiegj","from":"screening_dfffbdqj","to":"screening_match_uuvumvdf","label":"Match"},
    {"id":"edge_8qcsbgmr","from":"screening_dfffbdqj","to":"screening_no_match_uxdnjxv3","label":"No Match"},
    {"id":"edge_jz2e9a1u","from":"screening_match_uuvumvdf","to":"connect_xfugbsaf","label":""},
    {"id":"edge_rt7c6k0n","from":"connect_xfugbsaf","to":"accepted_feig2nw7","label":"If Accepted"},
    {"id":"edge_xu9my6p0","from":"connect_xfugbsaf","to":"not_accepted_67jmg9zz","label":"Not Accepted"},
    {"id":"edge_p3sc1y6h","from":"not_accepted_67jmg9zz","to":"wait_35_days_mw1xo8dv","label":""},
    {"id":"edge_3rotn3p1","from":"accepted_feig2nw7","to":"wait_1_day_lo8kjwi8","label":""},
    {"id":"edge_f8ixlm29","from":"wait_1_day_lo8kjwi8","to":"write_89pis8is","label":""},
    {"id":"edge_tez9ogwi","from":"wait_35_days_mw1xo8dv","to":"delete_connection_request_sdslm9zh","label":""}
  ]
}
```

## Implementation context

- Subscription Center is excluded from this partner handoff. Do not restore its
  page, settings link or customer-facing subscription-management UI in any of
  the five versions. Use the partner's agreed account/billing flow where needed.
  Preserve service-side entitlements and account-access checks; some retained
  backend endpoints still use `subscription_center` in their names.
- `app/` contains PHP templates and plain JavaScript/CSS, bundled libraries,
  legacy examples, tests and assets. It is not a React package or complete backend.
- Read `docs/SOURCE-MAP.md`, `docs/DEPENDENCIES.md`,
  `docs/EXTERNAL-REFERENCES.json`, and `docs/PACKAGING-REPORT.md` before implementation.
- Prefer the host platform's existing framework and design system. Decide whether
  to retain PHP or port selected views based on the chosen version and host stack.
- Treat API calls in the snapshot as evidence of existing usage, not a published
  partner API contract. Verify sandbox schemas, permissions, pagination and error
  behavior with the service owner. SSO, webhooks and embedded SDK support are not
  established by this package.
- Centralize brand tokens, asset URLs, application base path, environment-specific
  endpoints and module configuration. The supplied `.env.example` is a design
  template; the legacy code does not automatically read it.
- Keep secrets on the server. Bind tenant identity to authenticated server-side
  context and enforce access on every API operation. Preserve permission and
  subscription behavior until the recipient's replacement is defined.
- Do not run the original PHP pages against production while discovering the
  code. They contain live Coherta URLs, tracking and payment/outreach actions.
  Use synthetic fixtures and a sandbox adapter for preview and verification.
- Preserve bundled license notices. The snapshot itself does not grant new
  third-party asset or service rights.

## Completion

Use the selected guide's acceptance criteria. Verify responsive layouts,
keyboard interaction, empty/loading/error states, chosen branding, base paths,
authentication, tenant isolation, data mappings, partner signup attribution and
permitted actions. Run the acceptance checks in `docs/PARTNER-SIGNUP.md` for all
implemented signup paths. Test
real service integration only in the agreed environment. Report what was tested,
what remains mocked, and any outstanding dependency with its owner. Include local
setup steps and environment-variable names in the recipient's final project.
