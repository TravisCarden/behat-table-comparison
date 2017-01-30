<?php

namespace TravisCarden\Tests\BehatTableComparison;

use Behat\Testwork\Tester\Exception\TesterException;
use TravisCarden\BehatTableComparison\UnequalTablesException;

/**
 * Provides unit tests for UnequalTablesException.
 */
class UnequalTablesExceptionTest extends \PHPUnit_Framework_TestCase
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
