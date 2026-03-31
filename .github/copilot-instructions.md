# Copilot Instructions For behat-table-comparison

This repository is a small PHP library, not an application service. Optimize for predictable behavior, backward compatibility, and clear diagnostics.

## Project Shape

- Main code: `src/BehatTableComparison/`
- Unit tests: `tests/unit/BehatTableComparison/` (PHPUnit)
- Self-test scenarios: `tests/behat/features/` and `tests/behat/bootstrap/` (Behat)
- Runnable examples: `examples/features/` and `examples/bootstrap/`
- Public API center: `TableEqualityAssertion` and `UnequalTablesException`
- Contract source of truth: `docs/contract-surface.md`

## Coding Rules

- PHP `^8.3` is the supported range; write to the **floor** of that range. Do not use language features or functions introduced after PHP 8.3 (for example, features from PHP 8.4+).
- Preserve fluent API behavior on `TableEqualityAssertion` setters (`return $this`).
- Keep behavior changes minimal and explicit; do not silently rename labels or message sections.
- Follow existing PSR-2 style and current naming conventions in the file being edited.
- Ensure every non-automated file ends with a trailing linebreak (for example, exclude generated lockfiles like `composer.lock`).

## Change Discipline

- Do not modify `vendor/`, CI history badges, or unrelated project metadata unless explicitly requested.
- Treat `docs/contract-surface.md` as authoritative for what is stable in `1.x`.
- For behavior changes, update both:
  - PHPUnit tests in `tests/unit/BehatTableComparison/`
  - Behat integration coverage in `tests/behat/features/integration-tests.feature` when user-facing output changes
- When error message text changes, also update `README.md` sections that document output labels and diagnostics.
- If a change alters public methods, exception behavior, or diagnostics semantics, update `docs/contract-surface.md` in the same change.
- When adding any new file or directory that is not part of the production library, tests, documentation, or examples (for example CI config, Docker files, editor config, build artifacts), add it to `.gitattributes` with `export-ignore` so it is excluded from Composer/GitHub package archives.

## Validation Commands

Prefer this command after non-trivial edits to validate across all supported PHP versions:

- `composer docker:check-fast-all`

Use local (single-version) checks for rapid iteration:

- `composer check:test`
- `composer check:static`
- `composer check:fast`

Use targeted checks only when narrowing scope for speed:

- `composer check:phpunit`
- `composer check:behat`

If running a narrower check for speed, state what was not run.

## Review and Explanation Expectations

- Call out contract impact using: `none`, `additive`, `behavior-changing`, or `breaking`.
- In reviews, prioritize correctness and regression risk over style.
- Include file/line evidence for findings.
