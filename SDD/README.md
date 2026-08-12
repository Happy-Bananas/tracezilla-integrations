# tracezilla Integrations — Software Design Documents

Status: Draft for review
Owners: Happy Bananas
Last updated: 2026-08-11

This directory contains the long-term design and delivery strategy for the
tracezilla integration ecosystem.

- [Architecture Overview](./01-architecture-overview.md) defines the product
  goal, repository boundaries, documentation structure, and design principles.
- [Operational Strategy](./02-operational-strategy.md) divides the transition
  into independently implementable, testable, and publishable steps.

The architecture overview should change slowly. The operational strategy is a
living plan: record completed checkpoints and revise later phases as experience
is gained.

## Document status

These files are intentionally excluded by the repository `.gitignore`. They
are local planning documents until the owners decide where architectural
records should be version-controlled.

No new GitHub repository is created merely because it appears in these
documents. Repository creation is an explicit owner checkpoint in the
operational strategy.
