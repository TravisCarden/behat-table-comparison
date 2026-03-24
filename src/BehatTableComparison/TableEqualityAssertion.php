<?php

namespace TravisCarden\BehatTableComparison;

use Behat\Gherkin\Node\TableNode;

/**
 * Asserts equality between two TableNodes.
 */
class TableEqualityAssertion
{

    const DEFAULT_MISSING_ROWS_LABEL = 'Missing rows';

    const DEFAULT_UNEXPECTED_ROWS_LABEL = 'Unexpected rows';

    const DEFAULT_DUPLICATE_ROWS_LABEL = 'Duplicate rows';

    const DEFAULT_ROW_ORDER_MISMATCH_LABEL = 'Row order mismatch';

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
     * @var string
     */
    protected $duplicateRowsLabel = self::DEFAULT_DUPLICATE_ROWS_LABEL;

    /**
     * @var string
     */
    protected $rowOrderMismatchLabel = self::DEFAULT_ROW_ORDER_MISMATCH_LABEL;

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
    public function setMissingRowsLabel(string $label)
    {
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
    public function setUnexpectedRowsLabel(string $label)
    {
        $this->unexpectedRowsLabel = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getDuplicateRowsLabel()
    {
        return $this->duplicateRowsLabel;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setDuplicateRowsLabel(string $label)
    {
        $this->duplicateRowsLabel = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getRowOrderMismatchLabel()
    {
        return $this->rowOrderMismatchLabel;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setRowOrderMismatchLabel(string $label)
    {
        $this->rowOrderMismatchLabel = $label;
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
            $sorted_expected_rows = $this->sortTable(new TableNode($expected_body_rows))->getRows();
            $sorted_actual_rows = $this->sortTable(new TableNode($actual_body_rows))->getRows();

            if ($sorted_expected_rows == $sorted_actual_rows) {
                $message = $this->generateMessageForRowOrderMismatch($expected_body_rows, $actual_body_rows);
                throw new UnequalTablesException($message);
            }

            $message = $this->generateMessageForContentAndOrderDifferences($expected_body_rows, $actual_body_rows);
            throw new UnequalTablesException($message);
        }
    }

    protected function assertBodyIgnoringRowOrder()
    {
        $expected_body = $this->sortTable($this->getExpectedBody());
        $actual_body = $this->sortTable($this->getActual());

        if ($expected_body != $actual_body) {
            $message = $this->generateMessageForPostSortDifferences($expected_body->getRows(), $actual_body->getRows());
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
        $expected_counts = $this->countRows($expected_rows);
        $actual_counts = $this->countRows($actual_rows);

        // Rows completely absent from actual (expected > 0, actual == 0).
        $missing_rows = $this->buildAbsentRows($expected_counts, $actual_counts);
        // Rows entirely new in actual (actual > 0, expected == 0).
        $unexpected_rows = $this->buildAbsentRows($actual_counts, $expected_counts);
        // Rows present on both sides but with differing counts.
        $duplicate_rows = $this->buildDuplicateRowDifferenceLines($expected_counts, $actual_counts);

        if (!empty($missing_rows)) {
            $message[] = '--- ' . $this->getMissingRowsLabel();
            $message[] = (new TableNode($missing_rows))->getTableAsString();
        }
        if (!empty($unexpected_rows)) {
            $message[] = '+++ ' . $this->getUnexpectedRowsLabel();
            $message[] = (new TableNode($unexpected_rows))->getTableAsString();
        }
        if (!empty($duplicate_rows)) {
            $message[] = '*** ' . $this->getDuplicateRowsLabel();
            foreach ($duplicate_rows as $line) {
                $message[] = $line;
            }
        }

        return implode(PHP_EOL, $message);
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    protected function countRows(array $rows)
    {
        $counts = [];
        foreach ($rows as $row) {
            $key = json_encode($row);
            if (!isset($counts[$key])) {
                $counts[$key] = [
                    'row' => $row,
                    'count' => 0,
                ];
            }
            $counts[$key]['count']++;
        }
        return $counts;
    }

    /**
     * Returns rows that appear in $present_counts but are completely absent from $absent_counts.
     *
     * @param array $present_counts
     * @param array $absent_counts
     *
     * @return array
     */
    protected function buildAbsentRows(array $present_counts, array $absent_counts)
    {
        $rows = [];
        foreach ($present_counts as $key => $data) {
            if (!empty($absent_counts[$key]['count'])) {
                continue;
            }

            for ($i = 0; $i < $data['count']; $i++) {
                $rows[] = $data['row'];
            }
        }
        return $rows;
    }

    /**
     * @param array $expected_counts
     * @param array $actual_counts
     *
     * @return array
     */
    protected function buildDuplicateRowDifferenceLines(array $expected_counts, array $actual_counts)
    {
        $lines = [];
        $keys = array_unique(array_merge(array_keys($expected_counts), array_keys($actual_counts)));

        foreach ($keys as $key) {
            $expected_count = $expected_counts[$key]['count'] ?? 0;
            $actual_count = $actual_counts[$key]['count'] ?? 0;
            if ($expected_count == $actual_count) {
                continue;
            }
            // Only report as duplicate when the row is present on both sides.
            if ($expected_count === 0 || $actual_count === 0) {
                continue;
            }

            $row = $actual_counts[$key]['row'] ?? $expected_counts[$key]['row'];
            $row_string = (new TableNode([$row]))->getTableAsString();
            $actual_times_label = $actual_count === 1 ? 'time' : 'times';
            $lines[] = sprintf(
                '%s (appears %d %s, expected %d)',
                $row_string,
                $actual_count,
                $actual_times_label,
                $expected_count
            );
        }

        return $lines;
    }

    /**
     * @param array $expected_rows
     * @param array $actual_rows
     *
     * @return string
     */
    protected function generateMessageForRowOrderMismatch(array $expected_rows, array $actual_rows)
    {
        $message = ['*** ' . $this->getRowOrderMismatchLabel()];
        $expected_positions = $this->mapRowPositions($expected_rows);
        $actual_positions = $this->mapRowPositions($actual_rows);

        foreach ($expected_positions as $key => $expected_data) {
            $actual_data = $actual_positions[$key];
            $position_count = min(count($expected_data['positions']), count($actual_data['positions']));

            for ($i = 0; $i < $position_count; $i++) {
                $expected_position = $expected_data['positions'][$i];
                $actual_position = $actual_data['positions'][$i];
                if ($expected_position == $actual_position) {
                    continue;
                }

                $message[] = sprintf(
                    '%s should be at position %d, found at %d',
                    (new TableNode([$expected_data['row']]))->getTableAsString(),
                    $expected_position,
                    $actual_position
                );
            }
        }

        $message[] = 'Expected order:';
        $message[] = (new TableNode($expected_rows))->getTableAsString();
        $message[] = 'Actual order:';
        $message[] = (new TableNode($actual_rows))->getTableAsString();

        return implode(PHP_EOL, $message);
    }

    /**
     * @param array $expected_rows
     * @param array $actual_rows
     *
     * @return string
     */
    protected function generateMessageForContentAndOrderDifferences(array $expected_rows, array $actual_rows)
    {
        $sorted_expected = $this->sortTable(new TableNode($expected_rows))->getRows();
        $sorted_actual = $this->sortTable(new TableNode($actual_rows))->getRows();

        $message = [];
        $content_message = $this->generateMessageForPostSortDifferences($sorted_expected, $sorted_actual);
        if ($content_message) {
            $message[] = $content_message;
        }

        $message[] = 'Expected order:';
        $message[] = (new TableNode($expected_rows))->getTableAsString();
        $message[] = 'Actual order:';
        $message[] = (new TableNode($actual_rows))->getTableAsString();

        return implode(PHP_EOL, $message);
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    protected function mapRowPositions(array $rows)
    {
        $positions = [];
        foreach ($rows as $index => $row) {
            $key = json_encode($row);
            if (!isset($positions[$key])) {
                $positions[$key] = [
                    'row' => $row,
                    'positions' => [],
                ];
            }
            $positions[$key]['positions'][] = $index + 1;
        }
        return $positions;
    }
}
