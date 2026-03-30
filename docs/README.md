# Behat Table Comparison

[![Packagist](https://img.shields.io/packagist/v/traviscarden/behat-table-comparison.svg)](https://packagist.org/packages/traviscarden/behat-table-comparison)
[![Build Status](https://travis-ci.org/TravisCarden/behat-table-comparison.svg?branch=develop)](https://travis-ci.org/TravisCarden/behat-table-comparison)
[![Coverage Status](https://coveralls.io/repos/github/TravisCarden/behat-table-comparison/badge.svg?branch=master)](https://coveralls.io/github/TravisCarden/behat-table-comparison?branch=master)

The Behat Table Comparison library provides an equality assertion for comparing Behat `TableNode` tables.

## Installation & Usage

Install the library via [Composer](https://getcomposer.org/):

```bash
composer require --dev traviscarden/behat-table-comparison
```

Then use the [`TableEqualityAssertion`](../src/BehatTableComparison/TableEqualityAssertion.php) class in your [`FeatureContext` class](http://docs.behat.org/en/v2.5/guides/4.context.html):

```php
<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;

class FeatureContext implements Context
{

    /**
     * @Then I should include the following characters in the Company of the Ring
     */
    public function iShouldIncludeTheFollowingCharactersInTheCompanyOfTheRing(TableNode $expected)
    {
        // Get the data from the application and create a table from it.
        $application_data = [
            ['Frodo Baggins', 'Hobbit'],
            ['Samwise "Sam" Gamgee', 'Hobbit'],
            ['Saruman the White', 'Wizard'],
            ['Legolas', 'Elf'],
            ['Gimli', 'Dwarf'],
            ['Aragorn (Strider)', 'Man'],
            ['Boromir', 'Man'],
            ['Meriadoc "Merry" Brandybuck', 'Hobbit'],
            ['Peregrin "Pippin" Took', 'Hobbit'],
        ];
        $actual = new TableNode($application_data);

        // Build and execute assertion.
        (new TableEqualityAssertion($expected, $actual))
            ->expectHeader(['name', 'race'])
            ->ignoreRowOrder()
            ->setMissingRowsLabel('Missing characters')
            ->setUnexpectedRowsLabel('Unexpected characters')
            ->setDuplicateRowsLabel('Duplicate characters')
            ->assert();
    }

}
```

Output is like the following:

![Example Output](example-output.gif)

## Error Message Specification

When tables are unequal, the assertion throws a detailed error message with labeled sections.

### Difference sections

- `--- Missing rows`: Rows present in expected but not in actual.
- `+++ Unexpected rows`: Rows present in actual but not in expected.
- `*** Duplicate rows`: Rows present on both sides with different multiplicity, shown as `(appears N time/times, expected M)`.

### Row-order diagnostics

When row order is respected and rows are out of order:

- `*** Row order mismatch` is shown.
- Per-row diagnostics are listed as `... should be at position X, found at Y`.
- Full order context is appended under:
    - `Expected order`
    - `Actual order`

When row content differs while respecting row order, semantic missing/unexpected/duplicate sections are shown first, then full expected/actual order tables.

### Header mismatch diagnostics

When `expectHeader(...)` is used and the first row does not match:

- `--- Expected header`
- `+++ Given header`

### Label customization

All user-facing section labels are configurable via defaults plus getter/setter pairs:

- Missing rows
- Unexpected rows
- Duplicate rows
- Row order mismatch
- Expected header
- Given header
- Expected order subheading
- Actual order subheading

## Examples

See [`features/bootstrap/FeatureContext.php`](../features/bootstrap/FeatureContext.php) and [`features/examples.feature`](../features/examples.feature) for more examples.

## Contribution

All contributions are welcome according to [normal open source practice](https://opensource.guide/how-to-contribute/#how-to-submit-a-contribution).
