<?php

namespace TravisCarden\BehatTableComparison\Tests\Behat\Bootstrap;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;

/**
 * Defines integration-test steps for end-to-end Behat coverage.
 */
class IntegrationTestingContext implements Context
{

    /**
     * @var \Behat\Gherkin\Node\TableNode|null
     */
    protected $integrationExpected;

    /**
     * @var \Behat\Gherkin\Node\TableNode|null
     */
    protected $integrationActual;

    /**
     * @var bool
     */
    protected $integrationRespectRowOrder = true;

    /**
     * @var array
     */
    protected $integrationExpectedHeader = [];

    /**
     * @var array
     */
    protected $integrationLabelSetters = [];

    /**
     * @var bool|null
     */
    protected $integrationComparisonPassed;

    /**
     * @var \Throwable|null
     */
    protected $integrationComparisonException;

    public function __construct()
    {
        $this->resetIntegrationState();
    }

    protected function resetIntegrationState()
    {
        $this->integrationExpected = null;
        $this->integrationActual = null;
        $this->integrationRespectRowOrder = true;
        $this->integrationExpectedHeader = [];
        $this->integrationLabelSetters = [];
        $this->integrationComparisonPassed = null;
        $this->integrationComparisonException = null;
    }

    /**
     * @Given /^the expected table is:$/
     */
    public function theExpectedTableIs(TableNode $expected)
    {
        $this->resetIntegrationState();
        $this->integrationExpected = $expected;
    }

    /**
     * @Given /^the actual table is:$/
     */
    public function theActualTableIs(TableNode $actual)
    {
        $this->integrationActual = $actual;
    }

    /**
     * @Given /^row order is ignored$/
     */
    public function rowOrderIsIgnored()
    {
        $this->integrationRespectRowOrder = false;
    }

    /**
     * @Given /^row order is respected$/
     */
    public function rowOrderIsRespected()
    {
        $this->integrationRespectRowOrder = true;
    }

    /**
     * @Given /^the expected header is:$/
     */
    public function theExpectedHeaderIs(TableNode $header)
    {
        $rows = $header->getRows();
        $this->integrationExpectedHeader = $rows[0] ?? [];
    }

    /**
     * @Given /^the "(missing rows|unexpected rows|duplicate rows|row order mismatch|expected header|given header|expected order|actual order)" label is "([^"]+)"$/
     */
    public function theLabelIs($labelType, $label)
    {
        $map = [
            'missing rows' => 'setMissingRowsLabel',
            'unexpected rows' => 'setUnexpectedRowsLabel',
            'duplicate rows' => 'setDuplicateRowsLabel',
            'row order mismatch' => 'setRowOrderMismatchLabel',
            'expected header' => 'setExpectedHeaderLabel',
            'given header' => 'setGivenHeaderLabel',
            'expected order' => 'setExpectedOrderLabel',
            'actual order' => 'setActualOrderLabel',
        ];

        if (!isset($map[$labelType])) {
            throw new \RuntimeException("Unknown label type: {$labelType}");
        }

        $this->integrationLabelSetters[] = [$map[$labelType], $label];
    }

    /**
     * @When /^I compare the tables$/
     */
    public function iCompareTheTables()
    {
        if (!$this->integrationExpected || !$this->integrationActual) {
            throw new \RuntimeException('Both expected and actual tables must be provided.');
        }

        $assertion = new TableEqualityAssertion($this->integrationExpected, $this->integrationActual);

        if (!$this->integrationRespectRowOrder) {
            $assertion->ignoreRowOrder();
        }

        if (!empty($this->integrationExpectedHeader)) {
            $assertion->expectHeader($this->integrationExpectedHeader);
        }

        foreach ($this->integrationLabelSetters as $setterAndValue) {
            [$setter, $value] = $setterAndValue;
            $assertion->{$setter}($value);
        }

        try {
            $assertion->assert();
            $this->integrationComparisonPassed = true;
            $this->integrationComparisonException = null;
        } catch (\Throwable $e) {
            $this->integrationComparisonPassed = false;
            $this->integrationComparisonException = $e;
        }
    }

    /**
     * @Then /^the comparison should pass$/
     */
    public function theComparisonShouldPass()
    {
        if ($this->integrationComparisonPassed !== true) {
            $message = $this->integrationComparisonException
                ? $this->integrationComparisonException->getMessage()
                : 'Comparison did not pass.';
            throw new \RuntimeException("Expected comparison to pass. Actual: {$message}");
        }
    }

    /**
     * @Then /^the comparison should fail$/
     */
    public function theComparisonShouldFail()
    {
        if ($this->integrationComparisonPassed !== false || !$this->integrationComparisonException) {
            throw new \RuntimeException('Expected comparison to fail, but it passed.');
        }
    }

    /**
     * @Then /^the error message should contain:$/
     */
    public function theErrorMessageShouldContain(PyStringNode $message)
    {
        if (!$this->integrationComparisonException) {
            throw new \RuntimeException('No comparison exception is available.');
        }

        $needle = $message->getRaw();
        $haystack = $this->integrationComparisonException->getMessage();
        if (strpos($haystack, $needle) === false) {
            throw new \RuntimeException(
                "Expected error message to contain:\n{$needle}\n\nActual message:\n{$haystack}"
            );
        }
    }

    /**
     * @Then /^the error message should contain the full output:$/
     */
    public function theErrorMessageShouldContainTheFullOutput(PyStringNode $message)
    {
        if (!$this->integrationComparisonException) {
            throw new \RuntimeException('No comparison exception is available.');
        }

        $expected = $message->getRaw();
        $actual = $this->integrationComparisonException->getMessage();
        if ($expected !== $actual) {
            throw new \RuntimeException(
                "Expected error message to match exactly.\n\nExpected:\n{$expected}\n\nActual:\n{$actual}"
            );
        }
    }
}
