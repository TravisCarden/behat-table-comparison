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
- Public constants on `UnequalTablesException` representing error codes.

Any change to the above is at least `behavior-changing` and may be `breaking`.

## Exception Contract

All assertion failures from `assert()` throw `UnequalTablesException`. The integer error code,
available via `getCode()`, identifies the category of failure:

| Constant                                     | Value | When thrown                                                                                                     |
|----------------------------------------------|-------|-----------------------------------------------------------------------------------------------------------------|
| `UnequalTablesException::HEADER_MISMATCH`    | `1`   | The header row does not match the expected header.                                                              |
| `UnequalTablesException::CONTENT_MISMATCH`   | `2`   | Rows are missing, unexpected, or duplicated.                                                                    |
| `UnequalTablesException::ROW_ORDER_MISMATCH` | `3`   | The same rows are present but in a different order.                                                             |
| `UnequalTablesException::STRUCTURAL_ERROR`   | `4`   | A structural failure occurred processing a table node; the original exception is available via `getPrevious()`. |

The constant names, integer values, and the single-exception-type guarantee are stable contract in `1.x`.
Consumers should use the constants (not bare integers) for clarity and forward-compatibility.

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
- `expectHeader(...)` controls header validation behavior: when called, both the expected and actual tables' first rows are validated against the specified header, then stripped from body comparison.
- `ignoreRowOrder()` and `respectRowOrder()` control order-sensitive diagnostics.
- `assert()` returns void on success and throws on failure.

## Header Validation Semantics

### Terminology

Three distinct concepts are involved in header validation:

- **Specified header**: The header specification passed to `expectHeader(...)`. This declares what columns should exist.
- **Expected table**: The table provided as the first argument to `TableEqualityAssertion` (the test specification, typically from Gherkin). Its first row is validated and stripped when `expectHeader(...)` is used.
- **Actual table**: The table provided as the second argument to `TableEqualityAssertion` (the application's real output). It is compared as-is with no header stripping.

### Validation Process (Asymmetrical Design)

When `expectHeader(...)` is called:

1. The **first row of the expected table** is validated against the specified header. If it does not match, `HEADER_MISMATCH` is thrown.
2. The **first row of the expected table is excluded from body comparison** (treated as a header, not a data row).
3. The **actual table is compared as-is** — it is not validated against the header and no rows are stripped.

This asymmetrical design accommodates the common pattern where test specifications include headers (e.g., from Gherkin TableNode format) while application-generated output may not. The validation ensures the test specification has the correct structure before comparing bodies.

### Example

**Test specification (from Gherkin, includes header):**
```gherkin
Then the following users should exist
  | name  | role  |
  | Alice | admin |
  | Bob   | user  |
```

**Application output (constructed data, no header):**
```php
$actual = new TableNode([
    ['Alice', 'admin'],  // Note: no header row
    ['Bob', 'user'],
]);
```

**PHP code:**
```php
$expected = new TableNode([
    ['name', 'role'],    // Header from Gherkin
    ['Alice', 'admin'],  // Body rows
    ['Bob', 'user'],
]);

(new TableEqualityAssertion($expected, $actual))
    ->expectHeader(['name', 'role'])
    ->assert();  // ✓ Passes
    // - Expected table's header validated and stripped
    // - Actual table compared as-is
    // - Bodies match
```

### When Header Validation Fails

If the expected table's first row does not match the specified header:

```php
$expected = new TableNode([
    ['username', 'permission'],  // ✗ Does not match ['name', 'role']
    ['Alice', 'admin'],
]);


(new TableEqualityAssertion($expected, $actual))
    ->expectHeader(['name', 'role'])
    ->assert();  // Throws HEADER_MISMATCH
```

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
  - all assertion failures throw `UnequalTablesException` (no low-level exceptions leak).
  - error code constants (`HEADER_MISMATCH`, `CONTENT_MISMATCH`, `ROW_ORDER_MISMATCH`, `STRUCTURAL_ERROR`) are assigned correctly.
  - `STRUCTURAL_ERROR` wraps original exception via `getPrevious()`.
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
