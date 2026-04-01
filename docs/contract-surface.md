# Contract Surface

This document defines the consumer-facing contract for this library.
For `1.x`, items listed as contract are stable unless a major version is released.

## Scope

- Package: `traviscarden/behat-table-comparison`
- Primary namespace: `TravisCarden\\BehatTableComparison`
- Public API center: `TableEqualityAssertion` and `UnequalTablesException`

## Public API Contract

The following are part of the public contract for `1.x`:

- Public class names and namespaces:
  - `TravisCarden\\BehatTableComparison\\TableEqualityAssertion`
  - `TravisCarden\\BehatTableComparison\\UnequalTablesException`
- Constructor and public methods on `TableEqualityAssertion`.
- Fluent setter behavior on `TableEqualityAssertion` (setters return `$this`).
- Public constants on `TableEqualityAssertion` representing default labels.

Any change to the above is at least `behavior-changing` and may be `breaking`.

## Exception Contract

- Content/body inequality failures throw `UnequalTablesException`.
- Header expectation failures currently throw `LogicException`.

This split is currently part of observed behavior and must be treated as contract unless intentionally changed and documented as `breaking`.

## Diagnostics Contract

For failing assertions, the diagnostics format is consumer-facing contract:

- Section semantics:
  - missing rows
  - unexpected rows
  - duplicate rows
  - row-order mismatch
  - header mismatch
  - expected/actual order subheadings
- Section labels and ordering semantics as documented in `docs/README.md`.
- Meaning of duplicate-row count annotations (for example, `(appears N time/times, expected M)`).

Notes:

- Whitespace and table alignment may vary with upstream formatter behavior and should be treated as presentation detail unless explicitly documented otherwise.
- Label customization through getter/setter pairs is contract.

## Input and Comparison Semantics Contract

- The assertion compares Behat `TableNode` inputs.
- `expectHeader(...)` controls header validation behavior.
- `ignoreRowOrder()` and `respectRowOrder()` control order-sensitive diagnostics.
- `assert()` returns `true` on success and throws on failure.

## Non-Contract Internals

The following are implementation details and may change in minor releases:

- Protected/internal helper method structure and naming.
- Internal algorithms used for sorting/counting/matching rows.
- Internal data structures used while building diagnostics.

Changes in this area should still preserve public behavior and diagnostics contract.

## Change Classification Guide

Use these labels when reporting changes:

- `none`: no contract impact.
- `additive`: new optional behavior without altering existing behavior.
- `behavior-changing`: existing behavior changes, but migration can be straightforward.
- `breaking`: source or runtime compatibility break.

## Release Discipline

When contract behavior changes:

- Update `docs/README.md` contract-facing sections.
- Update PHPUnit coverage in `tests/unit/`.
- Update Behat integration coverage in `tests/behat/features/integration-tests.feature` when user-facing diagnostics change.
- Call out change classification and migration notes in release notes.

## Contract Review Checklist (Release PR)

Use this checklist for release candidates and release PRs.

- Contract classification is set (`none`, `additive`, `behavior-changing`, or `breaking`) with one-line rationale.
- Public API audit completed:
  - public classes/methods/signatures/constants unchanged, or changes are documented.
  - fluent setter methods still return `$this`.
- Exception behavior audit completed:
  - content/body mismatch still throws `UnequalTablesException`.
  - header mismatch behavior is unchanged or intentionally migrated with notes.
- Diagnostics audit completed:
  - section labels/semantics/order reviewed against README and integration expectations.
  - duplicate count annotation semantics preserved.
- Test symmetry verified:
  - unit tests cover changed behavior.
  - Behat integration scenarios cover user-visible diagnostics where applicable.
- Documentation sync completed:
  - `docs/README.md` matches current behavior and labels.
  - this file reflects any updated guarantees.
- Runtime support policy confirmed:
  - PHP and dependency constraints in `composer.json` match release notes.
  - CI matrix validates all declared supported versions.
- Upgrade communication prepared (for behavior-changing or breaking updates):
  - migration notes included.
  - examples of before/after behavior included where useful.
