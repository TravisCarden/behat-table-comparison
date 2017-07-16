<?php

namespace TravisCarden\BehatTableComparison;

use Behat\Gherkin\Node\TableNode;
use SebastianBergmann\Diff\Differ;

/**
 * Asserts equality between two TableNodes.
 */
class TableEqualityAssertion
{

    const DEFAULT_MISSING_ROWS_LABEL = 'Missing rows';

    const DEFAULT_UNEXPECTED_ROWS_LABEL = 'Unexpected rows';

    const UNSPECIFIED_DIFFERENCE_NOTICE = 'Notice: Detected differences that cannot yet be displayed. See https://github.com/TravisCarden/behat-table-comparison/issues/1.';

    /**
     * @var \Behat\Gherkin\Node\TableNode
     */
    protected $expected;

    /**
     * @var \Behat\Gherkin\Node\TableNode
     */
    protected $actual;

    /**
     * @var string
     */
    protected $missingRowsLabel = self::DEFAULT_MISSING_ROWS_LABEL;

    /**
     * @var string
     */
    protected $unexpectedRowsLabel = self::DEFAULT_UNEXPECTED_ROWS_LABEL;

    /**
     * @var array
     */
    protected $expectedHeader = [];

    /**
     * @var bool
     */
    protected $respectRowOrder = true;

    /**
     * TableEqualityAssertion constructor.
     *
     * @param \Behat\Gherkin\Node\TableNode $expected
     * @param \Behat\Gherkin\Node\TableNode $actual
     */
    public function __construct(TableNode $expected, TableNode $actual)
    {
        $this->expected = $expected;
        $this->actual = $actual;
    }

    /**
     * @return \Behat\Gherkin\Node\TableNode
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * @return \Behat\Gherkin\Node\TableNode
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * @return string
     */
    public function getMissingRowsLabel()
    {
        return $this->missingRowsLabel;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setMissingRowsLabel($label)
    {
        assert(is_string($label), 'Missing rows label must be a string.');
        $this->missingRowsLabel = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnexpectedRowsLabel()
    {
        return $this->unexpectedRowsLabel;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setUnexpectedRowsLabel($label)
    {
        assert(is_string($label), 'Unexpected rows label must be a string.');
        $this->unexpectedRowsLabel = $label;
        return $this;
    }

    /**
     * @return array
     */
    public function getExpectedHeader()
    {
        return $this->expectedHeader;
    }

    /**
     * @param array $header
     *
     * @return $this
     */
    public function expectHeader(array $header)
    {
        $this->expectedHeader = $header;
        return $this;
    }

    /**
     * @return $this
     */
    public function expectNoHeader()
    {
        $this->expectedHeader = [];
        return $this;
    }

    /**
     * @return bool
     */
    public function isRowOrderRespected()
    {
        return $this->respectRowOrder;
    }

    /**
     * @return $this
     */
    public function ignoreRowOrder()
    {
        $this->respectRowOrder = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function respectRowOrder()
    {
        $this->respectRowOrder = true;
        return $this;
    }

    /**
     * Performs the assertion.
     *
     * @return true
     *
     * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
     */
    public function assert()
    {
        $this->assertHeader();
        $this->assertBody();
        return true;
    }

    protected function assertHeader()
    {
        $expected_header = $this->getExpectedHeader();
        if (empty($expected_header)) {
            return;
        }

        $actual_header = $this->getExpected()->getRow(0);
        if ($expected_header === $actual_header) {
            return;
        }

        $message = [
            '--- Expected header',
            (new TableNode([$expected_header]))->getTableAsString(),
            '+++ Given',
            (new TableNode([$actual_header]))->getTableAsString(),
        ];
        throw new \LogicException(implode(PHP_EOL, $message));
    }

    protected function assertBody()
    {
        if ($this->isRowOrderRespected()) {
            $this->assertBodyRespectingRowOrder();
        } else {
            $this->assertBodyIgnoringRowOrder();
        }
    }

    protected function assertBodyRespectingRowOrder()
    {
        $expected_body_rows = $this->getExpectedBody()->getRows();
        $actual_body_rows = $this->getActual()->getRows();

        // Normalize column widths between expected and actual tables.
        $combined_table = (new TableNode(array_merge($expected_body_rows, $actual_body_rows)))
            ->getTableAsString();
        $combined_table_rows = explode(PHP_EOL, $combined_table);

        $expected_body = implode(PHP_EOL, array_slice($combined_table_rows, 0, count($expected_body_rows)));
        $actual_body = implode(PHP_EOL, array_slice($combined_table_rows, count($expected_body_rows)));

        if ($expected_body != $actual_body) {
            $diff = (new Differ("--- Expected\n+++ Actual\n"))
                ->diff($expected_body, $actual_body);
            throw new UnequalTablesException($diff);
        }
    }

    protected function assertBodyIgnoringRowOrder()
    {
        $expected_body = $this->sortTable($this->getExpectedBody());
        $actual_body = $this->sortTable($this->getActual());

        if ($expected_body != $actual_body) {
            $message = $this->generateMessageForPostSortDifferences($expected_body->getRows(), $actual_body->getRows());

            if (!$message) {
                $message = implode(PHP_EOL, [
                    self::UNSPECIFIED_DIFFERENCE_NOTICE,
                    '*** Given',
                    $actual_body->getTableAsString(),
                ]);
            }

            throw new UnequalTablesException($message);
        }
    }

    /**
     * @return \Behat\Gherkin\Node\TableNode
     */
    protected function getExpectedBody()
    {
        $body = $this->getExpected()->getRows();
        if ($this->getExpectedHeader()) {
            array_shift($body);
        }
        return new TableNode($body);
    }

    /**
     * @param \Behat\Gherkin\Node\TableNode $table
     *
     * @return \Behat\Gherkin\Node\TableNode
     */
    protected function sortTable(TableNode $table)
    {
        $raw_table = $table->getTable();
        sort($raw_table);
        return new TableNode($raw_table);
    }

    /**
     * @param array $expected_rows
     * @param array $actual_rows
     *
     * @return string
     */
    protected function generateMessageForPostSortDifferences(array $expected_rows, array $actual_rows)
    {
        $message = [];
        $this->addArrayDiffMessageLines($message, $actual_rows, $expected_rows, '--- ' . $this->getMissingRowsLabel());
        $this->addArrayDiffMessageLines($message, $expected_rows, $actual_rows, '+++ ' . $this->getUnexpectedRowsLabel());
        return implode(PHP_EOL, $message);
    }

    /**
     * @param array $message
     * @param array $left
     * @param array $right
     * @param string $label
     */
    protected function addArrayDiffMessageLines(array &$message, array $left, array $right, $label)
    {
        $differences = array_filter($right, function (array $row) use ($left) {
            return !in_array($row, $left);
        });
        if (!empty($differences)) {
            $message[] = $label;
            $message[] = (new TableNode($differences))->getTableAsString();
        }
    }
}
