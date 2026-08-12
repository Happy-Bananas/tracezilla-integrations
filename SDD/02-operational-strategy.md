# tracezilla Integrations — Operational Strategy

Status: Active
Owners: Happy Bananas
Last updated: 2026-08-12

## 1. Delivery rules

This strategy builds the architecture defined in the Architecture Overview as
a new collection of focused repositories. The current repository is retained
unchanged as a legacy reference source. Proven documentation and code may be
copied from it, but new work is implemented and maintained only in the new
repositories.

Every implementation step must:

1. Have one clear outcome.
2. Preserve unrelated working behavior.
3. Include proportionate automated or manual verification.
4. Be reviewable as a focused commit or pull request.
5. Be pushed before dependent work begins.
6. Record its completion and any changed assumptions in this strategy.

No repository is created, renamed, archived, or made public without an owner
checkpoint. The legacy repository is not cleaned, renamed, reorganized, or
used as a destination for fixes. No old documentation URL is assumed to be
replaceable until the new documentation has a verified canonical page.

Clearly labelled placeholders are permitted in the documentation hub. They
exist to show the intended ecosystem and coverage, must say `Planned`, and
must not contain commands or links that imply working code exists.

## 2. Baseline

Legacy source repository:

```text
Happy-Bananas/tracezilla-shopify-connector
```

Verified baseline on 2026-08-11:

- Laravel test suite: 84 tests passed, 309 assertions.
- Jekyll documentation build: successful, with Sass deprecation warnings.
- Laravel application: HTTP 200 on port 8000 after dependency and environment
  initialization.
- Documentation: HTTP 200 on port 4000 after Bundler initialization.

Known baseline issues:

- Fresh `docker compose up` does not install application or documentation
  dependencies.
- The repository mixes documentation, Laravel, TypeScript, and Python.
- Naming and branding describe only Shopify and Laravel.
- Documentation navigation is platform-first rather than service-first.
- Customer-specific mapping identifiers are embedded in example classes.
- Suspicious empty files and duplicate bootstrap files require validation.
- Some controllers contain unfinished or obsolete code.
- Jekyll emits Sass deprecation warnings.

These issues explain why the repository is frozen. They are not an active
cleanup backlog. When content is copied, only the selected content is repaired
and tested in its new repository.

## 3. Target repositories

Repositories planned for the initial transition:

| Repository | Purpose | Creation checkpoint |
|---|---|---|
| `tracezilla-integrations-docs` | Published service-first documentation | Phase 2 |
| `tracezilla-shopify-php` | Framework-neutral PHP templates for Shopify workflows | Created; implementation begins in Phase 4 |
| `tracezilla-shopify-typescript` | Focused TypeScript Shopify examples | Phase 5 |
| `tracezilla-shopify-python` | Focused Python Shopify examples | Phase 6 |
| `tracezilla-integration-workbench` | Local consultant workbench | Phase 8 |

Future WooCommerce repositories are outside the initial migration and are
created only when their first tested workflow is ready.

### Current progress snapshot

| Area | Status | Verified result |
|---|---|---|
| Phase 1 — Freeze legacy repository | Complete | Legacy repository remains unchanged and is used only as a read-only source |
| Step 2.0 — Local workspace | Complete | Parent README maps the independent repositories, SDD, working agreements, and common local commands |
| Step 2.1 — Documentation scaffold | Complete | Independent Docker/Jekyll site returns HTTP 200 on `localhost:4000` |
| Step 2.2 — Automated checks | Complete | GitHub Actions blocks deployment when the Jekyll build or internal-link validation fails |
| Step 2.3 — GitHub Pages | Complete | Public site returns HTTP 200 |
| Step 3.1 — Navigation skeleton | Complete | Service-first navigation and planned placeholders are rendered |
| Step 3.2 — tracezilla fundamentals | Complete | Account, authentication, validation, entities, pagination, mapping, results, and safety are documented |
| Step 3.3 — Shopify content | In progress | Setup and connection validation are migrated; workflow groups remain |
| Step 3.4 — Migration inventory | Not started | Legacy-to-canonical destination map remains to be written |
| Phase 4 — Shopify PHP | In progress | Framework-neutral Compare Catalogs CLI is implemented, tested, and verified read-only against configured APIs |
| Phase 5 — Shopify TypeScript | Repository ready | Public repository exists and is registered in the umbrella workspace; implementation has not started |
| Phases 6–7, 9–10 | Not started | No Python or WooCommerce implementation repositories have been created |
| Phase 8 | In progress | Legacy Laravel baseline runs with `.env` credentials; connection tools are retained and the first preview/confirmed-write SKU workflow is implemented |

