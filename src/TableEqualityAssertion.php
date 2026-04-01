<?php declare(strict_types=1);

namespace TravisCarden\BehatTableComparison;

use Behat\Gherkin\Exception\NodeException;
use Behat\Gherkin\Node\TableNode;
use JsonException;

/**
 * Asserts equality between two TableNodes.
 */
final class TableEqualityAssertion
{
    private const string DEFAULT_MISSING_ROWS_LABEL = 'Missing rows';

    private const string DEFAULT_UNEXPECTED_ROWS_LABEL = 'Unexpected rows';

    private const string DEFAULT_DUPLICATE_ROWS_LABEL = 'Duplicate rows';

    private const string DEFAULT_ROW_ORDER_MISMATCH_LABEL = 'Row order mismatch';

    private const string DEFAULT_EXPECTED_HEADER_LABEL = 'Expected header';

    private const string DEFAULT_GIVEN_HEADER_LABEL = 'Given header';

    private const string DEFAULT_EXPECTED_ORDER_LABEL = 'Expected order';

    private const string DEFAULT_ACTUAL_ORDER_LABEL = 'Actual order';

    private string $missingRowsLabel = self::DEFAULT_MISSING_ROWS_LABEL;

    private string $unexpectedRowsLabel = self::DEFAULT_UNEXPECTED_ROWS_LABEL;

    private string $duplicateRowsLabel = self::DEFAULT_DUPLICATE_ROWS_LABEL;

    private string $rowOrderMismatchLabel = self::DEFAULT_ROW_ORDER_MISMATCH_LABEL;

    private string $expectedHeaderLabel = self::DEFAULT_EXPECTED_HEADER_LABEL;

    private string $givenHeaderLabel = self::DEFAULT_GIVEN_HEADER_LABEL;

    private string $expectedOrderLabel = self::DEFAULT_EXPECTED_ORDER_LABEL;

    private string $actualOrderLabel = self::DEFAULT_ACTUAL_ORDER_LABEL;

    /** @var array<string> */
    private array $expectedHeader = [];

    private bool $respectRowOrder = true;

    public function __construct(private readonly TableNode $expected, private readonly TableNode $actual)
    {
    }

    public function getExpected(): TableNode
    {
        return $this->expected;
    }

    public function getActual(): TableNode
    {
        return $this->actual;
    }

    public function getMissingRowsLabel(): string
    {
        return $this->missingRowsLabel;
    }

    public function setMissingRowsLabel(string $label): static
    {
        $this->missingRowsLabel = $label;

        return $this;
    }

    public function getUnexpectedRowsLabel(): string
    {
        return $this->unexpectedRowsLabel;
    }

    public function setUnexpectedRowsLabel(string $label): static
    {
        $this->unexpectedRowsLabel = $label;

        return $this;
    }

    public function getDuplicateRowsLabel(): string
    {
        return $this->duplicateRowsLabel;
    }

    public function setDuplicateRowsLabel(string $label): static
    {
        $this->duplicateRowsLabel = $label;

        return $this;
    }

    public function getRowOrderMismatchLabel(): string
    {
        return $this->rowOrderMismatchLabel;
    }

    public function setRowOrderMismatchLabel(string $label): static
    {
        $this->rowOrderMismatchLabel = $label;

        return $this;
    }

    public function getExpectedHeaderLabel(): string
    {
        return $this->expectedHeaderLabel;
    }

    public function setExpectedHeaderLabel(string $label): static
    {
        $this->expectedHeaderLabel = $label;

        return $this;
    }

    public function getGivenHeaderLabel(): string
    {
        return $this->givenHeaderLabel;
    }

    public function setGivenHeaderLabel(string $label): static
    {
        $this->givenHeaderLabel = $label;

        return $this;
    }

    public function getExpectedOrderLabel(): string
    {
        return $this->expectedOrderLabel;
    }

    public function setExpectedOrderLabel(string $label): static
    {
        $this->expectedOrderLabel = $label;

        return $this;
    }

    public function getActualOrderLabel(): string
    {
        return $this->actualOrderLabel;
    }

    public function setActualOrderLabel(string $label): static
    {
        $this->actualOrderLabel = $label;

        return $this;
    }

    /** @return list<string> */
    public function getExpectedHeader(): array
    {
        return array_values($this->expectedHeader);
    }

    /** @param list<string> $header */
    public function expectHeader(array $header): static
    {
        $this->expectedHeader = $header;

        return $this;
    }

    public function expectNoHeader(): static
    {
        $this->expectedHeader = [];

        return $this;
    }

    public function isRowOrderRespected(): bool
    {
        return $this->respectRowOrder;
    }

    public function ignoreRowOrder(): static
    {
        $this->respectRowOrder = false;

        return $this;
    }

    public function respectRowOrder(): static
    {
        $this->respectRowOrder = true;

        return $this;
    }

    /**
     * Performs the assertion.
     *
     * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
     */
    public function assert(): bool
    {
        try {
            $this->assertHeader();
            $this->assertBody();
        } catch (NodeException|JsonException $e) {
            throw new UnequalTablesException(
                $e->getMessage(),
                UnequalTablesException::STRUCTURAL_ERROR,
                $e,
            );
        }

        return true;
    }

