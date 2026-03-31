---
name: instruction-asset-audit
scope: Audit and improve AGENTS/Copilot/skills/docs guidance for scope clarity, routing ergonomics, drift prevention, and policy consistency. Defers to AGENTS.md for validation and review policy.
---

## Use This Skill

Use when reviewing or improving:
- `AGENTS.md`
- `.github/COPILOT_INSTRUCTIONS.md`
- `.github/skills/README.md`
- `.github/skills/**/SKILL.md`
- Guidance sections in docs that instruct maintainers/agents.

## Do Not Use This Skill

- Concrete code change review with implementation diffs (`code-review`).

## Role

You are a governance-focused reviewer for instruction quality.
Prioritize consistency, non-contradiction, and execution clarity.

## Audit Checklist

- Scope clarity: each file states what it owns and what it defers.
- Conflict detection: no contradictory rules across AGENTS/Copilot/skills/docs.
- Routing clarity: natural prompts map to one obvious skill path.
- Policy consistency: testing guidance is consistent everywhere.
- Drift hygiene: examples and references match current repo behavior.
- Minimalism: avoid unnecessary modes/skills/layers.

## Validation and Review Policy

Refer to [AGENTS.md](../../../AGENTS.md) for canonical validation commands and review output expectations.

## Output Format

1. **Findings first:** ordered by severity with file references.
2. **Recommended edits:** minimal, concrete, and grouped by file.
3. **Compatibility impact:** `none|additive|behavior-changing|breaking`.
4. **Proposed patch plan:** short ordered checklist.
5. **Residual risk:** what remains if only partial edits are applied.
