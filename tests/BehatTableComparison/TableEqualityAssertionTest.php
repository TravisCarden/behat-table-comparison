<?php

namespace TravisCarden\Tests\BehatTableComparison;

use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\TestCase;
use TravisCarden\Tests\AssertionError;
use TravisCarden\BehatTableComparison\TableEqualityAssertion;
use TravisCarden\BehatTableComparison\UnequalTablesException;

/**
 * @covers \TravisCarden\BehatTableComparison\TableEqualityAssertion
 */
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

        // Set values.
        $assertion
            ->ignoreRowOrder()
            ->expectHeader([1, 2, 3]);
        self::assertFalse($assertion->isRowOrderRespected());
        self::assertEquals([1, 2, 3], $assertion->getExpectedHeader());

        // Unset values.
        $assertion
            ->respectRowOrder()
            ->expectNoHeader();
        self::assertTrue($assertion->isRowOrderRespected());
        self::assertEmpty($assertion->getExpectedHeader());
    }

    /**
     * Tests assertion with identical tables.
     *
     * @dataProvider providerTestAssertionWithIdenticalTables
     */
    public function testAssertionWithIdenticalTables($left, $right)
    {
        $left = new TableNode($left);
        $right = new TableNode($right);

        $actual = (new TableEqualityAssertion($left, $right))
            ->assert();

        self::assertTrue($actual);
    }

    public function providerTestAssertionWithIdenticalTables()
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
     *
     * @dataProvider providerTestAssertionWithUnequalTables
     */
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

    public function providerTestAssertionWithUnequalTables()
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
     * Tests assertion with tables that are unequal in ways that do not yet have error messages specified.
     *
     * @todo Specify these scenarios.
     * @see https://github.com/TravisCarden/behat-table-comparison/issues/1
     *
     * @dataProvider providerTestAssertionWithUnspecifiedInequalities
     */
    public function testAssertionWithUnspecifiedInequalities($left, $right)
    {
        $this->expectException(UnequalTablesException::class);
        $left = new TableNode($left);
        $right = new TableNode($right);

        try {
            (new TableEqualityAssertion($left, $right))
                ->ignoreRowOrder()
                ->assert();
        } catch (UnequalTablesException $e) {
            $this->assertUnspecifiedErrorException($e, $right);
            throw $e;
        }
    }

    public function providerTestAssertionWithUnspecifiedInequalities()
    {
        return [
            'Duplicate rows on right' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                ],
            ],
            'Duplicate rows on left' => [
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                    ['id2', 'Label two'],
                ],
                [
                    ['id1', 'Label one'],
                    ['id2', 'Label two'],
                ],
            ],
        ];
    }

    /**
     * Tests assertion with custom label.
     *
     * @dataProvider providerTestAssertionWithCustomLabels
     */
    public function testAssertionWithCustomLabels($method, $tables, $label, $prefix)
    {
        $this->expectException(UnequalTablesException::class);
        $assertion = (new TableEqualityAssertion(...$tables))->ignoreRowOrder();
        /** @var TableEqualityAssertion $assertion */
        $assertion = call_user_func_array([$assertion, $method], [$label]);

        try {
            $assertion->assert();
        } catch (UnequalTablesException $e) {
            self::assertStringStartsWith("${prefix} ${label}", $e->getMessage());
            throw $e;
        }
    }

    public function providerTestAssertionWithCustomLabels()
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
        ];
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
                '+++ Given',
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
     * Tests assertion with complex differences.
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
                '--- Expected',
                '+++ Actual',
                '@@ @@',
                ' | 1  | one      |',
                ' | 2  | two      |',
                '+| 2  | two      |',
                ' | 3  | three    |',
                ' | 4  | four     |',
                '-| 5  | five     |',
                ' | 6  | six      |',
                ' | 7  | seven    |',
                '-| 8  | eight    |',
                '+| 8  | changed  |',
                ' | 9  | nine     |',
                '-| 10 | ten      |', // These two lines seem unintuitive, but
                '+| 10 | ten      |', // they come straight from the differ.
                '+| 13 | thirteen |',
                '',
            ]);
            self::assertSame($expected, $e->getMessage());

            throw $e;
        }
    }

    /**
     * @param \Exception $e
     * @param TableNode $right
     */
    protected function assertUnspecifiedErrorException(\Exception $e, TableNode $right)
    {
        $message = implode(PHP_EOL, [
            TableEqualityAssertion::UNSPECIFIED_DIFFERENCE_NOTICE,
            '*** Given',
            $right->getTableAsString(),
        ]);
        self::assertSame($message, $e->getMessage());
    }
}