Current delivery focus: complete the Compare Catalogs publication checkpoint,
then consolidate the workbench SKU-import pilot and create the documentation
migration inventory.

## 4. Phase 1 — Freeze the legacy repository

Status: Decision complete; no implementation work is planned.

The existing `tracezilla-shopify-connector` remains available as historical
source material. Its working code, tests, screenshots, and documentation may
be inspected and selectively copied. Its current shortcomings are not repaired
in place.

Rules:

- Do not rename or reorganize the legacy repository.
- Do not delete embedded TypeScript, Python, Laravel, or documentation content.
- Do not add migration notices, redirects, or maintenance commits to it unless
  the owners explicitly revise this decision.
- Never copy generated directories, secrets, accidental files, or known stale
  configuration.
- Treat copied material as untrusted until it passes the destination
  repository's tests and acceptance checks.

This decision deliberately skips the former stabilization phase and makes
Phase 2 the first active delivery phase.

## 5. Phase 2 — Establish the documentation hub

Owner checkpoint: create
`Happy-Bananas/tracezilla-integrations-docs` after approving its name,
visibility, and GitHub Pages URL.

### Step 2.0 — Create the local multi-repository workspace

Status: Complete.

Outcome: local planning material and independent repositories have one
reproducible home without combining their histories.

Create:

```text
tracezilla-integrations/
├── README.md
├── SDD/
├── .gitmodules
├── tracezilla-integrations-docs/
├── tracezilla-integration-workbench/
├── tracezilla-shopify-php/
└── tracezilla-shopify-typescript/
```

The parent is an umbrella Git repository. Each child implementation or product
directory remains its own repository and is registered as a submodule. The
parent README describes setup and maintenance, and `SDD/` holds the
program-level design documents.

Verification:

- A recursive clone restores every registered child at its recorded revision.
- The SDD is readable from the parent workspace.
- Child repositories retain independent remotes and histories.

Completed evidence:

- Public umbrella repository: `Happy-Bananas/tracezilla-integrations`.
- `README.md` maps active child repositories and planning documents.
- `.gitmodules` records documentation, workbench, Laravel, and TypeScript
  repositories.
- The README documents recursive cloning, updates, port ownership, and the
  independent-repository workflow.

Checkpoint: local workspace review; only child repositories are pushed.

### Step 2.1 — Scaffold the independent documentation repository

Status: Complete.

Outcome: an independently runnable and publishable documentation site exists.

Scope:

- Initialize the documentation repository.
- Retain Jekyll and Just the Docs unless a separate decision changes the
  technology.
- Add a dedicated Dockerfile and Compose configuration.
- Add concise local authoring instructions.
- Add ignores for generated site content, caches, gems, and secrets.
- Copy only useful Jekyll configuration, content, and assets from the legacy
  repository; do not preserve its mixed repository layout.

Verification:

- A clean clone starts the documentation container with one documented setup
  flow.
- The local site returns HTTP 200.
- The static build succeeds.

Git checkpoint: initial documentation-infrastructure commit and push.

Completed evidence:

- Repository: `Happy-Bananas/tracezilla-integrations-docs`.
- Docker Compose serves the site directly at `http://localhost:4000/`.
- The production base path remains `/tracezilla-integrations-docs/`.
- The pinned Ruby/Jekyll build succeeds locally.

### Step 2.2 — Add automated documentation checks

Status: Complete.

Outcome: broken builds and links are detected before publication.

Scope:

- Add a GitHub Actions build workflow.
- Add internal-link validation.
- Treat build failures and broken internal links as blocking.
- Record Sass deprecations separately if they cannot yet be eliminated.

Verification: workflow passes on the default branch and fails against a
deliberately broken test branch or local fixture.

Git checkpoint: documentation CI pull request.

Completed evidence:

- Pushes to `main` run a production Jekyll build.
- The Ruby version and gem dependencies are pinned.
- HTMLProofer 5.2.0 checks generated pages, internal links, anchors, images,
  and scripts before the deployment step can run.
- External requests are disabled so third-party outages do not block a release;
  localhost HTTP links remain valid for local workbench instructions.
- The complete production site passes locally.
- A temporary fixture linking to a missing page failed locally with exit code 1
  and identified the missing target.