    /**
     * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
     * @throws \Behat\Gherkin\Exception\NodeException
     */
    private function assertHeader(): void
    {
        $expectedHeader = $this->getExpectedHeader();

        if ($expectedHeader === []) {
            return;
        }

        $actualHeader = $this->expected->getRow(0);

        if ($expectedHeader === $actualHeader) {
            return;
        }

        $message = [
            '--- ' . $this->expectedHeaderLabel,
            (new TableNode([$expectedHeader]))->getTableAsString(),
            '+++ ' . $this->givenHeaderLabel,
            (new TableNode([$actualHeader]))->getTableAsString(),
        ];

        throw new UnequalTablesException(implode(PHP_EOL, $message), UnequalTablesException::HEADER_MISMATCH);
    }

    /**
     * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \JsonException
     */
    private function assertBody(): void
    {
        if ($this->respectRowOrder) {
            $this->assertBodyRespectingRowOrder();
        } else {
            $this->assertBodyIgnoringRowOrder();
        }
    }

    /**
     * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \JsonException
     */
    private function assertBodyRespectingRowOrder(): void
    {
        $expectedBodyRows = $this->getExpectedBody()->getRows();
        $actualBodyRows = $this->actual->getRows();

        // Normalize column widths between expected and actual tables.
        $combinedTable = (new TableNode(array_merge($expectedBodyRows, $actualBodyRows)))
            ->getTableAsString();
        $combinedTableRows = explode(PHP_EOL, $combinedTable);

        $expectedBody = implode(PHP_EOL, array_slice($combinedTableRows, 0, count($expectedBodyRows)));
        $actualBody = implode(PHP_EOL, array_slice($combinedTableRows, count($expectedBodyRows)));

        if ($expectedBody !== $actualBody) {
            $sortedExpectedRows = $this->sortTable(new TableNode($expectedBodyRows))->getRows();
            $sortedActualRows = $this->sortTable(new TableNode($actualBodyRows))->getRows();

            if ($sortedExpectedRows === $sortedActualRows) {
                $message = $this->generateMessageForRowOrderMismatch($expectedBodyRows, $actualBodyRows);

                throw new UnequalTablesException($message, UnequalTablesException::ROW_ORDER_MISMATCH);
            }

            $message = $this->generateMessageForContentAndOrderDifferences($expectedBodyRows, $actualBodyRows);

            throw new UnequalTablesException($message, UnequalTablesException::CONTENT_MISMATCH);
        }
    }

    /**
     * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \JsonException
     */
    private function assertBodyIgnoringRowOrder(): void
    {
        $expectedBody = $this->sortTable($this->getExpectedBody());
        $actualBody = $this->sortTable($this->actual);

        if ($expectedBody->getRows() !== $actualBody->getRows()) {
            $message = $this->generateMessageForPostSortDifferences($expectedBody->getRows(), $actualBody->getRows());

            throw new UnequalTablesException($message, UnequalTablesException::CONTENT_MISMATCH);
        }
    }

    /** @throws \Behat\Gherkin\Exception\NodeException */
    private function getExpectedBody(): TableNode
    {
        $body = $this->expected->getRows();

        if ($this->getExpectedHeader() !== []) {
            array_shift($body);
        }

        return new TableNode($body);
    }

    /** @throws \Behat\Gherkin\Exception\NodeException */
    private function sortTable(TableNode $table): TableNode
    {
        $rawTable = $table->getTable();
        sort($rawTable);

        return new TableNode($rawTable);
    }

    /**
     * @param list<list<string>> $expectedRows
     * @param list<list<string>> $actualRows
     *
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \JsonException
     */
    private function generateMessageForPostSortDifferences(array $expectedRows, array $actualRows): string
    {
        $message = [];
        $expectedCounts = $this->countRows($expectedRows);
        $actualCounts = $this->countRows($actualRows);

        // Rows completely absent from actual (expected > 0, actual == 0).
        $missingRows = $this->buildAbsentRows($expectedCounts, $actualCounts);
        // Rows entirely new in actual (actual > 0, expected == 0).
        $unexpectedRows = $this->buildAbsentRows($actualCounts, $expectedCounts);
        // Rows present on both sides but with differing counts.
        $duplicateRows = $this->buildDuplicateRowDifferenceLines($expectedCounts, $actualCounts);

        if ($missingRows !== []) {
            $message[] = '--- ' . $this->missingRowsLabel;
            $message[] = (new TableNode($missingRows))->getTableAsString();
        }

        if ($unexpectedRows !== []) {
            $message[] = '+++ ' . $this->unexpectedRowsLabel;
            $message[] = (new TableNode($unexpectedRows))->getTableAsString();
        }

        if ($duplicateRows !== []) {
            $message[] = '*** ' . $this->duplicateRowsLabel;

            foreach ($duplicateRows as $line) {
                $message[] = $line;
            }
        }

        return implode(PHP_EOL, $message);
    }

