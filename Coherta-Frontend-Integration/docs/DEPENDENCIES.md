# Dependencies and runtime boundaries

## What the archive does not contain

The archive intentionally follows the requested `/app` scope. It does not copy
the website root, `/api`, `/includes`, `/classes`, `/lib`, root `/assets`, the root
`.htaccess`, Git history or local environment files from elsewhere in the source
repository. Required external files must be replaced with recipient-owned
equivalents or supplied through an agreed separate handoff.

Known active-page dependencies outside `/app` include:

| Reference | Examples of callers | Integration action |
| --- | --- | --- |
| `/pricing.php` | `app/base.php`, `app/sign-up.php`, `app/free_trial.php` | Define billing/plan ownership and provide an appropriate pricing adapter |
| `/includes/stakeholder_monitoring_credits.php` | `app/stakeholder-monitoring.php` | Resolve monitoring entitlements/credits before enabling the module |
| `/includes/low-word-count-content.php` | `app/free_trial.php` | Replace or omit the website-content helper if that page is retained |
| `/assets/img/logo/logo.png` and other root assets | Shared shell and onboarding pages | Replace with supplied partner assets and configurable URLs |
| Root URL rewrite rules | Extensionless `/app/login`, `/app/campaign`, etc. | Implement routing in the recipient's server/framework for the selected base path |

`EXTERNAL-REFERENCES.json` lists static include expressions and root-level asset
references discovered in the source. It is an aid for review, not a complete
runtime dependency graph: dynamic paths, PHP evaluation and CSS imports require
inspection. Legacy files and tests can reference additional external files.

## Runtime clues

The active PHP uses typed properties and PHP 8 functions such as
`str_starts_with`; a compatible PHP 8 runtime is needed if retaining it. PHP cURL,
JSON handling, sessions and a PHP-capable web server are used. This package does
not establish a tested PHP version matrix. A static file server cannot execute
the application, and a plain PHP development server will not by itself reproduce
the repository's extensionless route configuration or missing includes.

The frontend includes jQuery/Bootstrap-style assets, chart libraries and other
bundled scripts. Several pages load CDN assets/fonts. Review chosen pages and
bundle or allow their dependencies according to the recipient's environment.
Preserve original third-party license notices and respect existing usage rights.

## Service integration

Subscription Center is intentionally excluded from the partner UI. Shared account
activation/entitlement code still calls a backend endpoint whose name contains
`subscription_center`; removing that service call would also alter account-access
behavior, which is outside this exclusion. Legacy backend billing tests can refer
to the service and its original return URLs. They remain backend reference tests,
not evidence that the removed frontend route is available. Do not restore that
route or its settings link when adapting this package for partner customers.

The source references `https://api.coherta.com` from both PHP and JavaScript.
Authentication, users/companies, campaign execution, lead data, monitoring,
messages and reporting rely on service behavior that is not shipped here.
Some calls involve external sender, connector, analytics or payment services.
The service implementations and their partner contracts are not inferred from
frontend calls alone.

For the chosen version, obtain or agree:

- Sandbox origin, authentication/session model, tenant scoping and permissions.
- Required endpoints, schemas, pagination, errors, timeouts and rate limits.
- Supported sender/connector flows, entitlement rules and lifecycle behavior.
- Actual SSO/embedding/event/webhook capabilities, including any backend work.
- Synthetic fixtures and a plan for testing without real outreach or payments.

Use `.env.example` as a proposed naming convention. No environment loader or
server adapter has been added to the original frontend. Replacing hard-coded
URLs and introducing configuration is work for the recipient's integration.

## Preview and validation strategy

After discovery, build a mock adapter and selected UI preview in the chosen stack
or configure a PHP sandbox with the required replacement includes and routing.
Do not start the original pages against production merely to inspect their UI.
Implement real service integration only against the agreed environment.

The handoff was checked for archive integrity, file coverage and documented
redactions. No end-to-end application run or backend compatibility validation
was performed as part of packaging. Existing tests were preserved, not certified
as runnable in the standalone ZIP. Use relevant tests after resolving dependencies,
and add checks for the recipient's actual integration boundaries.
