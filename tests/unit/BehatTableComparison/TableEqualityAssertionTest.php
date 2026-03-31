<?php

namespace TravisCarden\BehatTableComparison\Tests\Unit;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;
use TravisCarden\BehatTableComparison\UnequalTablesException;

#[CoversClass(TableEqualityAssertion::class)]
class TableEqualityAssertionTest extends TestCase
{

    const TABLE_REALISTIC_SORTED = [
        ['id1', 'Label one', 'First value', 'true'],
        ['id2', 'Label two', 'Second value', 'true'],
        ['id3', 'Label three', 'Third value', 'false'],
        ['id4', 'Label four', 'Fourth value', 'true'],
        ['id5', 'Label five', 'Fifth value', 'false'],
    ];

    const TABLE_REALISTIC_UNSORTED = [
        ['id4', 'Label four', 'Fourth value', 'true'],
        ['id2', 'Label two', 'Second value', 'true'],
        ['id1', 'Label one', 'First value', 'true'],
        ['id3', 'Label three', 'Third value', 'false'],
        ['id5', 'Label five', 'Fifth value', 'false'],
    ];

    const TABLE_SIMPLE_SORTED = [[1, 2], [3, 4], [5, 6]];

    const TABLE_SIMPLE_UNSORTED = [[5, 6], [1, 2], [3, 4]];

    /**
     * @var \Behat\Gherkin\Node\TableNode
     */
    protected $arbitraryLeft;

    /**
     * @var \Behat\Gherkin\Node\TableNode
     */
    protected $arbitraryRight;

    public function setUp(): void
    {
        $this->arbitraryLeft = new TableNode([['left']]);
        $this->arbitraryRight = new TableNode([['right']]);
    }

    /**
     * Tests object construction.
     */
    public function testConstruction()
    {
        $assertion = new TableEqualityAssertion($this->arbitraryLeft, $this->arbitraryRight);

        self::assertInstanceOf(TableEqualityAssertion::class, $assertion);
        self::assertSame($assertion->getExpected(), $this->arbitraryLeft);
        self::assertSame($assertion->getActual(), $this->arbitraryRight);
    }

