---
name: library-change
description: "Implement or modify behat-table-comparison library behavior safely. Use when asked to add/fix assertion behavior, diagnostics, labels, or tests/docs for this package."
---

## Use This Skill

Use for concrete implementation work in this package, including:

- changes to `TableEqualityAssertion` or `UnequalTablesException`
- assertion message/label behavior updates
- PHPUnit and Behat coverage updates
- README usage/output documentation alignment
- contract-surface updates in `docs/contract-surface.md`

## Role

You are a maintainer for a small PHP library with stable consumer-facing diagnostics.
Prefer minimal, explicit behavior changes and preserve backward compatibility by default.

## Repository Constraints

- PHP `^8.3` is the supported range; write to the **floor** of that range. Do not use language features or functions introduced after PHP 8.3 (for example, features from PHP 8.4+).
- Keep fluent API setters fluent (`return $this`).
- Follow existing PSR-2 formatting and naming style in touched files.
- Do not edit `vendor/`.
- Ensure every non-automated file ends with a trailing linebreak (for example, generated lockfiles like `composer.lock` are excluded).
- When adding any new file or directory that is not part of the production library, tests, documentation, or examples, add it to `.gitattributes` with `export-ignore`.

## Workflow

1. Identify whether change touches public contract:
   - public methods/signatures
   - exception types
   - diagnostic labels or message sections
   - anything documented in `docs/contract-surface.md`
2. Implement the smallest code change in `src/BehatTableComparison/`.
3. Update coverage:
   - unit tests in `tests/unit/BehatTableComparison/`
   - integration behavior in `tests/behat/features/integration-tests.feature` when user-facing output changes
4. Update docs:
   - `docs/contract-surface.md` when any contract guarantee changes
   - `README.md` when API usage or output text changes
5. Run relevant validation commands.

## Validation Commands

Prefer this command after non-trivial edits to validate across all supported PHP versions:

- `composer docker:check-fast-all`

Use local (single-version) checks for rapid iteration:

- `composer check:test` (runs PHPUnit + Behat)
- `composer check:static` (runs coding standards and Composer metadata checks)
- `composer check:fast` (runs `check:test` + `check:static`; no dependency audit)

Use targeted checks only when narrowing scope:

- `composer check:phpunit`
- `composer check:behat`

If running a subset, state what was not run and why.

## Contract Classification

Always classify changes as one of:

- `none`
- `additive`
- `behavior-changing`
- `breaking`

Include one-line rationale in summaries.

## Output Expectations

When reporting completed changes, include:

- files changed
- why each change was needed
- validation performed
- remaining risk/coverage gaps (if any)
