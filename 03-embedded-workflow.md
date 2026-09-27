# 3. Embedded workflow into your own platform

Place a focused workflow experience inside an existing host page. For example,
a user opens a lead list, selects records, opens the Workflow tab, edits a
sequence and enrolls the selected records without leaving the platform.

## Product boundary

- Embed the workflow builder, workflow selection and the agreed enrollment/run
  controls. Keep the host's navigation, lead views and account management.
- The outreach engine owns execution, scheduling and run state unless a separate
  backend change is explicitly included.
- Inbox, broad reporting and Lead Monitoring are not automatically embedded in
  version 3. They may be accessed through an agreed external link.
- Preserve workflow validation and permissions when simplifying the UI.

## Architecture choices

Choose after inspecting the host and available service contracts:

| Method | Suitable situation | Work to resolve |
| --- | --- | --- |
| Scoped iframe | A separately hosted workflow route can be made embeddable | Session bootstrap, allowed parent origins, frame policy, sizing and focus |
| Separately deployed frontend module | Teams want independent releases in a shared shell | Framework/runtime agreement, CSS isolation and module lifecycle |
| Native host component | The host needs tight design-system and form integration | Port selected behavior and maintain an explicit service adapter |

These are proposed implementation options; no drop-in embedding SDK is supplied.
Browser frame/cookie behavior and the actual deployment headers must be tested.
For iframe communication, validate both origin and source, use versioned typed
messages and never use wildcard origins for sensitive data. Keep credentials out
of URL parameters and cross-window messages.

## Source starting points

- `app/campaign/campaigns-workflow-builder.php`: builder UI and workflow behavior.
- `app/campaign/campaigns-shared.php`: shared campaign shell to untangle.
- `app/campaign/screening-results.js` and `.css`: screening presentation.
- `app/campaign/hubspot-node.js`, `pipedrive-node.js`, `bitrix24-node.js`:
  connector-specific node examples to include only when needed.
- `app/tests/workflow-screening-test.cjs` and
  `app/tests/workflow-participants-polling-test.cjs`: existing behavior references.

`app/workflow_builder.html` is an additional reference; inspect the active campaign
builder before choosing which implementation to port.

## Additional questions

1. Where should Workflow appear in the host, and how does it receive lead/list
   context? Can a user create workflows, only enroll leads, or both?
2. Which embedding method fits the host stack and deployment? What exact parent
   origins, module origin and base path will be used?
3. How is the signed-in user/tenant established inside the module? What verified
   session mechanism exists, including renewal and logout?
4. Which workflow nodes, channels, templates, validation rules and run controls
   should be available? Who can publish, activate, pause and edit a running flow?
5. What events does the host need: saved, dirty/unsaved, enrollment completed,
   run status changed, navigation request or session expired?
6. What should closing the panel or switching tenants do with unsaved edits?
   What should users see when the embedded service is unavailable?

## Partner ID and direct signup

Ask for the Coherta Partner ID (PartnerId) for each environment and where the
customer account is created before using the embedded workflow. Follow the ZIP's
`docs/PARTNER-SIGNUP.md` for host registration, embedded onboarding, invitations
or supported SSO provisioning. Resolve the partner from trusted server context,
including across embed/bootstrap redirects. Distinguish provisioning a new
customer from attaching the workflow to an existing customer's authorized tenant.

## Implementation sequence

1. Complete discovery and document the host/module boundary and event contract.
2. Extract the builder from the standalone page shell, retaining validation and
   introducing scoped styling and configurable branding.
3. Build the agreed embed/native mount with synthetic lead context and service
   responses. Implement mount/unmount, resize, focus and unsaved-state behavior.
4. Connect identity, record authorization, save/load and enrollment to the sandbox.
5. Add verified host events and recovery for expiry, failure and tenant switching.

## Acceptance

- A host user can open, edit, validate, save and reopen a workflow with the correct
  tenant and selected lead context.
- Enrollment is permission-checked and duplicate submissions do not enroll twice.
- Host navigation and module styling coexist without a second full application
  sidebar, broken scroll areas or lost keyboard focus.
- Unsaved edits, session expiry, loading and unavailable-service states are clear.
- Unauthorized parent origins/records are rejected; switching tenants never
  reuses the prior tenant's workflow, cached data or credentials.
- Save/enrollment events update the host accurately, including after remount.
- New customers created by the host/module provisioning flow pass
  `docs/PARTNER-SIGNUP.md`; their saved Partner ID matches the intended partner
  and no embedding parameter can reassign an existing customer.

## Tradeoff and extension path

Users get a native workflow journey while only one module must be integrated.
Embedding requires careful session and layout handling. Add inbox and reporting
screens under the same host shell to progress toward version 4.

Subscription Center is excluded from this partner package and must not be
restored for partner customers. Preserve entitlements and use the agreed partner
account/billing flow where needed.

## Codex starting prompt

> Use version 3, Embedded workflow. Read the package AGENTS.md and ask the common
> questions, including colors, logo and domain, then resolve host stack, embedding
> method, identity, Partner ID, signup/provisioning flow, lead context, workflow
> permissions and host events. Follow docs/PARTNER-SIGNUP.md to verify customer
> attribution before enabling real signup. Build the
> scoped workflow integration from the active campaign builder after saving the brief.
