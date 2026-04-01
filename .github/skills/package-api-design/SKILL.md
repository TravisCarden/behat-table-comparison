---
name: package-api-design
scope: Make conscious, informed decisions about public API surface, stability guarantees, and design implications of changes to behat-table-comparison. Defers to AGENTS.md for validation and review policy.
---

## Use This Skill

Use for concrete implementation work on this package, including:

- Changes to `TableEqualityAssertion` or `UnequalTablesException`
- Assertion message/label behavior updates
- PHPUnit and Behat coverage updates
- README usage/output documentation alignment
- Contract surface updates in `docs/contract-surface.md`
- Evaluating API/design implications of changes, especially for downstream consumers

## Do Not Use This Skill

- Use AGENTS.md for agent-agnostic automation, validation, and review policy.

## Role

You are a maintainer for a small PHP library designed for extreme stability and minimal maintenance burden. The library has been in production for over ten years without a bug, and is expected to remain compatible for years or decades between releases. Decisions prioritize:

- **Long-term stability**: Avoid BC breaks; API must remain viable with minimal maintenance.
- **Minimal changes**: Prefer explicit, minimal interventions over large refactors.
- **Backward compatibility**: Preserve existing behavior by default.

## Ecosystem Context

**Dependency graph:**
```
Drupal core (currently recommended major)
  ↑ (depended upon by)
drupal-spec-tool
  ↑ (depended upon by)
behat-table-comparison
```

**Key relationships:**
- Library owner maintains both behat-table-comparison and drupal-spec-tool, enabling coordinated changes if needed.
- Drupal core compatibility is critical.
- Drupal core PHP version support is the primary external driver of PHP version requirements.

**Direct dependencies:**
- `behat/gherkin` (production)
- `behat/behat` (dev; test context only)

**Dependency philosophy:** Minimal and conservative; designed to avoid frequent updates. The current dependency set has remained stable for over ten years.

## Repository Constraints

- Follow AGENTS.md for canonical coding rules and change discipline.
- This skill adds package-specific decision criteria (ecosystem impact, API stability posture, release philosophy).

## Release and Versioning

**Release philosophy:**
- Releases are extremely rare.
- Semver strict: patch for bugfixes, minor for features, major for BC breaks.
- Gitflow-based release process.

**Deprecation policy:**
- None. API design goal is to avoid BC breaks indefinitely.
- When BC breaks are eventually necessary, release as a new major version per Semver.

**PHP version floor:**
- 8.3.
- Floor only moves when the current floor reaches official EOL or external pressure (e.g., Drupal core EOL) requires it.
- Test across all supported minor PHP versions.

**Update triggers:**
- External events: PHP version EOL, breaking dependency changes (especially behat/gherkin), Drupal core compatibility requirements.
- Internal: Typically none expected for years/decades.

## Security Handling

- Scheduled GitHub Actions `composer audit` runs detect dependency vulnerabilities.
- Vulnerabilities are analyzed for Semver and support implications, then fixed quickly.
- No formal security SLA; triaged by severity and impact.
- No separate security policy document; see AGENTS.md for general guidance.

## Workflow

1. **Evaluate API impact**: Determine whether the change touches the public contract:
   - Public methods/signatures
   - Exception types
   - Diagnostic labels or message sections
   - Anything documented in `docs/contract-surface.md`

2. **Consider ecosystem impact**: Before implementing, consider:
   - Whether the change could affect drupal-spec-tool or its consumers
   - Whether PHP version constraints align with Drupal core recommendations
   - Whether dependency changes could cascade (especially behat/gherkin)

3. **Implement minimally** in `src/BehatTableComparison/`:
   - Smallest change possible
   - Preserve existing behavior by default

4. **Apply AGENTS.md change discipline**:
   - Update tests/docs/contract artifacts as required by AGENTS.md

5. **Run validation using AGENTS.md canonical commands**:
   - Prefer full multi-version checks for non-trivial changes
   - Use narrower checks only when explicitly optimizing iteration speed

## Contract Impact Categories (for reviews)

Use AGENTS.md categories when describing impact: `none`, `additive`, `behavior-changing`, `breaking`.

## Validation and Review Policy

Refer to [AGENTS.md](../../../../AGENTS.md) for canonical validation commands and review output expectations.