- GitHub Actions run `31590578911` passed both the internal-link check and Pages
  deployment on `main`.

### Step 2.3 — Add GitHub Pages deployment

Status: Complete.

Outcome: the documentation hub publishes automatically from its default
branch.

Scope:

- Configure the canonical base URL.
- Deploy the generated static site through GitHub Actions.
- Document preview and production publication behavior.

Verification:

- Published home page returns HTTP 200.
- Assets load beneath the configured base URL.
- A known internal link resolves on the published site.

Git checkpoint: deployment pull request and owner approval before changing
public links.

Completed evidence:

- The GitHub Actions deployment completes successfully.
- The public site returns HTTP 200 at
  `https://happy-bananas.github.io/tracezilla-integrations-docs/`.

## 6. Phase 3 — Migrate to service-first documentation

### Step 3.1 — Create the navigation skeleton

Status: Complete.

Outcome: navigation reflects service → platform → examples.

Create landing pages, navigation relationships, and clearly labelled planned
placeholders initially:

- Getting Started.
- tracezilla Fundamentals.
- Shopify overview, setup, workflows, platforms, and troubleshooting.
- Integration Workbench.
- API Reference.

Include WooCommerce and planned platform/example entries when they help explain
the intended structure. Every unavailable item must visibly say `Planned`,
must not present executable instructions, and must link to the coverage or
roadmap page rather than to a nonexistent repository.

Verification:

- Jekyll builds.
- Navigation has no orphan or duplicate pages.
- Both need-first and platform-first paths reach Compare Catalogs.
- Planned pages are visually distinguishable from working examples.

Git checkpoint: navigation-only pull request.

Completed evidence:

- The rendered navigation is service-first.
- Shopify contains Setup, Workflows, Laravel, TypeScript, Python, Make.com,
  and Troubleshooting entries.
- WooCommerce and Integration Workbench placeholders are visibly labelled
  `Planned`.
- Shopify Setup has working third-level navigation.

### Step 3.2 — Migrate shared tracezilla material

Status: Complete.

Outcome: authentication, entities, pagination, mapping, results, and safety
have stable canonical pages.

Verification:

- No page requires knowledge of Laravel to understand the tracezilla concept.
- Existing relevant pages link to the canonical material.
- Internal-link check passes.

Git checkpoint: shared-foundation pull request.

Completed:

- Account/team selection and test-environment boundary.
- Team slug, API-token, secret handling, and conventional configuration.
- Platform-neutral connection and workflow-permission validation.
- SKUs/products, locations/inventory, and partners/sales-order concepts.
- tracezilla pagination and origin-safety guidance.
- Shared data-mapping and structured synchronization-result vocabulary.
- Integration safety and production responsibility boundary.
- Links to the current public tracezilla API documentation where endpoint
  schemas must be verified.

### Step 3.3 — Migrate Shopify setup and workflows

Status: In progress.

Outcome: existing Shopify knowledge is preserved under the Shopify service.

Migrate in small workflow groups:

1. Setup and connection validation.
2. Catalog comparison and catalog writes.
3. Locations and inventory.
4. Individual and collected orders.
5. Troubleshooting.

For each group:

- Separate service behavior from implementation instructions.
- Add Laravel, TypeScript, Python, or Make.com links only where implemented.
- Mark read-only and write behavior prominently.
- Preserve customer-specific decision points.

Verification per group:

- Documentation build and link check pass.
- Commands match the source repository.
- At least one maintainer follows the rendered procedure.

Git checkpoint: one pull request per workflow group.

Completed:

- Shopify developer-access requirements.
- Dev-store creation using current Dev Dashboard terminology.
- Test products, SKUs, locations, and inventory preparation.
- App versions, minimum scopes, installation, and credential protection.
- Platform-neutral connection validation and safe result requirements.
- Verification against current official Shopify developer documentation.

Remaining workflow groups:

1. Catalog comparison and catalog writes.
2. Locations and inventory synchronization.
3. Individual and collected orders.
4. Shopify troubleshooting consolidation.

### Step 3.4 — Record legacy-source relationships

Status: Not started.

Outcome: maintainers can trace migrated material without changing the legacy
repository or confusing readers about which site is canonical.

Scope:

- Inventory useful pages from the legacy GitHub Pages site.
- Record the destination page for copied or rewritten material.
- Identify the new documentation hub as canonical on the new site.
- Keep old URLs in the migration inventory, but do not modify the frozen
  repository to add redirects.

