<?php declare(strict_types=1);

namespace TravisCarden\BehatTableComparison;

use RuntimeException;

/**
 * An exception for table inequalities.
 */
final class UnequalTablesException extends RuntimeException
{
    /**
     * The header row of the given table did not match the assertion's expected header.
     */
    public const int HEADER_MISMATCH = 1;

    /**
     * The tables differ in content: one or more rows are missing, unexpected, or duplicated.
     */
    public const int CONTENT_MISMATCH = 2;

    /**
     * The tables contain the same rows but in a different order.
     */
    public const int ROW_ORDER_MISMATCH = 3;

    /**
     * A structural error occurred while processing a table (e.g., invalid table node).
     *
     * The original low-level exception is available via getPrevious().
     */
    public const int STRUCTURAL_ERROR = 4;
}
