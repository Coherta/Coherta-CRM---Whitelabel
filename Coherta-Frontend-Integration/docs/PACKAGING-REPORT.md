# Packaging report

- Snapshot time (UTC): 2026-09-24T22:41:28.288598+00:00
- Source: current working-tree `app/`, including uncommitted and untracked files.
- Original app files: 3,357.
- Original app bytes: 71,205,590.
- Original app files represented in archive: 3,348; omitted: 9 (Subscription Center scope).
- Byte-for-byte unchanged original app files: 3,342; 6 modified; 9 omitted.
- Added: integration instructions, guides, configuration example and reports.
- No source application files were edited by the packaging process.

## Sanitization

- `app/sign-up.php`: Removed embedded LinkedIn access token; empty string retained.
- `app/stripe/README.md`: Replaced credential-shaped SDK documentation example with a placeholder.

The empty signup token is intentional. Implement the approved per-user sender
connection flow; do not restore a shared credential to frontend source.

## Verification and limits

- Initial capture included all original app paths; the later partner exclusion
  below removes 9 and makes documented compatibility edits in addition to sanitization. The copied source uses the current working tree, not Git HEAD.
- Checked sanitized signup PHP syntax using `php -l` without execution.
- Scanned payload bytes for the removed credential and common provider-secret,
  JWT, cloud-key and private-key patterns. No remaining matches were accepted.
- During preparation, reviewed credential-assignment/context candidates in text
  files. Remaining candidates included UI labels, editor syntax tokens, vendor
  constants and a synthetic test fixture. Pattern scans cannot guarantee the
  absence of every secret, private dataset or issue in binary content.
- No root environment files, database exports or repository history were copied.
  Original theme/demo assets, sample files and vendor notices within app are retained.
- Validated ZIP CRCs, unique/safe paths, guide duplication and every manifest hash.
- Initial packaging ran no application/backend integration tests. The later
  exclusion update ran isolated login and upgrade regression checks. No production
  API requests, message sends, payments or deployment were performed.

## Scope

This is source material for adaptation. The five versions are written approaches,
not implemented variants. External services, missing root dependencies and new
partner-specific contracts remain integration work. See `DEPENDENCIES.md`.

## Partner signup documentation update

- Updated (UTC): 2026-09-26T13:41:40.440345+00:00
- Added mandatory Partner ID and direct signup/provisioning discovery for versions 1–5.
- Added `PARTNER-SIGNUP.md` with source evidence, trusted attribution requirements,
  existing-account handling and sandbox acceptance checks.
- Updated agent instructions, discovery, brief template, configuration example,
  README and all five approach guides. No actual Partner ID or credential was supplied.
- The original audited `app/` snapshot is byte-for-byte unchanged by this update,
  including the previously sanitized signup source. Production files were not edited.
- Signup attribution is a requirement for the recipient's future implementation;
  this documentation update does not implement or deploy signup changes.
- Rebuilt file manifest and verified ZIP CRCs, payload hashes and companion guides.

## Subscription Center exclusion

- Updated (UTC): 2026-09-26T13:57:10.100436+00:00
- Removed the customer page, partial, dedicated JavaScript/CSS, four dedicated
  tests and their dedicated payment harness (9 files listed below).
- Removed the account settings card and center-only header branch. The remaining
  account-action fallback points to account settings instead of the removed page.
- Preserved only upgrade-used CSS rules in `app/assets/css/product-upgrade.css`
  and updated the separate upgrade page to use that stylesheet.
- Updated shared login test destinations to an included protected route.
- Backend account-access/activation service calls are retained; their existing
  `subscription_center` API names and legacy backend tests are not frontend pages.
- Verified modified PHP syntax, isolated login/upgrade regression checks, no
  runtime links to the removed page/assets, complete ZIP integrity and hashes.
- All changes are confined to the distributable and handoff documentation;
  production application files are unchanged. Previous partner signup requirements
  and credential redactions are retained.

Removed files:

- `app/assets/css/subscription-center.css`
- `app/assets/js/subscription-center.js`
- `app/partial/subscription-center.php`
- `app/subscription_center.php`
- `app/tests/fixtures/subscription-center-payment-harness.php`
- `app/tests/subscription-center-reconciliation-test.php`
- `app/tests/subscription-center-revolut-test.php`
- `app/tests/subscription-center-test.php`
- `app/tests/subscription-center-ui-test.cjs`