Verification:

- Every migrated workflow records its legacy source where useful.
- New pages contain no links back to legacy pages for content already made
  canonical.

Git checkpoint: migration inventory committed in the documentation repository.

## 7. Phase 4 — Create the framework-neutral PHP repository

Status: In progress; first workflow implemented.

Owner checkpoint complete: `Happy-Bananas/tracezilla-shopify-php` exists
as a new public repository and is cloned beneath the local workspace. The
legacy `tracezilla-shopify-connector` is not renamed or modified.

### Step 4.1 — Define the PHP repository contract

Outcome: the README describes ready-to-use, framework-neutral PHP templates
for Shopify workflows, not the documentation hub or Laravel workbench.

Scope:

- List implemented workflows and safety status.
- Link to canonical documentation.
- Explain installation, configuration, tests, and adaptation points.
- Remove claims that the repository is a universal connector.

Verification: a fresh-clone PHP onboarding test succeeds without installing an
application framework.

Git checkpoint: repository-contract pull request.

Completed evidence:

- The README defines a framework-neutral PHP boundary and explains each layer.
- Docker, Composer, `.env.example`, and a reproducible test command are present.
- Laravel remains confined to the optional integration workbench.

### Step 4.2 — Populate the new PHP repository

Outcome: selected proven behavior is implemented in a clean, framework-neutral
PHP repository.

Scope:

- Reuse verified API behavior without copying Laravel application boundaries.
- Exclude legacy Jekyll, Laravel UI and Artisan code, TypeScript, Python,
  accidental files, and generated content.
- Update Composer package metadata, headings, repository links, and assets.
- Implement a fresh-clone Docker flow in the new repository.
- Preserve copyright and license information.

Verification:

- Clone the new repository using its own URL.
- Docker startup and all PHP tests pass without Laravel.
- Documentation hub links resolve to the new repository.

Git checkpoint: initial working repository push.

Completed for the first workflow:

- Shopify GraphQL query, authenticated client, pagination service, and response
  mapper are separated.
- The tracezilla client, paginated SKU service, and response mapper are
  separated.
- Both APIs map to a shared `CatalogItem`; `CompareCatalogs` has no HTTP or
  framework dependency.
- Table and JSON output are available with a default display limit of 10.
- Five unit tests with 13 assertions pass on PHP 8.3.
- A live read-only run completed using the configured test accounts.
- GitHub Actions run `31599724717` installed the locked dependencies and passed
  Composer validation and the complete test suite from a clean checkout.

Remaining:

- Add later workflows only after their contracts and safety behavior are
  independently reviewed.

### Step 4.3 — Publish documentation links

Outcome: the documentation hub points to the verified new PHP repository.

Precondition: the new repository passes clean-clone setup and tests.

Verification:

- README and documentation links resolve in both directions.
- PHP coverage is changed from `Planned` to `Maintained` only for verified
  workflows.

Git checkpoint: focused documentation pull request. The legacy repository is
unchanged.

Completed for Compare Catalogs:

- The canonical workflow page documents the SKU matching rule, read-only
  boundary, result categories, and PHP command.
- Shopify coverage links to the maintained PHP implementation.

## 8. Phase 5 — Extract TypeScript

Owner checkpoint: create `tracezilla-shopify-typescript`.

### Step 5.1 — Establish independent history and runtime

Outcome: the TypeScript catalog comparison runs without PHP, Composer, Ruby,
or Python.

Scope:

- Extract the existing TypeScript example, preserving relevant history where
  practical.
- Move it to repository root conventions.
- Add independent Docker, environment example, README, license, and tests.
- Add repository metadata and maintenance status.

Verification:

- Clean clone runs tests in Docker.
- Clean clone runs the documented comparison command with sandbox credentials.
- No Laravel or Jekyll dependency remains.

Git checkpoint: initial repository push.

### Step 5.2 — Link the canonical TypeScript implementation

Outcome: the new repository becomes the maintained TypeScript implementation.

Precondition: new repository is public and verified.

Verification:

- Documentation hub links to the new repository.
- New repository and documentation links resolve in both directions.
- TypeScript coverage changes from `Planned` to `Maintained` only after its
  clean-clone checks pass.

Git checkpoint: documentation link pull request. The embedded legacy copy
remains unchanged and is not maintained.

## 9. Phase 6 — Extract Python

Owner checkpoint: create `tracezilla-shopify-python`.

