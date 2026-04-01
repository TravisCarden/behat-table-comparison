<?php declare(strict_types=1);

namespace TravisCarden\BehatTableComparison\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TravisCarden\BehatTableComparison\UnequalTablesException;

/**
 * Provides unit tests for UnequalTablesException.
 */
#[CoversClass(UnequalTablesException::class)]
final class UnequalTablesExceptionTest extends TestCase
{
    /**
     * Tests class inheritance.
     */
    public function testInheritance(): void
    {
        $exception = new UnequalTablesException();

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /**
     * Tests that all error code constants are defined, are integers, and are distinct.
     */
    public function testErrorCodeConstantsAreDefinedAndDistinct(): void
    {
        $codes = [
            UnequalTablesException::HEADER_MISMATCH,
            UnequalTablesException::CONTENT_MISMATCH,
            UnequalTablesException::ROW_ORDER_MISMATCH,
            UnequalTablesException::STRUCTURAL_ERROR,
        ];

        foreach ($codes as $code) {
            self::assertIsInt($code);
        }

        self::assertSame($codes, array_unique($codes), 'All error code constants must be distinct.');
    }

    /**
     * Tests that the exception correctly carries each error code via getCode().
     */
    #[DataProvider('providerTestErrorCode')]
    public function testErrorCode(int $code): void
    {
        $exception = new UnequalTablesException('message', $code);

        self::assertSame($code, $exception->getCode());
    }

    /** @return array<string, array{0: int}> */
    public static function providerTestErrorCode(): array
    {
        return [
            'HEADER_MISMATCH' => [UnequalTablesException::HEADER_MISMATCH],
            'CONTENT_MISMATCH' => [UnequalTablesException::CONTENT_MISMATCH],
            'ROW_ORDER_MISMATCH' => [UnequalTablesException::ROW_ORDER_MISMATCH],
            'STRUCTURAL_ERROR' => [UnequalTablesException::STRUCTURAL_ERROR],
        ];
    }

    /**
     * Tests that a wrapped previous exception is accessible via getPrevious().
     */
    public function testStructuralErrorPreservesPreviousException(): void
    {
        $previous = new RuntimeException('low-level failure');
        $exception = new UnequalTablesException(
            $previous->getMessage(),
            UnequalTablesException::STRUCTURAL_ERROR,
            $previous,
        );

        self::assertSame(UnequalTablesException::STRUCTURAL_ERROR, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
