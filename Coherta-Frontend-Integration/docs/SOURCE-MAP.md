# Source map

The source is primarily PHP-rendered HTML with inline/plain JavaScript, shared
partials, CSS/SCSS and bundled libraries. No application-level package manager or
frontend build configuration was found under `/app` when this handoff was built.

## Product modules

| Area | Files | Notes for integration |
| --- | --- | --- |
| HTTP client and session | `app/base.php` | `ApiClient` uses PHP cURL, a Coherta API base URL and session tokens; depends on root `pricing.php` |
| Shared shell | `app/partial/header.php`, `sidebar.php`, `topbar.php`, `style.php`, `scripts.php`, `footer.php`, `footer-end.php` | Authentication, navigation, theme and scripts are coupled to standalone routes |
| Authentication | `app/login.php`, `logout.php`, `forgot-password.php`, `change_password.php` | Preserve account/session behavior or replace with the agreed host identity flow |
| Campaigns | `app/campaign/index.php`, `campaigns-dashboard.php`, `campaigns-shared.php` | Campaign entry, overview and shared shell |
| Workflow | `app/campaign/campaigns-workflow-builder.php` | Main campaign workflow builder; includes API interaction and product rules |
| Workflow support | `app/campaign/screening-results.js`, `screening-results.css`, `hubspot-node.js`, `pipedrive-node.js`, `bitrix24-node.js` | Screening and connector-specific UI |
| Messages | `app/campaign/campaigns-messages.php`, `campaigns-messages-endpoint.php` | Conversation UI and server-side endpoint handling |
| Statistics | `app/campaign/campaigns-statistics.php`, `app/interaction-scoreboard.php` | Campaign and interaction views; align metrics with the recipient's definitions |
| Monitoring | `app/stakeholder-monitoring.php`, `app/assets/js/stakeholder-monitoring.js` | Stakeholder monitoring, with a missing root-level credit helper dependency |
| Company activity | `app/company-activity.php`, `app/partial/company-activity.php`, `app/assets/js/company-activity.js`, `app/assets/css/company-activity.css` | Company activity presentation and API calls |
| Leads and contacts | `app/leads.php`, `leads_people.php`, `lead.php`, `lead_people.php`, `leads-outreach.php` | Inspect record models and permissions before mapping host records |
| Sender connections | `app/connect_linkedin.php`, `connect_office365.php`, `connect_whatsapp.php`, `extension-bridge.php` | Include only approved channels/connection flows |
| Account and billing | `app/my-account.php`, `users.php`, `app/partial/billing-access.php` | Subscription Center is excluded; entitlements, users and partner account behavior still need an explicit owner |
| Onboarding | `app/setup-funnel.php`, `setup-funnel-i18n.json`, `sign-up.php`, `welcome.php` | May be replaced by host provisioning; signup source has a documented credential redaction |

Abbreviated paths in a table cell use the preceding directory in that cell.

## Asset and reference directories

- `app/assets/css`, `app/assets/scss`, `app/assets/js`: application and vendor UI.
- `app/assets/images`, `app/assets/fonts`, `app/assets/svg`: visual assets.
- `app/assets/pug`: theme templates; do not assume these generate the active PHP pages.
- `app/dist`: bundled chart library; retained as part of the requested source.
- `app/stripe`: vendored SDK and notices; its presence does not imply that new
  integrations should use Stripe or that current billing is exclusively Stripe.
- `app/old`, `app/gh`, files ending in `2`/`_old`, and
  `app/workflow_builder.html`: alternative/legacy references. Determine active
  routes before reusing them; archive inclusion does not make them production entry points.
- `app/tests`: existing PHP and JavaScript tests and fixtures. Review their
  dependencies and network behavior before running selected tests.

## Where to begin for branding

Start with shared shell/style files, `app/assets/css/style.css`,
`app/assets/js/config.js`, and assets in `app/assets/images/logo/`. Several views
also reference the website's `/assets/img/logo/logo.png`, which is outside this
ZIP. Do not assume one logo replacement or a single CSS variable covers every
screen. Centralize the recipient's theme tokens and inspect each selected module.

## Integration cautions from the source

Subscription Center's route, partial, dedicated JavaScript/CSS and dedicated tests
are omitted from this partner package. The account settings link and obsolete
header branch are removed. The separate upgrade page uses
`app/assets/css/product-upgrade.css`. Shared account-activation APIs and legacy
backend test references retain their existing endpoint/function names; those
names do not indicate an included customer-facing Subscription Center.

Both server-side and browser-side API calls exist. Some views expose session
context to browser scripts. Routing assumes `/app` and extensionless PHP paths.
Moving to another base path, domain or host session needs an explicit adapter and
route audit. Navigation visibility checks alone are not sufficient authorization.