Repeat the TypeScript extraction pattern:

1. Preserve relevant history where practical.
2. Establish an independent Docker runtime and tests.
3. Verify the read-only comparison from a clean clone.
4. Link it from the documentation hub.
5. Mark the embedded legacy copy as historical in the migration inventory,
   without changing the legacy repository.

Git checkpoint: initial repository push followed by a documentation link pull
request.

## 10. Phase 7 — Decide the Make.com artifact boundary

Outcome: Make.com is published in the smallest maintainable form.

Decision criteria:

- If examples are instructions only, keep them in the documentation hub.
- If maintainable blueprint exports, fixtures, or automated validation assets
  exist, create `tracezilla-shopify-make`.
- Do not create a repository solely for a short documentation page.

Verification: a consultant can reproduce the verified recipe without access to
the PHP repository or Laravel workbench.

Git checkpoint: decision record plus implementation in the selected location.

## 11. Phase 8 — Create the integration workbench

Owner checkpoint complete: `tracezilla-integration-workbench` has been created.
The initial browser credential refactor was rolled back in favor of the proven
legacy baseline using `.env` credentials. Workbench capabilities are now added
incrementally on top of that baseline.

### Step 8.1 — Scaffold a clean workbench

Status: Complete as a baseline copy, with a documented exception to the
original clean-scaffold approach.

Outcome: the proven legacy Laravel application runs independently in the new
workbench repository. Existing commands and tests were retained deliberately
to avoid another premature refactor.

Verification:

- Clean-clone Docker startup returns HTTP 200.
- Automated tests pass.
- README documents `.env` configuration and reload behavior.

Completed evidence:

- Application and Tracezilla/Shopify pages return HTTP 200 locally.
- Legacy baseline test suite passed with 84 tests and 309 assertions before
  workbench-specific features were added.
- Local `.env` remains ignored by Git.

Git checkpoint: initial repository push.

### Step 8.2 — Implement credential lifecycle

Status: Deferred by owner decision.

Outcome: consultants can enter Shopify and tracezilla credentials in the UI.

Scope:

- Provider-specific credential forms and validation.
- Encrypted, expiring session storage.
- Redaction in views, logs, validation, and exceptions.
- Forget-credentials action.
- No permanent storage by default.
- No secret values in URLs or browser caches.

Verification:

- Feature tests prove secrets are not rendered or logged.
- Session expiration and credential removal are tested.
- Invalid credentials produce safe actionable messages.

Git checkpoint: security-focused pull request.

Current decision: credentials remain in the local `.env` file. Revisit this
step only if a concrete consultant workflow justifies browser-managed secrets.

### Step 8.3 — Add read-only connection and inspection tools

Status: Partially complete through retained legacy behavior.

Deliver independently:

1. Shopify connection and API-version validation.
2. tracezilla connection validation.
3. Shopify product and location inspection.
4. tracezilla SKU and warehouse inspection.
5. Read-only catalog comparison.

Each tool requires tests with mocked APIs, pagination coverage where relevant,
and safe error rendering.

Completed:

- Shopify connection and API-version validation.
- Tracezilla connection validation.
- Shopify product inspection.
- Tracezilla SKU inspection.

Remaining:

- Shopify location inspection in the browser.
- Tracezilla warehouse inspection in the browser.
- Read-only catalog-comparison page.

Git checkpoint: one pull request per tool or tightly related group.

### Step 8.4 — Add tracezilla test-data preparation

Status: First narrow workflow implemented.

Outcome: consultants can populate a controlled tracezilla environment after
reviewing a preview.

Requirements:

- Preview before execution.
- Explicit confirmation.
- Small default limit.
- Idempotency or duplicate detection.
- Structured per-item results.
- Redacted request and error details.

Begin with one narrow operation, such as creating missing test SKUs. Expand
only after the first write workflow is reviewed against real consultant use.

Completed evidence:

- Dedicated page is linked from the Tracezilla connection page.
- Both Shopify and Tracezilla configuration are checked before controls are
  enabled.
- Dry run is enabled by default with a limit of 10 variants.
- Disabling dry run requires a centered OK/Cancel confirmation dialog when the
  run button is pressed.
- Server-side confirmation is required before API clients are resolved.
- Existing Tracezilla SKUs, missing Shopify SKU codes, and duplicate Shopify
  SKU codes are not created again.
- Structured summary and per-item results are rendered in the browser.
- Latest verified suite: 89 tests passed with 329 assertions.

