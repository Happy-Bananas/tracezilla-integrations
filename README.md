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
| `tracezilla-integration-php/` | Headless, programmable PHP integration | Primary customer deployment; Shopify adapter available |
| `tracezilla-woocommerce-php/` | Deployable WooCommerce/PHP console integration | Connection test available; workflows in progress |
| `tracezilla-woocommerce-sandbox/` | Disposable WordPress/WooCommerce development store | Seeded sandbox available |

Planned repositories are created only when working, tested content is ready.
Additional language examples remain available through the
[implementations directory](https://happy-bananas.github.io/tracezilla-integrations-docs/implementations.html)
without being checked out as umbrella submodules.
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
git add tracezilla-integration-php
git commit -m "Update headless integration revision"
git push
```

## Common local commands

Documentation site:

```bash
cd tracezilla-integrations-docs
docker compose up -d
```

Open <http://localhost:4000/>.

Headless PHP integration:

```bash
cd tracezilla-integration-php
docker compose up --build
```

When the ready message appears, open a second terminal and list the available
commands:

```bash
docker compose exec integration php bin/tracezilla-integration help
```

WooCommerce sandbox:

```bash
cd tracezilla-woocommerce-sandbox
docker compose up -d
```

Open <http://localhost:8080/wp-admin>.

## Planning documents

- [`SDD/01-architecture-overview.md`](SDD/01-architecture-overview.md) defines
  the target ecosystem and repository boundaries.
- [`SDD/02-operational-strategy.md`](SDD/02-operational-strategy.md) records
  delivery phases, verified progress, and the next execution sequence.
