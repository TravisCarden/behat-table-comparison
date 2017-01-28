<?php

namespace TravisCarden\BehatTableComparison;

use Behat\Gherkin\Node\TableNode;

/**
 * Asserts equality between two TableNodes.
 */
class TableEqualityAssertion {

  const DEFAULT_MISSING_ROWS_LABEL = 'Missing rows';

  const DEFAULT_UNEXPECTED_ROWS_LABEL = 'Unexpected rows';

  /**
   * @var \Behat\Gherkin\Node\TableNode
   */
  protected $expected;

  /**
   * @var \Behat\Gherkin\Node\TableNode
   */
  protected $actual;

  /**
   * @var string
   */
  protected $missingRowsLabel = self::DEFAULT_MISSING_ROWS_LABEL;

  /**
   * @var string
   */
  protected $unexpectedRowsLabel = self::DEFAULT_UNEXPECTED_ROWS_LABEL;

  /**
   * @var array
   */
  protected $expectedHeader = [];

  /**
   * @var bool
   */
  protected $respectRowOrder = TRUE;

  /**
   * TableEqualityAssertion constructor.
   *
   * @param \Behat\Gherkin\Node\TableNode $expected
   * @param \Behat\Gherkin\Node\TableNode $actual
   */
  public function __construct(TableNode $expected, TableNode $actual) {
    $this->expected = $expected;
    $this->actual = $actual;
  }

  /**
   * @return \Behat\Gherkin\Node\TableNode
   */
  public function getExpected() {
    return $this->expected;
  }

  /**
   * @return \Behat\Gherkin\Node\TableNode
   */
  public function getActual() {
    return $this->actual;
  }

  /**
   * @return string
   */
  public function getMissingRowsLabel() {
    return $this->missingRowsLabel;
  }

  /**
   * @param string $label
   *
   * @return $this
   */
  public function setMissingRowsLabel($label) {
    assert(is_string($label), 'Missing rows label must be a string.');
    $this->missingRowsLabel = $label;
    return $this;
  }

  /**
   * @return string
   */
  public function getUnexpectedRowsLabel() {
    return $this->unexpectedRowsLabel;
  }

  /**
   * @param string $label
   *
   * @return $this
   */
  public function setUnexpectedRowsLabel($label) {
    assert(is_string($label), 'Unexpected rows label must be a string.');
    $this->unexpectedRowsLabel = $label;
    return $this;
  }

  /**
   * @return array
   */
  public function getExpectedHeader() {
    return $this->expectedHeader;
  }

  /**
   * @param array $header
   *
   * @return $this
   */
  public function expectHeader(array $header) {
    $this->expectedHeader = $header;
    return $this;
  }

  /**
   * @return $this
   */
  public function expectNoHeader() {
    $this->expectedHeader = [];
    return $this;
  }

  /**
   * @return bool
   */
  public function isRowOrderRespected() {
    return $this->respectRowOrder;
  }

  /**
   * @return $this
   */
  public function ignoreRowOrder() {
    $this->respectRowOrder = FALSE;
    return $this;
  }

  /**
   * @return $this
   */
  public function respectRowOrder() {
    $this->respectRowOrder = TRUE;
    return $this;
  }

  /**
   * Performs the assertion.
   *
   * @return true
   *
   * @throws \TravisCarden\BehatTableComparison\UnequalTablesException
   */
  public function assert() {
    $this->assertHeader();
    $this->assertBody();
    return TRUE;
  }

  /**
   * Asserts header expectations.
   */
  protected function assertHeader() {
    $expected_header = $this->getExpectedHeader();
    if (!$expected_header) {
      return;
    }

    $actual_header = $this->getExpected()->getRow(0);
    if ($expected_header === $actual_header) {
      return;
    }

    $message = [
      '--- Expected header',
      (new TableNode([$expected_header]))->getTableAsString(),
      '+++ Given',
      (new TableNode([$actual_header]))->getTableAsString(),
    ];
    throw new \LogicException(implode(PHP_EOL, $message));
  }

  /**
   * Asserts body expectations.
   */
  protected function assertBody() {
    $expected_body = $this->getExpectedBody();
    $actual_body = $this->getActual();

    if ($this->isRowOrderRespected()) {
      throw new \LogicException('Equality assertion respecting row order has not yet been implemented in this library. Use ignoreRowOrder() to ignore row order.');
    }

    $expected_body = $this->sortTable($expected_body);
    $actual_body = $this->sortTable($actual_body);

    if ($expected_body != $actual_body) {
      $message = $this->generateMessage($expected_body->getRows(), $actual_body->getRows());
      throw new UnequalTablesException($message);
    }
  }

  /**
   * @return \Behat\Gherkin\Node\TableNode
   */
  protected function getExpectedBody() {
    $body = $this->getExpected()->getRows();
    if ($this->getExpectedHeader()) {
      array_shift($body);
    }
    return new TableNode($body);
  }

  /**
   * @param \Behat\Gherkin\Node\TableNode $table
   *
   * @return \Behat\Gherkin\Node\TableNode
   */
  protected function sortTable(TableNode $table) {
    $raw_table = $table->getTable();
    sort($raw_table);
    return new TableNode($raw_table);
  }

  /**
   * @param array $expected_rows
   * @param array $actual_rows
   *
   * @return string
   */
  protected function generateMessage(array $expected_rows, array $actual_rows) {
    $message = [];
    $this->addArrayDiffMessageLines($message, $actual_rows, $expected_rows, '--- ' . $this->getMissingRowsLabel());
    $this->addArrayDiffMessageLines($message, $expected_rows, $actual_rows, '+++ ' . $this->getUnexpectedRowsLabel());
    return implode(PHP_EOL, $message);
  }

  /**
   * @param array $message
   * @param array $left
   * @param array $right
   * @param string $label
   */
  protected function addArrayDiffMessageLines(array &$message, array $left, array $right, $label) {
    $differences = array_filter($right, function (array $row) use ($left) {
      return !in_array($row, $left);
    });
    if ($differences) {
      $message[] = $label;
      $message[] = (new TableNode($differences))->getTableAsString();
    }
  }

}