Remaining before expanding write capabilities:

- Review the demonstration unit, weight, and conversion mapping against a real
  consultant use case.
- Perform and record a controlled real-API dry run and limited execution.

Git checkpoint: one pull request per write capability.

### Step 8.5 — Add service extension points

Outcome: WooCommerce credentials and inspection can be added without coupling
them to Shopify controllers or configuration.

Avoid a general plugin framework until the second service demonstrates the
needed interface. Extract only proven shared boundaries such as credential
lifecycle, connection-result presentation, and redaction.

Verification: architecture tests or dependency rules prevent Shopify-specific
types from leaking into shared workbench concerns.

Git checkpoint: refactoring pull request driven by the first WooCommerce tool.

## 12. Phase 9 — Add WooCommerce deliberately

### Step 9.1 — Research and define the first workflow

Outcome: WooCommerce scope is based on an actual consultant scenario.

Recommended first workflow: read-only catalog comparison, because it exercises
authentication, pagination, product/variation identity, SKU handling, and
tracezilla reads without changing customer data.

Document before implementation:

- Supported WooCommerce/API versions.
- Authentication and hosting assumptions.
- Product versus variation identity.
- Pagination and rate-limit behavior.
- Empty and duplicate SKU rules.
- Expected comparison output.

Git checkpoint: documentation workflow pull request.

### Step 9.2 — Select the first implementation platform

Choose based on a real maintainer or consultant need. Do not automatically
implement every existing Shopify platform.

Owner checkpoint: create the selected
`tracezilla-woocommerce-<platform>` repository only after the workflow and
acceptance criteria are approved.

### Step 9.3 — Publish the first implementation

Verification:

- Independent clean-clone build and tests pass.
- Sandbox read-only execution is verified.
- Documentation links both directions.
- WooCommerce appears in public navigation only when useful content is live.

Git checkpoint: implementation repository release and documentation pull
request.

## 13. Phase 10 — Ongoing quality and governance

Apply these checks to every maintained repository:

- Automated tests and formatting/linting appropriate to the platform.
- Container build or equivalent reproducible setup validation.
- Dependency and API-version review schedule.
- Secret scanning and safe example environment files.
- Explicit workflow coverage and maintenance status.
- Working links to canonical documentation.
- Release notes for behavior or mapping changes.

Apply these checks to the documentation hub:

- Static build and internal-link validation.
- Periodic external-link validation.
- Planned placeholders are labelled consistently and contain no fake commands
  or nonexistent repository links.
- Commands tested against their canonical repositories.
- Coverage table reconciled with repository status.

## 14. Suggested first execution sequence

Completed sequence:

- [x] Approve the ecosystem direction and freeze the legacy repository.
- [x] Create the `tracezilla-integrations/` umbrella repository, move the SDD
  into it, and register active child repositories as submodules.
- [x] Create and clone `tracezilla-integrations-docs`.
- [x] Scaffold and test the independent Jekyll Docker environment.
- [x] Add the service-first navigation skeleton and labelled placeholders.
- [x] Add a GitHub Actions production build and GitHub Pages deployment.
- [x] Verify both local and public sites return HTTP 200.
- [x] Migrate Shopify setup and connection-validation content.

Next execution sequence:

1. Manually verify the documented workbench SKU-import pilot, including its
   demonstration mapping and write-safety boundary.
2. Create the legacy-to-canonical documentation destination map.
3. Define and populate the framework-neutral Shopify PHP repository using the
   selected maintained behavior from the legacy implementation.
4. Migrate the Shopify catalog comparison workflow as the first complete
   workflow/implementation navigation pilot.
5. Review the rendered site before migrating inventory and order workflows or
   creating implementation repositories.

## 15. Definition of migration complete

The initial migration is complete when:

- The service-first documentation hub is live and validated.
- Legacy documentation has a recorded destination map; the frozen legacy
  repository remains unchanged.
- A new framework-neutral Shopify PHP repository runs independently.
- TypeScript and Python examples run from independent repositories.
- Legacy embedded copies are no longer treated as maintained implementations.
- The workbench validates configured credentials and provides a reviewed,
  preview-first test-data workflow; browser-managed credentials are optional
  unless the owner decision changes.
- All repositories have clean-clone setup and automated checks.
- The documentation accurately reports implemented workflow coverage.

WooCommerce delivery is a subsequent product increment, not a prerequisite for
completing the initial repository migration.
