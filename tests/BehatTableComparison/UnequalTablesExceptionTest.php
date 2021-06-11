<?php

namespace TravisCarden\Tests\BehatTableComparison;

use Behat\Testwork\Tester\Exception\TesterException;
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

        $this->assertInstanceOf(TesterException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}
