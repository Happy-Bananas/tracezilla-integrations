# tracezilla Integrations — Architecture Overview

Status: Draft for review
Owners: Happy Bananas
Last updated: 2026-08-11

## 1. Purpose

The tracezilla integration ecosystem provides practical examples, templates,
and supporting tools for consultants who integrate external commerce services
with the tracezilla API.

The material must cover common integration scenarios while remaining explicit
about customer-specific decisions. It is a starting point for a consultant,
not a universal production connector and not a substitute for understanding a
customer's product, inventory, order, accounting, and operational rules.

Shopify is the first supported external service. WooCommerce is the expected
next service. The architecture must allow additional services and
implementation platforms without turning every repository into a monolith.

## 2. Audience

Primary audience:

- Integration consultants and developers implementing customer integrations.

Secondary audience:

- Technically capable operations staff evaluating an integration.
- Maintainers creating and testing new examples.

Consultants primarily read the published documentation and download only the
implementation repository relevant to their service and platform.

## 3. Product model

The ecosystem has two product types: the documentation hub and headless
integration implementations.

### 3.1 Documentation hub

The documentation hub explains supported services, common workflows, shared
tracezilla concepts, and available implementations. It is service-first and
published as a static website.

Its local authoring and validation environment runs in its own container. The
container is not the production runtime: GitHub Pages receives a generated
static site through an automated build and deployment workflow.

### 3.2 Headless integration implementations

Each implementation repository combines one external service with one
implementation platform. A consultant downloads only the relevant code and
dependencies.

The primary deployment is a standalone headless application. It communicates
directly with the external commerce service and tracezilla, runs manually or
under a scheduler, and contains customer-specific business rules. It does not
require a separate UI or custom client application.

Examples include:

- `tracezilla-integration-php`
- `tracezilla-shopify-typescript`
- `tracezilla-shopify-python`
- `tracezilla-shopify-ruby`
- `tracezilla-shopify-dotnet`
- `tracezilla-shopify-java`
- `tracezilla-woocommerce-php`

The workspace-owned `tracezilla-woocommerce-sandbox` directory is a disposable
WordPress development environment, not an integration implementation or
independent product repository.

Repositories are created when a maintained implementation exists, not to fill
an aspirational matrix.

## 4. Repository architecture

The initial target under the Happy Bananas GitHub organization is:

```text
Happy-Bananas/
├── tracezilla-integrations
├── tracezilla-integrations-docs
├── tracezilla-integration-php
├── tracezilla-shopify-typescript
├── tracezilla-shopify-python
├── tracezilla-shopify-ruby
├── tracezilla-shopify-dotnet
├── tracezilla-shopify-java
└── tracezilla-woocommerce-php
```

`tracezilla-integrations` is the umbrella workspace repository. It owns the
program-level README and SDD and records each active product repository as a
Git submodule. This allows maintainers to recreate a known-compatible local
workspace without combining product histories or release lifecycles.

Possible future repositories are created only when justified by working
content:

```text
tracezilla-shopify-make
tracezilla-woocommerce-typescript
```

### 4.1 Naming convention

Implementation repository names use:

```text
tracezilla-<external-service>-<implementation-platform>
```

Shared products use purpose-based names:

```text
tracezilla-integrations-docs
tracezilla-integration-php
```

Names use lowercase words separated by hyphens. Official product spelling is
used in prose, including `WooCommerce`, `TypeScript`, and `tracezilla`.

### 4.2 Repository boundaries

The documentation repository owns:

- Service and workflow documentation.
- Shared tracezilla integration concepts.
- The service-first navigation model.
- Links to maintained implementation repositories.
- Documentation build, link checking, and GitHub Pages deployment.

An implementation repository owns:

- Runnable source code for one service/platform combination.
- Focused tests for its implemented workflows.
- Installation, configuration, execution, and adaptation instructions.
- Platform-specific mapping points and operational limitations.

An implementation repository does not own the canonical documentation for
other languages or other external services.

## 5. Documentation information architecture

The primary navigation is external-service-first. The implementation platform
is the next layer for runnable examples.

```text
Home
├── Getting Started
├── tracezilla Fundamentals
│   ├── Authentication
│   ├── SKUs and Products
│   ├── Locations and Inventory
│   ├── Sales Orders
│   ├── Pagination and Rate Limits
│   └── Integration Safety
├── Shopify
│   ├── Overview
│   ├── Setup
│   ├── Workflows
│   │   ├── Compare Catalogs
│   │   ├── Create tracezilla SKUs
│   │   ├── Synchronize Inventory
│   │   └── Import Orders
│   ├── Laravel
│   │   ├── Getting Started
│   │   ├── Examples
│   │   │   ├── Compare Catalogs
│   │   │   ├── Create tracezilla SKUs
│   │   │   ├── Synchronize Inventory
│   │   │   └── Import Orders
│   │   └── Reference
│   ├── TypeScript
│   │   ├── Getting Started
│   │   ├── Examples
│   │   │   └── Compare Catalogs
│   │   └── Reference
│   ├── Python
│   │   ├── Getting Started
│   │   ├── Examples
│   │   │   └── Compare Catalogs
│   │   └── Reference
│   ├── Make.com
│   │   ├── Getting Started
│   │   ├── Examples
│   │   │   └── Compare Catalogs
│   │   └── Reference
│   └── Troubleshooting
├── WooCommerce
│   ├── Overview
│   ├── Setup
│   ├── Workflows
│   ├── <Implementation Platform>
│   │   ├── Getting Started
│   │   ├── Examples
│   │   └── Reference
│   └── Troubleshooting
└── API Reference
```

