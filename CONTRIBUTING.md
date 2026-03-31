# Contributing

## Requirements

- [Docker](https://docs.docker.com/get-docker/) — for testing against all supported PHP versions without installing them locally.
- PHP `^8.3` and [Composer](https://getcomposer.org/) — for running checks locally when your host PHP satisfies the constraint.

---

## Testing with Docker (recommended)

The repository ships with a Docker setup that covers all three supported PHP versions (8.3, 8.4, 8.5). Each service gets its own isolated `vendor/` volume, so the versions coexist without interfering.

### First-time setup

Build the images once after cloning:

```bash
composer build
# equivalent: docker compose build
```

Rebuild only when the `Dockerfile` changes (e.g. a new PHP version is added) or when images have been explicitly removed. Normal day-to-day use does not require a rebuild.

### Daily workflow

```bash
composer 83               # develop and iterate against PHP 8.3
composer docker-fast-all  # run check:fast on all PHP versions before pushing
```

### Run check:fast on one PHP version

```bash
composer 83   # PHP 8.3
composer 84   # PHP 8.4
composer 85   # PHP 8.5
# equivalent: composer docker:check-fast-83 / docker:check-fast-84 / docker:check-fast-85
```

### Run check:fast on all PHP versions

```bash
composer docker-fast-all
# equivalent: composer docker:check-fast-all
```

### Run a specific check on one PHP version

Pass any Composer script directly as an argument:

```bash
docker compose run --rm php83 composer check:phpunit
docker compose run --rm php83 composer check:behat
docker compose run --rm php84 composer check:static
```

### Clean up

```bash
composer down
# equivalent: docker compose down
```

There is no `docker:up` counterpart. All containers are run with `--rm` and are removed automatically when a check finishes, so there is no persistent "up" state. Nothing runs between checks — the only footprint between runs is disk space used by the stored images and volumes. `docker:down` is only needed to clean up residual containers and networks left behind by an interrupted or failed run.

Both images and named volumes (`vendor/` per PHP version) are preserved by `docker:down`, so you can resume work with `composer 83` directly — no rebuild required. To remove volumes as well, run `docker compose down --volumes` directly (the next run will reinstall dependencies).

---

## Testing locally (host PHP)

If your host PHP satisfies `^8.3`, you can run checks directly:

```bash
composer install
composer fast     # tests + static analysis
composer test     # tests only
composer static   # static analysis only
```

See `composer list` for all available scripts and their aliases.

---

## Code standards

- Write to the **PHP 8.3 floor**. Do not use language features or functions introduced after PHP 8.3, even though CI also runs on 8.4 and 8.5.
- Follow the PSR-2 coding standard used throughout the project. Run `composer cs` to check, `composer fix` to auto-correct.
- Every non-generated file must end with a trailing newline.

## Keeping package artifacts clean

Anything that is not production library code, documentation, or examples must be added to `.gitattributes` with `export-ignore` so it is excluded from Composer/GitHub package archives.
