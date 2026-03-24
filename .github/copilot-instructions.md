# Copilot Instructions For behat-table-comparison

This repository is a small PHP library, not an application service. Optimize for predictable behavior, backward compatibility, and clear diagnostics.

## Project Shape

- Main code: `src/BehatTableComparison/`
- Unit tests: `tests/BehatTableComparison/` (PHPUnit)
- End-to-end usage tests: `features/` and `features/bootstrap/` (Behat)
- Public API center: `TableEqualityAssertion` and `UnequalTablesException`

## Coding Rules

- Maintain compatibility with PHP `^7.3 || ^8.0`.
- Avoid features unavailable in PHP 7.3 (for example typed properties, union types, attributes, named arguments).
- Preserve fluent API behavior on `TableEqualityAssertion` setters (`return $this`).
- Keep behavior changes minimal and explicit; do not silently rename labels or message sections.
- Follow existing PSR-2 style and current naming conventions in the file being edited.

## Change Discipline

- Do not modify `vendor/`, CI history badges, or unrelated project metadata unless explicitly requested.
- For behavior changes, update both:
  - PHPUnit tests in `tests/BehatTableComparison/`
  - Behat integration coverage in `features/integration-tests.feature` when user-facing output changes
- When error message text changes, also update `README.md` sections that document output labels and diagnostics.

## Validation Commands

Prefer these commands after non-trivial edits:

- `composer test`
- `composer static`
- `composer fast`

Use targeted checks only when narrowing scope for speed:

- `composer phpunit`
- `composer behat`

If running a narrower check for speed, state what was not run.

## Review and Explanation Expectations

- Call out contract impact using: `none`, `additive`, `behavior-changing`, or `breaking`.
- In reviews, prioritize correctness and regression risk over style.
- Include file/line evidence for findings.