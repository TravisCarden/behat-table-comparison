<?php

namespace TravisCarden\Tests\BehatTableComparison;

use PHPUnit\Framework\TestCase;
use TravisCarden\BehatTableComparison\UnequalTablesException;

/**
 * Provides unit tests for UnequalTablesException.
 */
class UnequalTablesExceptionTest extends TestCase
{

  /**
   * Tests class inheritance.
   */
    public function testInheritance()
    {
        $exception = new UnequalTablesException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
