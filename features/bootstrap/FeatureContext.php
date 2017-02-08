<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{

    /**
     * @Given I am :author
     * @Given I am writing :work
     */
    public function doNothing()
    {
    }

    /**
     * @Then I should include the following :items in :group
     */
    public function iShouldIncludeTheFollowingIn($group, TableNode $expected)
    {
        switch ($group) {
            case 'The Lord of the Rings series':
                $actual = new TableNode([
                    ['The Fellowship of the Ring'],
                    ['The Two Towers'],
                    ['The Return of the King'],
                ]);
                (new TableEqualityAssertion($expected, $actual))
                    ->setMissingRowsLabel('Missing books')
                    ->setUnexpectedRowsLabel('Unexpected books')
                    ->assert();
                break;

            case 'the Company of the Ring':
                $actual = new TableNode([
                    ['Frodo Baggins', 'Hobbit'],
                    ['Samwise "Sam" Gamgee', 'Hobbit'],
                    ['Gandalf the Grey', 'Wizard'],
                    ['Legolas', 'Elf'],
                    ['Gimli', 'Dwarf'],
                    ['Aragorn (Strider)', 'Man'],
                    ['Boromir', 'Man'],
                    ['Meriadoc "Merry" Brandybuck', 'Hobbit'],
                    ['Peregrin "Pippin" Took', 'Hobbit'],
                ]);
                (new TableEqualityAssertion($expected, $actual))
                    ->expectHeader(['name', 'race'])
                    ->ignoreRowOrder()
                    ->setMissingRowsLabel('Missing characters')
                    ->setUnexpectedRowsLabel('Unexpected characters')
                    ->assert();
                break;

            default:
                throw new PendingException();
        }
    }

}