Empty platforms and unfinished workflows are not shown in the public
navigation.

### 5.1 Workflow pages versus implementation pages

A service workflow page defines:

- The business purpose and data-flow direction.
- Source of truth and customer decisions.
- API sequence and common terminology.
- Identity, mapping, pagination, and idempotency rules.
- Read/write consequences, dry-run expectations, and recovery considerations.
- Links to every completed implementation.

A platform example page defines:

- The relevant implementation repository and version.
- Required runtime and credentials.
- Copyable installation and execution commands.
- Relevant source files and customization points.
- Platform-specific validation, limitations, and troubleshooting.
- A link back to the service workflow.

This supports both discovery paths:

```text
Need-first:     Shopify → Workflows → Compare Catalogs → TypeScript
Platform-first: Shopify → TypeScript → Examples → Compare Catalogs
```

### 5.2 Coverage reporting

Documentation must describe actual support without implying that every
platform implements every workflow. A coverage table should use explicit
statuses such as:

- Maintained implementation.
- Verified recipe.
- Planned, not published.
- Not available.

Only maintained implementations and verified recipes receive public example
pages.

## 6. Common workflow catalog

The initial cross-service workflow vocabulary is:

- Validate external-service credentials.
- Validate tracezilla credentials.
- Discover and map locations.
- Read and compare catalogs.
- Create missing tracezilla SKUs.
- Create or update external-service products.
- Synchronize inventory.
- Import individual orders.
- Aggregate or import collected orders.

This vocabulary does not require identical API behavior across Shopify and
WooCommerce. Each service page documents its actual capabilities and
constraints.

## 7. Implementation design principles

- Prefer explicit, runnable examples over a speculative universal framework.
- Keep service-specific names where they make an example easier to understand.
- Extract shared libraries only after repeated implementations demonstrate a
  stable boundary.
- Start with read-only operations or dry runs.
- Require an explicit execution option and conservative limits for writes.
- Make customer-specific mappings visible and configurable.
- Test pagination, duplicates, empty identifiers, partial failures, and
  idempotent retries where relevant.
- Keep credentials out of source control, output, logs, fixtures, and images.
- Keep each repository independently buildable and testable.

## 8. Deployment model

This is the default deployment and should cover most customer requirements:

```text
Shopify or WooCommerce ↔ headless integration ↔ tracezilla
```

The headless integration owns credentials, customer-specific PHP rules,
workflow execution, safety controls, structured results, idempotency, and
operational logs. Console commands, cron, webhooks, or a scheduler may invoke
the same workflow layer.

## 9. Current repository disposition

The current `tracezilla-shopify-connector` repository contains a tested
Laravel Shopify implementation, general documentation, and smaller TypeScript
and Python examples.

Its PHP behavior is a reference source for `tracezilla-integration-php`, but the
new implementation is framework-neutral rather than a copy of the Laravel
application. General documentation moves to the documentation hub. TypeScript
and Python examples move to their respective focused repositories. The Laravel
workbench has been removed from the active workspace after useful behavior was
migrated to the headless implementation.

Migration must be staged. Existing URLs remain available until redirects or
replacement pages are verified.

## 10. Quality attributes

### Usability

- A new consultant can choose a service, workflow, and implementation without
  understanding the repository layout.
- Commands are copyable and expected results are visible.
- The first runnable workflow is read-only.

### Maintainability

- Shared concepts have one canonical documentation location.
- Each repository has a narrow responsibility and independent checks.
- Navigation contains no empty promises.

### Safety

- Secrets are not committed or exposed.
- Writes are clearly distinguished from reads.
- Dry runs, limits, confirmations, and structured results are standard.

### Portability

- Each code repository has a containerized setup where practical.
- A developer downloads only the relevant implementation stack.
- Fresh-clone instructions are tested on Linux as well as macOS-compatible
  Docker environments.

## 11. Non-goals

- A universal no-configuration connector for every customer.
- Identical workflow coverage across every service and language.
- A shared runtime dependency between implementation repositories.
- Requiring a web UI for customer deployments.
- Abstracting service-specific APIs before stable repeated patterns exist.

## 12. Governance and publishing

- Repositories live in the `Happy-Bananas` GitHub organization.
- Maintainers create repositories at explicit operational checkpoints.
- Public documentation links only to verified repositories and commands.
- Each repository defines its supported workflows and maintenance status.
- Breaking documentation moves include redirects or a documented transition.
- Each completed operational step is reviewed, tested, committed, and pushed
  before the next dependent step begins.

## 13. Success criteria

The architecture succeeds when:

- Documentation is visibly service-first.
- Platform examples are nested below their external service.
- A customer can deploy the headless integration directly.
- Customer-specific business rules are programmable in PHP.
- Laravel, TypeScript, and Python users can download focused repositories.
- The current Shopify coverage remains usable throughout migration.
- A WooCommerce implementation can be added without restructuring existing
  Shopify repositories or navigation.
