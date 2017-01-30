<?php

namespace TravisCarden\Tests;

require __DIR__ . '/../vendor/autoload.php';

// PHP assertion handling.
class AssertionError extends \Exception
{
}

assert_options(ASSERT_ACTIVE, true);
assert_options(ASSERT_CALLBACK, function ($file, $line, $code, $message) {
    throw new AssertionError($message);
});
