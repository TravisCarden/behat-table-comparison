# Test Structure

This directory contains all test suites for the library:

- **`unit/`**: PHPUnit unit tests covering library classes under `src/`.
  - Path: `tests/unit/`
  - Run: `composer check:phpunit`

- **`behat/`**: Behat self-test scenarios validating library behavior end-to-end.
  - Features: `tests/behat/features/`
  - Contexts: `tests/behat/bootstrap/`
  - Config: `tests/behat.yml`
  - Run: `composer check:behat` (Docker PHP 8.3 floor)

- **`integration/`**: Reserved for future integration tests (e.g., cross-library or system-level tests).
  - Currently empty; add test code here as needed.

## Running Tests

Run all tests:
```bash
composer check:test          # PHPUnit + Behat
composer check:fast          # All tests + static analysis
```

Run targeted tests:
```bash
composer check:phpunit       # PHPUnit only
composer check:behat         # Behat self-tests only (Docker PHP 8.3 floor)
```

## Test Coverage and Alignment

- Unit tests (`tests/unit/`) verify assertion behavior and exception contracts.
- Behat scenarios (`tests/behat/`) verify user-facing diagnostics and error messages.
- Both test suites are required to pass before release.
