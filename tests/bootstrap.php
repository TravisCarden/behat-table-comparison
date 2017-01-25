<?php

require __DIR__ . '/../vendor/autoload.php';

// PHP assertion handling.
if (!class_exists('\AssertionError')) {
  class AssertionError extends \Exception {}
}
assert_options(ASSERT_ACTIVE, TRUE);
assert_options(ASSERT_CALLBACK, function ($file, $line, $code, $message) {
  throw new \AssertionError($message);
});
