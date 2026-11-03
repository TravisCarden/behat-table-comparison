# Contributing

## Requirements

- [Docker](https://docs.docker.com/get-docker/) — every check runs inside a container, so the supported PHP versions (8.3, 8.4, 8.5) do not need to be installed locally.
- PHP `^8.3` and [Composer](https://getcomposer.org/) on the host — to run the `composer` scripts below, which drive GrumPHP on the host. GrumPHP in turn runs each tool via `docker compose run --rm`. The `fix:*` scripts run directly on the host PHP.

---

## Setup

```bash
composer install   # host dependencies (includes GrumPHP)
composer build     # build the Docker images; equivalent: docker compose build
```

Rebuild images only when the `Dockerfile` changes (e.g. a new PHP version is added) or when images have been removed.

## Running checks

Run these on the host, not inside a container. The containers do not have Docker available, so Composer scripts run inside them fail.

```bash
composer check:fast   # static analysis + PHPUnit on PHP 8.3 + Behat (iterate with this)
composer check:all    # static analysis + PHPUnit on PHP 8.3, 8.4, and 8.5 + Behat (run before pushing)
```

Narrower checks:

| Script | Runs |
| --- | --- |
| `composer check:static` | PHPCS, PHPStan, Rector (dry run), `composer validate`, `composer normalize --dry-run`, and an autoload check, on PHP 8.3 |
| `composer check:test` | PHPUnit on 8.3, 8.4, and 8.5, plus Behat on 8.3 |
| `composer check:phpunit` | PHPUnit on 8.3, 8.4, and 8.5 |
| `composer check:behat` | Behat integration tests and examples on 8.3 |
| `composer check:phpcs`, `check:phpstan`, `check:rector` | One static analysis tool on 8.3 |

To run one test suite on a single PHP version, call GrumPHP directly:

```bash
vendor/bin/grumphp run --testsuite=test_php_84   # PHPUnit on 8.4 (also test_php_83, which adds Behat, and test_php_85)
```

Apply automatic fixes (Rector and PHPCBF) on the host:

```bash
composer fix:all
```

See `composer list` for all scripts and their aliases (for example `composer fast`, `composer all`, `composer fix-all`).

## Cleaning up

```bash
composer down   # equivalent: docker compose down
```

Containers are started with `--rm` and removed when a check finishes, so there is no `docker:up` counterpart and nothing runs between checks. `docker:down` only cleans up containers and networks left behind by an interrupted or failed run.

Images and the per-version `vendor/` volumes are preserved by `docker:down`, so you can resume without rebuilding. To remove the volumes as well, run `docker compose down --volumes` directly; the next run reinstalls dependencies.

---

## Code standards

- Write to the **PHP 8.3 floor**. Do not use language features or functions introduced after PHP 8.3, even though CI also runs on 8.4 and 8.5.
- Follow the project's coding standards and static analysis toolchain. Run `composer check:static` to verify; run `composer fix:all` to autocorrect where possible.
- Every non-generated file must end with a trailing newline.

## Keeping package artifacts clean

Anything that is not production library code, documentation, or examples must be added to `.gitattributes` with `export-ignore` so it is excluded from Composer/GitHub package archives.
