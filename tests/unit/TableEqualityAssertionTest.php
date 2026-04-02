<?php declare(strict_types=1);

namespace TravisCarden\BehatTableComparison\Tests\Unit;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;
use TravisCarden\BehatTableComparison\UnequalTablesException;

#[CoversClass(TableEqualityAssertion::class)]
final class TableEqualityAssertionTest extends TestCase
{
    private const array TABLE_SIMPLE_SORTED = [[1, 2], [3, 4], [5, 6]];

    private TableNode $arbitraryLeft;

    private TableNode $arbitraryRight;

    /**
     * Tests object construction.
     */
    public function testConstruction(): void
    {
        $assertion = new TableEqualityAssertion($this->arbitraryLeft, $this->arbitraryRight);

        self::assertSame($assertion->getExpected(), $this->arbitraryLeft);
        self::assertSame($assertion->getActual(), $this->arbitraryRight);
    }

    public function testSettersPairs(): void
    {
        // Default values.
        $assertion = new TableEqualityAssertion($this->arbitraryLeft, $this->arbitraryRight);
        self::assertTrue($assertion->isRowOrderRespected());
        self::assertEmpty($assertion->getExpectedHeader());
        self::assertSame('Missing rows', $assertion->getMissingRowsLabel());
        self::assertSame('Unexpected rows', $assertion->getUnexpectedRowsLabel());
        self::assertSame('Duplicate rows', $assertion->getDuplicateRowsLabel());
        self::assertSame('Row order mismatch', $assertion->getRowOrderMismatchLabel());
        self::assertSame('Expected header', $assertion->getExpectedHeaderLabel());
        self::assertSame('Given header', $assertion->getGivenHeaderLabel());
        self::assertSame('Expected order', $assertion->getExpectedOrderLabel());
        self::assertSame('Actual order', $assertion->getActualOrderLabel());

        // Set values.
        $assertion
            ->ignoreRowOrder()
            ->expectHeader([1, 2, 3])
            ->setMissingRowsLabel('Gone')
            ->setUnexpectedRowsLabel('Extra')
            ->setDuplicateRowsLabel('Cloned')
            ->setRowOrderMismatchLabel('Scrambled')
            ->setExpectedHeaderLabel('Expected columns')
            ->setGivenHeaderLabel('Actual columns')
            ->setExpectedOrderLabel('Expected sequence')
            ->setActualOrderLabel('Actual sequence');
        self::assertFalse($assertion->isRowOrderRespected());
        self::assertEquals([1, 2, 3], $assertion->getExpectedHeader());
        self::assertSame('Gone', $assertion->getMissingRowsLabel());
        self::assertSame('Extra', $assertion->getUnexpectedRowsLabel());
        self::assertSame('Cloned', $assertion->getDuplicateRowsLabel());
        self::assertSame('Scrambled', $assertion->getRowOrderMismatchLabel());
        self::assertSame('Expected columns', $assertion->getExpectedHeaderLabel());
        self::assertSame('Actual columns', $assertion->getGivenHeaderLabel());
        self::assertSame('Expected sequence', $assertion->getExpectedOrderLabel());
        self::assertSame('Actual sequence', $assertion->getActualOrderLabel());

        // Unset values.
        $assertion
            ->respectRowOrder()
            ->expectNoHeader();
        self::assertTrue($assertion->isRowOrderRespected());
        self::assertEmpty($assertion->getExpectedHeader());
    }

    /**
     * Tests assertion with identical tables.
     */
    #[DataProvider('providerTestAssertionWithIdenticalTables')]
    public function testAssertionWithIdenticalTables(array $left, array $right): void
    {
        $left = new TableNode($left);
        $right = new TableNode($right);

        (new TableEqualityAssertion($left, $right))
            ->assert();
    }

