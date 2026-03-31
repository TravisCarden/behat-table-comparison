# Copilot Instructions For behat-table-comparison

This file contains Copilot-specific overrides and clarifications for agent behavior in this repository.

For all agent-agnostic automation, validation, and review policy, see [AGENTS.md](../AGENTS.md).

## Copilot-Specific Guidance

- When using Copilot or Copilot Chat, ensure that agent-generated Git commands always append `| cat` or use `--no-pager` to avoid paged output in Zsh or CI environments.
- If Copilot-specific features or routing are required, document them here.

All other project, coding, validation, and review rules are defined in [AGENTS.md](../AGENTS.md).
