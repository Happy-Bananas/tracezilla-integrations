# Tracezilla Integrations Workspace

This repository is the reproducible workspace for the Happy Bananas Tracezilla
integration ecosystem. Each product remains an independent Git repository and
is registered here as a submodule. `SDD/` contains the shared architecture and
delivery strategy.

## Recreate the workspace

Clone the umbrella repository and all child repositories in one command:

```bash
git clone --recurse-submodules \
  https://github.com/Happy-Bananas/tracezilla-integrations.git
cd tracezilla-integrations
```

For an existing clone, initialize or update the recorded child revisions with:

```bash
git pull
git submodule update --init --recursive
```

## Workspace map

| Directory | Purpose | Status |
|---|---|---|
| `SDD/` | Program-level architecture and operational strategy | Active, local planning material |
| `tracezilla-integrations-docs/` | Service-first Jekyll documentation hub | Published and maintained |
| `tracezilla-integration-workbench/` | Local consultant tool based on Laravel | Active; connection checks and SKU-import pilot work |
| `tracezilla-shopify-php/` | Framework-neutral Shopify/PHP templates | Compare Catalogs maintained |
| `tracezilla-shopify-typescript/` | Framework-neutral Shopify/TypeScript templates | Compare Catalogs maintained |
| `tracezilla-shopify-python/` | Framework-neutral Shopify/Python templates | Compare Catalogs maintained |
| `tracezilla-shopify-ruby/` | Framework-neutral Shopify/Ruby templates | Compare Catalogs maintained |
| `tracezilla-shopify-dotnet/` | Framework-neutral Shopify/.NET templates | Compare Catalogs maintained |
| `tracezilla-shopify-java/` | Framework-neutral Shopify/Java templates | Compare Catalogs maintained |
| `tracezilla-woocommerce-php/` | Standalone WooCommerce/PHP examples and local sandbox | Sandbox and connection test available |

Planned repositories are created only when working, tested content is ready.
The frozen legacy source remains separately at:

```text
../tracezilla-shopify-connector/
```

## Published resources

- Documentation: <https://happy-bananas.github.io/tracezilla-integrations-docs/>
- GitHub organization: <https://github.com/Happy-Bananas>

## Working agreements

- Commit product code inside the relevant child repository first. Then commit
  the updated submodule revision in this umbrella repository.
- Do not modify the legacy repository; copy selected material from it when
  needed.
- Never copy or commit `.env` files, credentials, generated dependencies, or
  build output.
- Update the SDD progress snapshot when a milestone or owner decision changes.
- Update the public documentation whenever a user-facing workflow changes.
- Commit and verify each child repository independently.

When a child repository has been pushed, record its new revision here:

```bash
git add tracezilla-integration-workbench
git commit -m "Update workbench revision"
git push
```

## Common local commands

Documentation site:

```bash
cd tracezilla-integrations-docs
docker compose up -d
```

Open <http://localhost:4000/>.

Workbench application (after its documented setup):

```bash
cd tracezilla-integration-workbench
docker compose up -d db app
```

Open <http://localhost:8000/>. The workbench's bundled legacy documentation
service is not started because the dedicated documentation repository owns
port 4000.

WooCommerce sandbox:

```bash
cd tracezilla-woocommerce-php
docker compose --profile sandbox up -d
```

Open <http://localhost:8080/wp-admin>.

## Planning documents

- [`SDD/01-architecture-overview.md`](SDD/01-architecture-overview.md) defines
  the target ecosystem and repository boundaries.
- [`SDD/02-operational-strategy.md`](SDD/02-operational-strategy.md) records
  delivery phases, verified progress, and the next execution sequence.