    /**
     * Tests assertion with unequal tables.
     */
    #[DataProvider('providerTestAssertionWithUnequalTables')]
    public function testAssertionWithUnequalTables(array $left, array $right, array $expected): void
    {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode($left);
        $right = new TableNode($right);

        try {
            (new TableEqualityAssertion($left, $right))
                ->ignoreRowOrder()
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, $expected);
            self::assertSame($expected, $e->getMessage());
            self::assertSame(UnequalTablesException::CONTENT_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with complex differences while ignoring row order.
     */
    #[DataProvider('providerTestAssertionWithComplexDifferencesIgnoringRowOrder')]
    public function testAssertionWithComplexDifferencesIgnoringRowOrder(
        array $left,
        array $right,
        array $expected,
    ): void {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode($left);
        $right = new TableNode($right);

        try {
            (new TableEqualityAssertion($left, $right))
                ->ignoreRowOrder()
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, $expected);
            self::assertSame($expected, $e->getMessage());
            self::assertSame(UnequalTablesException::CONTENT_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests row order mismatch display while respecting row order.
     */
    public function testAssertionWithRowOrderMismatchMessage(): void
    {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode([
            ['id1', 'Label one'],
            ['id2', 'Label two'],
            ['id3', 'Label three'],
        ]);
        $right = new TableNode([
            ['id2', 'Label two'],
            ['id1', 'Label one'],
            ['id3', 'Label three'],
        ]);

        try {
            (new TableEqualityAssertion($left, $right))
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, [
                '*** Row order mismatch',
                '| id1 | Label one | should be at position 1, found at 2',
                '| id2 | Label two | should be at position 2, found at 1',
                'Expected order',
                '| id1 | Label one   |',
                '| id2 | Label two   |',
                '| id3 | Label three |',
                'Actual order',
                '| id2 | Label two   |',
                '| id1 | Label one   |',
                '| id3 | Label three |',
            ]);
            self::assertSame($expected, $e->getMessage());
            self::assertSame(UnequalTablesException::ROW_ORDER_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with custom label.
     */
    #[DataProvider('providerTestAssertionWithCustomLabels')]
    public function testAssertionWithCustomLabels(string $method, array $tables, string $label, string $prefix): void
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(...$tables))->ignoreRowOrder();
        $assertion = $assertion->$method($label);

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringStartsWith("{$prefix} {$label}", $e->getMessage());
            self::assertSame(UnequalTablesException::CONTENT_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with a custom row order mismatch label.
     */
    public function testAssertionWithCustomRowOrderMismatchLabel(): void
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(
            new TableNode([[1], [2]]),
            new TableNode([[2], [1]]),
        ))->setRowOrderMismatchLabel('Wrong order!');

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringStartsWith('*** Wrong order!', $e->getMessage());
            self::assertSame(UnequalTablesException::ROW_ORDER_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with custom header labels.
     */
    public function testAssertionWithCustomHeaderLabels(): void
    {
        $this->expectException(UnequalTablesException::class);
        $header = ['label', 'id'];
        $wrongHeader = ['Label one', 'id1'];
        $rows = [['Label one', 'id1'], ['Label two', 'id2']];
        $left = new TableNode(array_merge([$wrongHeader], $rows));

        try {
            (new TableEqualityAssertion($left, new TableNode($rows)))
                ->expectHeader($header)
                ->setExpectedHeaderLabel('Expected columns')
                ->setGivenHeaderLabel('Actual columns')
                ->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringContainsString('--- Expected columns', $e->getMessage());
            self::assertStringContainsString('+++ Actual columns', $e->getMessage());
            self::assertSame(UnequalTablesException::HEADER_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with custom order labels.
     */
    public function testAssertionWithCustomOrderLabels(): void
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(
            new TableNode([[1], [2]]),
            new TableNode([[2], [1]]),
        ))
            ->setExpectedOrderLabel('Expected sequence')
            ->setActualOrderLabel('Actual sequence');

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringContainsString('Expected sequence', $e->getMessage());
            self::assertStringContainsString('Actual sequence', $e->getMessage());
            self::assertSame(UnequalTablesException::ROW_ORDER_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with a table header.
     */
    public function testAssertionWithHeader(): void
    {
        $header = ['label', 'id'];
        $rows = [['Label one', 'id1'], ['Label two', 'id2']];
        $left = new TableNode(array_merge([$header], $rows));
        $right = new TableNode($rows);

        (new TableEqualityAssertion($left, $right))
            ->expectHeader($header)
            ->assert();
    }

    /**
     * Tests assertion with a table header mismatch.
     */
    public function testAssertionWithHeaderMismatch(): void
    {
        $this->expectException(UnequalTablesException::class);
        $header = ['label', 'id'];
        $wrongHeader = ['Label one', 'id1'];
        $left = new TableNode([$wrongHeader, ['Label one', 'id1'], ['Label two', 'id2']]);
        $right = new TableNode([['Label one', 'id1'], ['Label two', 'id2']]);

        try {
            (new TableEqualityAssertion($left, $right))
                ->expectHeader($header)
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, [
                '--- Expected header',
                '| label | id |',
                '+++ Given header',
                '| Label one | id1 |',
            ]);
            self::assertSame($expected, $e->getMessage());
            self::assertSame(UnequalTablesException::HEADER_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion ignoring row order.
     */
    public function testAssertionIgnoringRowOrder(): void
    {
        $left = new TableNode([
            ['id4', 'Label four', 'Fourth value', 'true'],
            ['id2', 'Label two', 'Second value', 'true'],
            ['id1', 'Label one', 'First value', 'true'],
            ['id3', 'Label three', 'Third value', 'false'],
            ['id5', 'Label five', 'Fifth value', 'false'],
        ]);
        $right = new TableNode([
            ['id1', 'Label one', 'First value', 'true'],
            ['id2', 'Label two', 'Second value', 'true'],
            ['id3', 'Label three', 'Third value', 'false'],
            ['id4', 'Label four', 'Fourth value', 'true'],
            ['id5', 'Label five', 'Fifth value', 'false'],
        ]);

        (new TableEqualityAssertion($left, $right))
            ->ignoreRowOrder()
            ->assert();
    }

    /**
     * Tests assertion with content differences while respecting row order.
     */
    #[DataProvider('providerTestAssertionWithContentDifferencesRespectingRowOrder')]
    public function testAssertionWithContentDifferencesRespectingRowOrder(
        array $left,
        array $right,
        array $expected,
    ): void {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode($left);
        $right = new TableNode($right);

        try {
            (new TableEqualityAssertion($left, $right))
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, $expected);
            self::assertSame($expected, $e->getMessage());
            self::assertSame(UnequalTablesException::CONTENT_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /**
     * Tests assertion with complex differences respecting row order.
     */
    public function testAssertionWithComplexDifferences(): void
    {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode([
            [1, 'one'],
            [2, 'two'],
            [3, 'three'],
            [4, 'four'],
            [5, 'five'],
            [6, 'six'],
            [7, 'seven'],
            [8, 'eight'],
            [9, 'nine'],
            [10, 'ten'],
        ]);
        $right = new TableNode([
            [1, 'one'],
            [2, 'two'],
            [2, 'two'], // Duplicate row.
            [3, 'three'],
            [4, 'four'],
            // Missing row.
            [6, 'six'],
            [7, 'seven'],
            [8, 'changed'], // Changed row.
            [9, 'nine'],
            [10, 'ten'],
            [13, 'thirteen'], // Unexpected row.
        ]);

        try {
            (new TableEqualityAssertion($left, $right))
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, [
                '--- Missing rows',
                '| 5 | five  |',
                '| 8 | eight |',
                '+++ Unexpected rows',
                '| 8  | changed  |',
                '| 13 | thirteen |',
                '*** Duplicate rows',
                '| 2 | two | (appears 2 times, expected 1)',
                'Expected order',
                '| 1  | one   |',
                '| 2  | two   |',
                '| 3  | three |',
                '| 4  | four  |',
                '| 5  | five  |',
                '| 6  | six   |',
                '| 7  | seven |',
                '| 8  | eight |',
                '| 9  | nine  |',
                '| 10 | ten   |',
                'Actual order',
                '| 1  | one      |',
                '| 2  | two      |',
                '| 2  | two      |',
                '| 3  | three    |',
                '| 4  | four     |',
                '| 6  | six      |',
                '| 7  | seven    |',
                '| 8  | changed  |',
                '| 9  | nine     |',
                '| 10 | ten      |',
                '| 13 | thirteen |',
            ]);
            self::assertSame($expected, $e->getMessage());
            self::assertSame(UnequalTablesException::CONTENT_MISMATCH, $e->getCode());

            throw $e;
        }
    }

    /** @return array<string, array{0: array, 1: array}> */
    public static function providerTestAssertionWithIdenticalTables(): array
    {
        return [
            'Identical with one single value row' => [[[1]], [[1]]],
            'Identical with one multi-value row' => [[[1, 2]], [[1, 2]]],
            'Identical with multiple multi-value rows' => [
                self::TABLE_SIMPLE_SORTED,
                self::TABLE_SIMPLE_SORTED,
            ],
        ];
    }

    /** @return array<string, array{0: array, 1: array, 2: list<string>}> */
    public static function providerTestAssertionWithUnequalTables(): array
    {
        return [
            'Missing rows' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id3', 'Label three'],
                    ['id4', 'Label four'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
                [
                    '--- Missing rows',
                    '| id3 | Label three |',
                    '| id4 | Label four  |',
                ],
            ],
            'Unexpected rows' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id3', 'Label three'],
                    ['id4', 'Label four'],
                ],
                [
                    '+++ Unexpected rows',
                    '| id3 | Label three |',
                    '| id4 | Label four  |',
                ],
            ],
            'Missing and unnexpected rows' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id3', 'Label three'],
                    ['id4', 'Label four'],
                ],
                [
                    '--- Missing rows',
                    '| id1 | Label one |',
                    '| id2 | Label two |',
                    '+++ Unexpected rows',
                    '| id3 | Label three |',
                    '| id4 | Label four  |',
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array, 1: array, 2: list<string>}> */
    public static function providerTestAssertionWithComplexDifferencesIgnoringRowOrder(): array
    {
        return [
            'Duplicate rows on actual' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                ],
                [
                    '*** Duplicate rows',
                    '| id2 | Label two | (appears 2 times, expected 1)',
                ],
            ],
            'Duplicate rows on expected' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
                [
                    '*** Duplicate rows',
                    '| id2 | Label two | (appears 1 time, expected 2)',
                ],
            ],
            'Duplicate rows fully missing from actual' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id1', 'Label one'],
                ],
                [
                    '--- Missing rows',
                    '| id2 | Label two |',
                    '| id2 | Label two |',
                ],
            ],
            'Duplicate rows fully unexpected in actual' => [
                [
                    ['id1', 'Label one'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                ],
                [
                    '+++ Unexpected rows',
                    '| id2 | Label two |',
                    '| id2 | Label two |',
                ],
            ],
            'Missing, unexpected, and duplicate rows together' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id3', 'Label three'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                    ['id4', 'Label four'],
                ],
                [
                    '--- Missing rows',
                    '| id3 | Label three |',
                    '+++ Unexpected rows',
                    '| id4 | Label four |',
                    '*** Duplicate rows',
                    '| id2 | Label two | (appears 2 times, expected 1)',
                ],
            ],
            'Changed row becomes missing and unexpected' => [
                [
                    ['id1', 'same'],
                    ['id2', 'before'],
                ],
                [
                    ['id1', 'same'],
                    ['id2', 'after'],
                ],
                [
                    '--- Missing rows',
                    '| id2 | before |',
                    '+++ Unexpected rows',
                    '| id2 | after |',
                ],
            ],
        ];
    }

    /** @return array<string, array{0: string, 1: array, 2: string, 3: string}> */
    public static function providerTestAssertionWithCustomLabels(): array
    {
        return [
            'Missing rows' => [
                'setMissingRowsLabel',
                [new TableNode([[1], [2]]), new TableNode([[1]])],
                "They're gone!",
                '---',
            ],
            'Unexpected rows' => [
                'setUnexpectedRowsLabel',
                [new TableNode([[1]]), new TableNode([[1], [2]])],
                'Free rows!',
                '+++',
            ],
            'Duplicate rows' => [
                'setDuplicateRowsLabel',
                [new TableNode([[1], [2]]), new TableNode([[1], [2], [2]])],
                'Cloned rows!',
                '***',
            ],
        ];
    }

    /** @return array<string, array{0: array, 1: array, 2: list<string>}> */
    public static function providerTestAssertionWithContentDifferencesRespectingRowOrder(): array
    {
        return [
            'Content differences, same order' => [
                [
                    ['id1', 'alpha'],
                    ['id2', 'beta'],
                ],
                [
                    ['id1', 'CHANGED'],
                    ['id2', 'beta'],
                ],
                [
                    '--- Missing rows',
                    '| id1 | alpha |',
                    '+++ Unexpected rows',
                    '| id1 | CHANGED |',
                    'Expected order',
                    '| id1 | alpha |',
                    '| id2 | beta  |',
                    'Actual order',
                    '| id1 | CHANGED |',
                    '| id2 | beta    |',
                ],
            ],
            'Content differences and row order both differ' => [
                [
                    ['id1', 'alpha'],
                    ['id2', 'beta'],
                    ['id3', 'gamma'],
                    ['id4', 'delta'],
                ],
                [
                    ['id3', 'gamma'],
                    ['id1', 'CHANGED'],
                    ['id4', 'delta'],
                    ['id5', 'epsilon'],
                ],
                [
                    '--- Missing rows',
                    '| id1 | alpha |',
                    '| id2 | beta  |',
                    '+++ Unexpected rows',
                    '| id1 | CHANGED |',
                    '| id5 | epsilon |',
                    'Expected order',
                    '| id1 | alpha |',
                    '| id2 | beta  |',
                    '| id3 | gamma |',
                    '| id4 | delta |',
                    'Actual order',
                    '| id3 | gamma   |',
                    '| id1 | CHANGED |',
                    '| id4 | delta   |',
                    '| id5 | epsilon |',
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->arbitraryLeft = new TableNode([['left']]);
        $this->arbitraryRight = new TableNode([['right']]);
    }
}