    /**
     * @param list<list<string>> $rows
     *
     * @return array<string, array{row: list<string>, count: int}>
     *
     * @throws \JsonException
     */
    private function countRows(array $rows): array
    {
        $counts = [];

        foreach ($rows as $row) {
            $key = json_encode($row, JSON_THROW_ON_ERROR) ?: '';

            if (!isset($counts[$key])) {
                $counts[$key] = [
                    'row' => $row,
                    'count' => 0,
                ];
            }

            ++$counts[$key]['count'];
        }

        return $counts;
    }

    /**
     * Returns rows that appear in $presentCounts but are completely absent from $absentCounts.
     *
     * @param array<string, array{row: list<string>, count: int}> $presentCounts
     * @param array<string, array{row: list<string>, count: int}> $absentCounts
     *
     * @return list<list<string>>
     */
    private function buildAbsentRows(array $presentCounts, array $absentCounts): array
    {
        $rows = [];

        foreach ($presentCounts as $key => $data) {
            if (($absentCounts[$key]['count'] ?? 0) > 0) {
                continue;
            }

            for ($i = 0; $i < $data['count']; ++$i) {
                $rows[] = $data['row'];
            }
        }

        return $rows;
    }

    /**
     * @param array<string, array{row: list<string>, count: int}> $expectedCounts
     * @param array<string, array{row: list<string>, count: int}> $actualCounts
     *
     * @return list<string>
     *
     * @throws \Behat\Gherkin\Exception\NodeException
     */
    private function buildDuplicateRowDifferenceLines(array $expectedCounts, array $actualCounts): array
    {
        $lines = [];
        $keys = array_unique(array_merge(array_keys($expectedCounts), array_keys($actualCounts)));

        foreach ($keys as $key) {
            $expectedCount = $expectedCounts[$key]['count'] ?? 0;
            $actualCount = $actualCounts[$key]['count'] ?? 0;

            if ($expectedCount === $actualCount) {
                continue;
            }

            // Only report as duplicate when the row is present on both sides.
            if ($expectedCount === 0) {
                continue;
            }

            if ($actualCount === 0) {
                continue;
            }

            $row = $actualCounts[$key]['row'] ?? $expectedCounts[$key]['row'];
            $rowString = (new TableNode([$row]))->getTableAsString();
            $actualTimesLabel = $actualCount === 1
                ? 'time'
                : 'times';
            $lines[] = sprintf(
                '%s (appears %d %s, expected %d)',
                $rowString,
                $actualCount,
                $actualTimesLabel,
                $expectedCount,
            );
        }

        return $lines;
    }

    /**
     * @param list<list<string>> $expectedRows
     * @param list<list<string>> $actualRows
     *
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \JsonException
     */
    private function generateMessageForRowOrderMismatch(array $expectedRows, array $actualRows): string
    {
        $message = ['*** ' . $this->rowOrderMismatchLabel];
        $expectedPositions = $this->mapRowPositions($expectedRows);
        $actualPositions = $this->mapRowPositions($actualRows);

        foreach ($expectedPositions as $key => $expectedData) {
            $actualData = $actualPositions[$key];
            $positionCount = min(count($expectedData['positions']), count($actualData['positions']));

            for ($i = 0; $i < $positionCount; ++$i) {
                $expectedPosition = $expectedData['positions'][$i];
                $actualPosition = $actualData['positions'][$i];

                if ($expectedPosition === $actualPosition) {
                    continue;
                }

                $message[] = sprintf(
                    '%s should be at position %d, found at %d',
                    (new TableNode([$expectedData['row']]))->getTableAsString(),
                    $expectedPosition,
                    $actualPosition,
                );
            }
        }

        $message[] = $this->expectedOrderLabel;
        $message[] = (new TableNode($expectedRows))->getTableAsString();
        $message[] = $this->actualOrderLabel;
        $message[] = (new TableNode($actualRows))->getTableAsString();

        return implode(PHP_EOL, $message);
    }

    /**
     * @param list<list<string>> $expectedRows
     * @param list<list<string>> $actualRows
     *
     * @throws \Behat\Gherkin\Exception\NodeException
     * @throws \JsonException
     */
    private function generateMessageForContentAndOrderDifferences(array $expectedRows, array $actualRows): string
    {
        $sortedExpected = $this->sortTable(new TableNode($expectedRows))->getRows();
        $sortedActual = $this->sortTable(new TableNode($actualRows))->getRows();

        $message = [];
        $contentMessage = $this->generateMessageForPostSortDifferences($sortedExpected, $sortedActual);

        if ($contentMessage !== '') {
            $message[] = $contentMessage;
        }

        $message[] = $this->expectedOrderLabel;
        $message[] = (new TableNode($expectedRows))->getTableAsString();
        $message[] = $this->actualOrderLabel;
        $message[] = (new TableNode($actualRows))->getTableAsString();

        return implode(PHP_EOL, $message);
    }

    /**
     * @param list<list<string>> $rows
     *
     * @return array<string, array{row: list<string>, positions: list<int>}>
     *
     * @throws \JsonException
     */
    private function mapRowPositions(array $rows): array
    {
        $positions = [];

        foreach ($rows as $index => $row) {
            $key = json_encode($row, JSON_THROW_ON_ERROR) ?: '';

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
