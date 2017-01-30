<?php

namespace TravisCarden\BehatTableComparison;

use Behat\Testwork\Tester\Exception\TesterException;

/**
 * An exception for table inequalities.
 */
class UnequalTablesException extends \RuntimeException implements TesterException
{
}
