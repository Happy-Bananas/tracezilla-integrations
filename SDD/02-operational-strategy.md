# BifrostConnect — Operational Strategy

Status: Active

Updated: 2026-08-13

This document contains only remaining work. Completed implementation history is
available in Git and the architecture overview.

## Decisions

- Keep the legacy `tracezilla-shopify-connector` frozen and use it only as a
  reference source.
- Keep documentation service-first and implementation repositories
  framework-neutral.
- Treat BifrostConnect as the primary customer deployment.
- Keep customer business rules programmable through generated PHP scenarios.
- Do not change Shopify Setup until its owner review and screenshots are ready.
- Defer Make.com and AWS Lambda without further work.
- Do not create speculative repositories; create one when its first tested
  workflow is ready.

## Current foundation

Compare Catalogs is the cross-platform read-only “Hello World” workflow. It is
implemented with Docker, tests, CI, and documentation for PHP, TypeScript,
Python, Ruby, C#/.NET, and Java.

## 1. Port the remaining Shopify commands

Port each useful legacy command into BifrostConnect first. Port
it to another maintained platform only where there is a concrete audience.
Preserve the common architecture: query, client, service, mapper, workflow,
output, and thin entry point.

| Workflow | Source command | Status |
|---|---|---|
| Compare catalogs | `PullCatalogFromShopify` | Complete |
| Push catalog to Shopify | `PushCatalogToShopify` | Not started |
| Create tracezilla SKUs from Shopify | `TracezillaSkusFromShopifyCommand` | Implemented on all maintained platforms; sandbox write pending |
| List Shopify locations | `CheckLocationsInShopify` | Implemented on all maintained platforms; PHP sandbox verified |
| Synchronize inventory | `UpdateInventoryInShopify` | Implemented and dry-run verified on all maintained platforms; sandbox write pending |
| Import individual orders | `PullOrdersFromShopifyIndividual` | Not started |
| Import collected orders | `PullOrdersFromShopifyCollected` | Not started |

Implement one workflow across the selected platforms before starting the next.
For every workflow:

- Define the platform-neutral behavior and mapping first.
- Default writes to dry run and require explicit confirmation.
- Use bounded test data and report created, updated, skipped, invalid, and
  failed records.
- Add automated tests, a live sandbox verification, and canonical
  documentation.

## 2. Finish the headless starting point

Keep the clean-clone path small enough for a consultant to understand without
learning framework internals:

- Clone and configure `.env`.
- Generate a platform-specific scenario.
- Edit the service requests, PHP business rules, and test.
- Preview and execute the scenario through the console.
- Schedule the same command with cron.

## 3. Finish Shopify command documentation

- Organize navigation as Shopify → platform → command.
- Keep each platform page as its installation and architecture guide.
- Add a self-contained child page for every command implemented on that
  platform; repetition between platforms is acceptable.
- Keep the same section order and equivalent guidance depth for a workflow on
  every platform; only commands, paths, and language-specific details differ.
- Link only implementations that pass clean-clone tests and sandbox checks.
- Add owner-reviewed Shopify Setup screenshots later without rewriting its
  instructions.
- Record the destination of remaining useful legacy documentation as it is
  migrated.

## 4. Start WooCommerce

Research WooCommerce authentication, products/variations, inventory, orders,
pagination, webhooks, and test-store setup. Then define its first workflow.

Prefer reusing Compare Catalogs as the WooCommerce onboarding workflow so its
behavior can be compared with Shopify. Select one implementation platform for
the pilot; create further repositories only after the pilot is verified.

Expected repository naming:

```text
tracezilla-woocommerce-<platform>
```

## 5. Add an order-closing and email scenario

Define a common event-driven scenario in which closing an order triggers a
tracezilla-related email. Before implementation, establish:

- Which system owns the order-closing event.
- Which tracezilla webhook event and payload are available.
- Whether tracezilla sends the email or the integration calls an email service.
- Required recipient, template, retry, audit, and duplicate-delivery rules.
- What constitutes successful completion and safe replay.

The first implementation must verify webhook signatures if available, be
idempotent, redact personal data from logs, and include a documented local test
procedure. Do not combine this discovery with the WooCommerce pilot unless the
same event contract genuinely applies.

## 6. Maintain quality

Every maintained repository must retain:

- Reproducible Docker setup and locked dependencies.
- Automated tests and CI.
- Safe example configuration with no committed credentials.
- Clear read/write boundaries and supported workflow coverage.
- Links to canonical documentation.

Documentation builds and internal-link checks remain blocking. External-link
checks may run periodically and must not make releases depend on third-party
availability.

## Next execution sequence

1. Evaluate **Create tracezilla SKUs from Shopify** with one bounded PHP sandbox
   write, then repeat the dry run to verify idempotency.
2. Verify the generated credential scenario from a clean clone.
3. Port locations and inventory workflows.
4. Port individual and collected order workflows.
5. Research tracezilla webhooks and specify the order-closing/email contract.
6. Establish the WooCommerce test store and Compare Catalogs pilot.

## Completion criteria

This stage is complete when useful workflows have canonical, tested definitions
in BifrostConnect; selected implementations run from clean
clones; generated scenarios provide the normal consultant editing surface; and
documentation accurately reports coverage.