    public function testSettersPairs()
    {
        // Default values.
        $assertion = (new TableEqualityAssertion($this->arbitraryLeft, $this->arbitraryRight));
        self::assertTrue($assertion->isRowOrderRespected());
        self::assertEmpty($assertion->getExpectedHeader());
        self::assertSame(TableEqualityAssertion::DEFAULT_MISSING_ROWS_LABEL, $assertion->getMissingRowsLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_UNEXPECTED_ROWS_LABEL, $assertion->getUnexpectedRowsLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_DUPLICATE_ROWS_LABEL, $assertion->getDuplicateRowsLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_ROW_ORDER_MISMATCH_LABEL, $assertion->getRowOrderMismatchLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_EXPECTED_HEADER_LABEL, $assertion->getExpectedHeaderLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_GIVEN_HEADER_LABEL, $assertion->getGivenHeaderLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_EXPECTED_ORDER_LABEL, $assertion->getExpectedOrderLabel());
        self::assertSame(TableEqualityAssertion::DEFAULT_ACTUAL_ORDER_LABEL, $assertion->getActualOrderLabel());

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
    public function testAssertionWithIdenticalTables($left, $right)
    {
        $left = new TableNode($left);
        $right = new TableNode($right);

        $actual = (new TableEqualityAssertion($left, $right))
            ->assert();

        self::assertTrue($actual);
    }

    public static function providerTestAssertionWithIdenticalTables()
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

    /**
     * Tests assertion with unequal tables.
     */
    #[DataProvider('providerTestAssertionWithUnequalTables')]
    public function testAssertionWithUnequalTables($left, $right, $expected)
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
            throw $e;
        }
    }

    public static function providerTestAssertionWithUnequalTables()
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

    /**
     * Tests assertion with complex differences while ignoring row order.
     */
    #[DataProvider('providerTestAssertionWithComplexDifferencesIgnoringRowOrder')]
    public function testAssertionWithComplexDifferencesIgnoringRowOrder($left, $right, $expected)
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
            throw $e;
        }
    }

    public static function providerTestAssertionWithComplexDifferencesIgnoringRowOrder()
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

    /**
     * Tests row order mismatch display while respecting row order.
     */
    public function testAssertionWithRowOrderMismatchMessage()
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
            throw $e;
        }
    }

    /**
     * Tests assertion with custom label.
     */
    #[DataProvider('providerTestAssertionWithCustomLabels')]
    public function testAssertionWithCustomLabels($method, $tables, $label, $prefix)
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(...$tables))->ignoreRowOrder();
        /** @var TableEqualityAssertion $assertion */
        $assertion = call_user_func_array([$assertion, $method], [$label]);

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringStartsWith("{$prefix} {$label}", $e->getMessage());
            throw $e;
        }
    }

    public static function providerTestAssertionWithCustomLabels()
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

    /**
     * Tests assertion with a custom row order mismatch label.
     */
    public function testAssertionWithCustomRowOrderMismatchLabel()
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(
            new TableNode([[1], [2]]),
            new TableNode([[2], [1]])
        ))->setRowOrderMismatchLabel('Wrong order!');

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringStartsWith('*** Wrong order!', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tests assertion with custom header labels.
     */
    public function testAssertionWithCustomHeaderLabels()
    {
        $this->expectException(\LogicException::class);
        $rows = [['Label one', 'id1'], ['Label two', 'id2']];
        $left = $right = new TableNode($rows);

        try {
            (new TableEqualityAssertion($left, $right))
                ->expectHeader(['label', 'id'])
                ->setExpectedHeaderLabel('Expected columns')
                ->setGivenHeaderLabel('Actual columns')
                ->assert();
        } catch (\LogicException $e) {
            self::assertStringContainsString('--- Expected columns', $e->getMessage());
            self::assertStringContainsString('+++ Actual columns', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tests assertion with custom order labels.
     */
    public function testAssertionWithCustomOrderLabels()
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(
            new TableNode([[1], [2]]),
            new TableNode([[2], [1]])
        ))
            ->setExpectedOrderLabel('Expected sequence')
            ->setActualOrderLabel('Actual sequence');

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringContainsString('Expected sequence', $e->getMessage());
            self::assertStringContainsString('Actual sequence', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tests assertion with a table header.
     */
    public function testAssertionWithHeader()
    {
        $header = ['label', 'id'];
        $rows = [['Label one', 'id1'], ['Label two', 'id2']];
        $left = new TableNode(array_merge([$header], $rows));
        $right = new TableNode($rows);

        $actual = (new TableEqualityAssertion($left, $right))
            ->expectHeader($header)
            ->assert();

        self::assertTrue($actual);
    }

    /**
     * Tests assertion with a table header mismatch.
     */
    public function testAssertionWithHeaderMismatch()
    {
        $this->expectException(\LogicException::class);
        $rows = [['Label one', 'id1'], ['Label two', 'id2']];
        $left = $right = new TableNode($rows);

        try {
            (new TableEqualityAssertion($left, $right))
                ->expectHeader(['label', 'id'])
                ->assert();
        } catch (\LogicException $e) {
            $expected = implode(PHP_EOL, [
                '--- Expected header',
                '| label | id |',
                '+++ Given header',
                '| Label one | id1 |',
            ]);
            self::assertSame($expected, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tests assertion ignoring row order.
     */
    public function testAssertionIgnoringRowOrder()
    {
        $left = new TableNode(self::TABLE_REALISTIC_UNSORTED);
        $right = new TableNode(self::TABLE_REALISTIC_SORTED);

        $actual = (new TableEqualityAssertion($left, $right))
            ->ignoreRowOrder()
            ->assert();

        self::assertTrue($actual);
    }

    /**
     * Tests assertion with content differences while respecting row order.
     */
    #[DataProvider('providerTestAssertionWithContentDifferencesRespectingRowOrder')]
    public function testAssertionWithContentDifferencesRespectingRowOrder($left, $right, $expected)
    {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode($left);
        $right = new TableNode($right);

        try {
            (new TableEqualityAssertion($left, $right))
                ->assert();
        } catch (UnequalTablesException $e) {
            $expected = implode(PHP_EOL, $expected);
            self::assertSame($expected, $e->getMessage());
            throw $e;
        }
    }

    public static function providerTestAssertionWithContentDifferencesRespectingRowOrder()
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

    /**
     * Tests assertion with complex differences respecting row order.
     */
    public function testAssertionWithComplexDifferences()
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

            throw $e;
        }
    }
}
