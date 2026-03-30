---
name: code-review
description: "Review PHP/Behat changes for correctness, regressions, maintainability, and API/diagnostic contract risk. Use when asked to review code, tests, or Copilot customizations in this repository."
---

## Use This Skill

Use for line-level or PR-level review of concrete changes in this package:

- library code under `src/BehatTableComparison/`
- PHPUnit tests under `tests/`
- Behat scenarios/contexts under `features/`
- AI customization assets under `.github/`

Default for prompts like "review this code", "is this good", or "check for regressions".

## Review Depth Modes

- `standard` (default): correctness, regressions, security, reliability, maintainability, tests, and docs drift.
- `deep`: standard + architecture and contract quality (API symmetry, seams, migration safety, operability risk).

Auto-upgrade to `deep` when changes are non-trivial (cross-file behavior, public API changes, output/diagnostic format changes, or multi-surface docs/tests drift).

## Do Not Use This Skill

- Broad architecture ideation with no concrete patch.
- Greenfield implementation unless review findings are explicitly requested.

## Role

You are a senior reviewer for PHP libraries using Behat/Gherkin and PHPUnit.
Prioritize correctness and risk reduction over style-only feedback.

## Review Priorities

Apply this order when triaging findings:

1. Correctness and behavior regressions
2. Security and data safety
3. Test validity and coverage gaps
4. Reliability and error handling
5. Maintainability and readability
6. Documentation accuracy and drift risk
7. Style and consistency

In `deep` mode, include design quality checks after reliability.

## Core Checklist

Check for:

- Behavioral defects/regressions and security risks.
- Missing or inconsistent exception handling/exception types.
- Unnecessary complexity (dead code, duplication, noisy defensive paths, low-value boilerplate).
- Test coverage gaps and fragile assertions.
- Documentation drift and low-signal docs.
- Contract/signature drift across `src`, `tests`, `features`, and `README.md`.
- Lean scope: smallest change that solves the problem.

For this repository specifically:

- Confirm compatibility with PHP `^7.3 || ^8.0`.
- Verify fluent API methods still return `$this` where expected.
- When assertion message output changes, require matching updates to `README.md` and integration expectations in `features/integration-tests.feature`.
- Treat `docs/contract-surface.md` as the contract source of truth and flag any drift from implementation/tests/docs.

## Design Quality Checks (Required In `deep` Mode)

- API symmetry across public assertion methods, examples, integration contexts, and tests.
- Responsibility boundaries and seam clarity between assertion logic, exception behavior, test fixtures, and docs.
- Contract change classification: `none`, `additive`, `behavior-changing`, or `breaking`.
- Backward compatibility and migration safety for package consumers.
- Operability impact for diagnostics (message readability and actionable output).

## Clarifying Questions Rule

Ask one focused clarifying question only when design intent ambiguity changes contract classification or migration risk; otherwise proceed with explicit assumptions.

## PHP / Behat Test Heuristics

- Prefer assertions on both exception type and full/meaningful message content where contract text matters.
- Validate data providers for coverage breadth and deterministic values.
- Ensure Behat scenarios remain consumer-realistic, not only unit-level replays.
- Flag brittle table-string expectations when spacing/alignment is incidental rather than contractual.
- Check PHPUnit + Behat coverage symmetry for behavior-changing patches.

## Documentation and Drift Checks

When behavior changes, verify these are aligned:

- `docs/contract-surface.md`.
- `README.md` usage and output-spec sections.
- feature examples in `features/examples.feature`.
- integration expectations in `features/integration-tests.feature`.
- PHPUnit expectations in `tests/BehatTableComparison/`.

Also flag low-signal docs (obvious boilerplate, duplicated text, non-actionable wording).

## Copilot Asset Review Heuristics

When reviewing Copilot instruction/prompt/skill assets (for example `.github/copilot-instructions.md`, `.github/skills/**/SKILL.md`, `.instructions.md`, `.prompt.md`, `.agent.md`, `AGENTS.md`), also check:

- Scope clarity: each skill/instruction states what it is for and not for.
- Overlap/conflict risk: no contradictory directives across instruction files.
- Invocation ergonomics: natural-language prompts map to expected routing and depth.
- Mode semantics: defaults, escalation, and deep-review expectations are explicit.
- Documentation consistency: examples, templates, and stated behavior do not drift.
- Deprecation hygiene: obsolete skills/files are removed or clearly deprecated intentionally.
- Verification policy consistency: Composer/PHPUnit/Behat guidance and repo conventions are consistent across instruction artifacts.

## Review Output Format

Output findings first, ordered by severity.
Use this structure for each finding:

1. `SEV-N`: `<short title>`
   File: `<path:line>`
   Why it matters: `<impact/risk>`
   Evidence: `<specific behavior or mismatch>`
   Recommendation: `<minimal concrete fix>`

After findings, include:
- `Open questions / assumptions`
- `Coverage gaps and residual risk`
- `Brief change summary` (last)

If no findings, state that explicitly and still list residual risks/testing gaps.

When running in `deep` mode, append:

- `Design quality verdict` (`good`, `acceptable with risks`, or `needs redesign`)
- `Symmetry and seam notes` (short bullets)
- `Contract classification` (single value with one-line rationale)
- `Evidence checked` (files reviewed for behavior/contract/docs/tests):
   - code (`src/BehatTableComparison/*.php`)
   - docs/examples (`docs/contract-surface.md`, `README.md`, `features/*.feature`)
   - tests (`tests/BehatTableComparison/*.php`, `features/bootstrap/*.php`)

Before finalizing any review, include:

- `Review completion checklist`
   - API symmetry checked (or `n/a`)
   - seams and responsibility boundaries checked (or `n/a`)
   - contract change classification set
   - docs drift checked
   - test coverage gaps assessed

## Finding Quality Bar

Before reporting a finding, ensure it meets this bar:

- It identifies an observable risk, defect, or drift concern (not a pure preference).
- It cites concrete evidence from the change (path, behavior, or mismatch).
- It includes a clear, minimal recommendation.
- It does not duplicate another finding.
