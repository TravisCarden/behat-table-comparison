---
name: package-api-design
description: Evaluate API, stability, and ecosystem impact of a change to behat-table-comparison
disable-model-invocation: true
user-invocable: true
---

# Package API Design

AGENTS.md owns coding rules, change discipline, validation commands, and contract-impact vocabulary (`none`, `additive`, `behavior-changing`, `breaking`). This skill adds only the project posture and ecosystem facts needed to judge API decisions.

Explicitly invoked: apply this posture to the task or change at hand (any argument supplied, otherwise the current conversation).

## Posture

Small PHP library built for extreme stability: in production 10+ years, expected to stay compatible for years or decades between releases, with minimal maintenance burden. Weigh every change against that:

- Existing behavior, including message text and labels that consumers may match on, is presumed load-bearing. Change it only with a reason that outweighs downstream cost.
- Prefer the smallest intervention that solves the actual problem; a larger refactor needs a concrete payoff.
- A new public surface is a permanent commitment (no deprecation policy). Add it only when needed, and favor shapes that are hard to misuse and cheap to keep.
- `docs/contract-surface.md` defines what is stable in `1.x`. If a change touches or contradicts it, say so explicitly.

Use judgment on how much analysis a change deserves: an internal fix needs little; anything touching public methods, exception types, labels, or message sections deserves a deliberate look at consumers and an honest impact category.

## Ecosystem

```
Drupal core (currently recommended major)
  ↑ depended on by
drupal-spec-tool
  ↑ depended on by
behat-table-comparison
```

- The owner maintains both this package and drupal-spec-tool, so coordinated changes are possible; still treat other consumers as unknown.
- Drupal core's supported PHP range is the main external driver of this package's PHP floor.
- Runtime dependency: `behat/gherkin`. Dev only: `behat/behat` (test context). The dependency set has been stable for 10+ years and should stay minimal; breaking changes in `behat/gherkin` are the most likely cascade risk.

## Release and Versioning

- Releases are very rare; strict Semver (patch = fixes, minor = features, major = BC breaks); Gitflow process.
- No deprecation policy: the goal is to avoid BC breaks indefinitely. When one becomes unavoidable, ship it as a new major.
- PHP floor is 8.3. It moves only when that version reaches official EOL or external pressure (e.g., Drupal core EOL) forces it. Test across all supported minor versions.
- Expected update triggers are external: PHP EOL, dependency breaks, Drupal compatibility. Internal triggers are rare.

## Security

Scheduled GitHub Actions run `composer audit`. Vulnerabilities are triaged by severity and impact, assessed for Semver/support implications, and fixed quickly. No formal SLA or separate security policy.
