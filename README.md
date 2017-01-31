# Behat Table Comparison

[![Build Status](https://travis-ci.org/TravisCarden/behat-table-comparison.svg?branch=develop)](https://travis-ci.org/TravisCarden/behat-table-comparison)
[![Code Climate](https://codeclimate.com/github/TravisCarden/behat-table-comparison/badges/gpa.svg)](https://codeclimate.com/github/TravisCarden/behat-table-comparison)
[![Test Coverage](https://codeclimate.com/github/TravisCarden/behat-table-comparison/badges/coverage.svg)](https://codeclimate.com/github/TravisCarden/behat-table-comparison/coverage)

The Behat Table Comparison library provides an equality assertion for comparing Behat `TableNode` tables.

## Installation & Usage

Install the library via [Composer](https://getcomposer.org/):

```bash
composer require --dev traviscarden/behat-table-comparison
```

Then use the [`TableEqualityAssertion`](src/BehatTableComparison/TableEqualityAssertion.php) class in your [`FeatureContext` class](http://docs.behat.org/en/v2.5/guides/4.context.html):

```php
<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;

class FeatureContext implements Context
{

    /**
     * @Then some table should equal the following
     */
    public function someTableShouldEqualTheFollowing(TableNode $expected)
    {
        // Build a table of actual data from your application.
        $fake_data = [
            ['id2', 'Label Two'],
            ['id4', 'Label Four'],
        ];
        $actual = new TableNode($fake_data);

        // Assert equality between the actual table and the expected table.
        (new TableEqualityAssertion($expected, $actual))
            ->expectHeader(['id', 'label'])
            ->ignoreRowOrder()
            ->assert();
    }

}
```

Output is like the following:

![Example Output](misc/example-output.gif)

## Examples

See [`features/bootstrap/FeatureContext.php`](features/bootstrap/FeatureContext.php) and [`features/examples.feature`](features/examples.feature) for more examples.

## Limitations & Known Issues

* The library does not yet support table comparisons that respect (i.e., require identical) row order, because it has not yet been decided what the error output should look like. Suggestions are welcome in the issue queue.
* Duplicate rows currently fail equality assertion but do not yet show a helpful message.

## Contribution

All contributions are welcome according to [normal GitHub practice](https://guides.github.com/activities/contributing-to-open-source/#contributing).
