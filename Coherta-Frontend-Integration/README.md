# Coherta frontend integration handoff

This ZIP contains a partner adaptation snapshot of the `/app` frontend, including
local uncommitted changes at the original capture, plus integration instructions.
Subscription Center, its dedicated assets/tests and its settings link have been
removed from this handoff because partner customers do not need that UI.
The five Markdown integration guides are included in the ZIP and supplied next
to it so they can also be shared separately.

## Start in Codex

1. Extract the ZIP to its own folder. Open the extracted folder containing this
   file and `AGENTS.md` as your Codex project.
2. If you already have a platform repository, make it available to the task and
   explain where the integration should live. Preserve its existing instructions;
   do not overwrite its `AGENTS.md` when copying this package into that repository.
3. Start a task with this prompt:

   > Read AGENTS.md and start the Coherta integration questionnaire. Ask me which
   > of the five versions to use, then collect the required branding, logo,
   > domain, platform, authentication, Partner ID, direct customer signup flow,
   > data and hosting decisions before building.

`AGENTS.md` is an instruction file read by Codex, not an executable or a script
that runs when a ZIP is opened. Starting the task above triggers the guided
conversation. If you copied this package into an existing repository, explicitly
point Codex to the handoff's `AGENTS.md` in the task prompt.
See the [official OpenAI documentation on AGENTS.md](https://learn.chatgpt.com/docs/agent-configuration/agents-md).

## Choose your approach

| Version | User experience | Connection to your platform | Data ownership and scope |
| --- | --- | --- | --- |
| 1. Stand alone outreach | Separate branded outreach application | No host integration required | Separate accounts and outreach records |
| 2. Slightly connected | Separate application with links and a return path | Agreed lead handoff and status sync; optional verified SSO | Explicit mapping between host leads and outreach records |
| 3. Embedded workflow | Workflow builder or runner inside a host page | Scoped embedded module with host record context | Host owns surrounding experience; outreach engine owns execution |
| 4. Fully embedded | Complete agreed outreach UI inside host navigation | Shared session/context and several embedded screens | Coherta remains the outreach system of record; existing host lead views can remain |
| 5. Fully integrated | Native Lead Monitoring, Workflow, Messages and Statistics throughout host journeys | Shared identities, records, events and reporting | Field-by-field ownership and synchronization across all four modules |

Versions 4 and 5 differ in data and product integration, not just visual styling.
Version 4 brings the agreed outreach experience into the host shell. Version 5
also links lead signals, workflow enrollment, conversations and outcomes to native
host records and reporting. Version numbers describe approaches, not released
software versions. The guides are implementation briefs, not five prebuilt apps.

## Package contents

- `app/`: source pages, scripts, styles, images, fonts, bundled libraries and tests,
  with the Subscription Center exclusion and related compatibility edits applied.
- `AGENTS.md`: version selection and discovery instructions.
- `integration-approaches/`: the five alternative integration briefs.
- `docs/DISCOVERY.md`: required common and conditional follow-up questions.
- `docs/PARTNER-SIGNUP.md`: Partner ID discovery, customer-registration
  attribution requirements and acceptance checks for all five versions.
- `docs/INTEGRATION-BRIEF-TEMPLATE.md`: a place to save answers and deferred items.
- `docs/SOURCE-MAP.md`: where the relevant product code lives.
- `docs/DEPENDENCIES.md`: runtime assumptions and dependencies outside `/app`.
- `docs/EXTERNAL-REFERENCES.json`: static include and external asset references.
- `docs/PACKAGING-REPORT.md`: snapshot counts, redactions, scan scope and limitations.
- `docs/FILE-MANIFEST.json`: SHA-256 and byte size of every delivered file except
  the manifest itself.
- `.env.example`: suggested configuration names for the future integration.

## Runtime status

All five versions require a Partner ID and a defined signup/provisioning flow.
The agent must ensure that customers signing up directly with the partner are
registered against the configured partner, including when no referral link or
browser storage exists. The instructions require sandbox verification of the
saved association before enabling real signup. Adding these instructions does
not change or implement signup behavior in the supplied PHP/JavaScript snapshot.

Subscription Center must stay excluded in all five versions. Account activation
and entitlement checks remain; retained backend API names may contain
`subscription_center`, but the customer-facing page and links are absent. The
separate upgrade page has its own stylesheet so it does not depend on the removed
assets. Partner account/billing integration remains a discovery decision.

This is a frontend source handoff, not a self-contained running application.
The original pages depend on Coherta's API, authentication, server-side includes,
root-level assets and routing rules that are outside `/app` and are not included.
No backend service, database, customer export, credentials or deployment account
is provisioned by this package. The original pages still reference production
services; inspect them before running them and use an agreed sandbox or mock mode.

There is no application-level `package.json` or universal `npm install`/`npm start`
command in this snapshot. Codex should establish build and preview commands for
the recipient's actual stack after discovery. Existing tests are reference
material and may depend on files outside the archive or local runtime setup.

One embedded access token was removed from the distributed source, and a
credential-shaped SDK documentation example was replaced with a placeholder.
See the packaging report for exact paths. Bundled license files are retained;
existing agreements govern use of Coherta and third-party materials.
