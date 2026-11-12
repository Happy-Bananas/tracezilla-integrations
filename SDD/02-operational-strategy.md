# BifrostConnect — Operational Strategy

Status: Active

Updated: 2026-11-10

This document contains only decisions and remaining work. Completed work is in
Git, the pull requests of each repository, and the changelogs.

## Decisions

- Keep the legacy `tracezilla-shopify-connector` frozen and use it only as a
  reference source.
- One PHP template per external service: `tracezilla-shopify-php` and
  `tracezilla-woocommerce-php`. Other languages and Make.com / AWS Lambda are
  deferred.
- Code that every template needs lives in one Composer package,
  `happy-bananas/bifrost-core` (namespace `Bifrost\Core`): the tracezilla client
  with OAuth login and automatic renewal, retries after temporary tracezilla
  failures, the run lock, `WriteMode`, and the seed with the standard test
  dataset. Templates require it with a caret constraint and find it through its
  GitHub repository (`"no-api": true`). A fix there reaches every project with
  `composer update happy-bananas/bifrost-core`.
- New projects are created with `create-project` from a tagged release of the
  template, never with `git clone`; `TEMPLATE_VERSION` records the release.
- Customer business rules are PHP scenarios in `custom/` (DataReader →
  BusinessRules → planned changes), tested with PHPUnit.
- Every command that changes data requires `--dry-run` or `--execute`.
  Every command that must not overlap takes the run lock itself.
- Cron runs the integration. Monitoring is a heartbeat call after a
  successful run; the integration has no monitoring code of its own.
- Keep configuration minimal. Prefer safe defaults and the dry run over extra
  settings; the seed needs no settings and is left out of production projects
  with `--without-seed`.
- Do not create speculative repositories; create one when its first tested
  workflow is ready.

## Current state

| Part | State |
|---|---|
| bifrost-core | `v0.7.0`: tracezilla client with automatic login, run lock, seed |
| WooCommerce template | `v0.2.0`: connection check, login, scenarios (`confirm-credentials`, `compare-catalogs`, `missing-in-woocommerce`, `create-tracezilla-skus`), seed |
| Shopify template | `v0.3.0`: all commands ported from the legacy connector, including the order import with its retry queue, plus scenarios, seed, and a connection check of both services |
| Manual | Published; Getting Started, setup per platform, deployment with cron and monitoring, Update Your Integration |

## 1. More WooCommerce scenario templates

Add, one at a time, each with tests, a sandbox check and a manual page:

- Synchronize inventory: tracezilla stock → WooCommerce `stock_quantity`.
- Import orders: WooCommerce order → tracezilla sales order, with an `ext_ref`
  and duplicate check.
- Report orders, and complete shipped orders.

## 2. Bring the Shopify template to the same shape

- Use the scenario template mechanism (`--template`, `templates/scenarios/`,
  `DataReader.php`) as in WooCommerce, and give its manual a Scenarios menu.
- Decide which built-in commands become templates; keep existing command
  names working.
- Rewrite the remaining files in the coding style of `AGENTS.md`, worst first
  (`SynchronizeInventory`, `SynchronizeInventoryResult`,
  `TracezillaInventoryService`, `FileRetryRepository`,
  `TracezillaSalesOrderService`).

## 3. Releases

- Release a template when its changelog has user-facing changes: tag `main`,
  then raise its `version` in `create-project`.
- Move shared code into bifrost-core when a second template needs it.

## 4. Order-closing and email scenario

Define a common event-driven scenario in which closing an order triggers a
tracezilla-related email. Before implementation, establish which system owns
the event, which tracezilla webhook is available, who sends the email, and the
retry, audit and duplicate-delivery rules.

## Quality rules for every repository

- One Docker setup (`docker compose run --rm php ...`) and locked dependencies.
- Automated tests and CI on every pull request.
- Safe example configuration with no committed credentials.
- Manual builds and internal-link checks stay blocking.
