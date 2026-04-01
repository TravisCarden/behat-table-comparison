<?php declare(strict_types=1);

namespace TravisCarden\BehatTableComparison\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use TravisCarden\BehatTableComparison\UnequalTablesException;

/**
 * Provides unit tests for UnequalTablesException.
 */
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
}
