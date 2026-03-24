Feature: Behat integration coverage
  In order to validate real Behat consumer workflows
  As a library maintainer
  I want representative end-to-end scenarios

  Scenario: Equal tables pass with header and ignored row order
    Given the expected table is:
      | Frodo | Hobbit |
      | Sam   | Hobbit |
    And the actual table is:
      | Sam   | Hobbit |
      | Frodo | Hobbit |
    And row order is ignored
    When I compare the tables
    Then the comparison should pass

  Scenario: Duplicate mismatch shows semantic duplicate section
    Given the expected table is:
      | id |
      | 1  |
      | 2  |
    And the actual table is:
      | id |
      | 1  |
      | 2  |
      | 2  |
    And row order is ignored
    And the expected header is:
      | id |
    When I compare the tables
    Then the comparison should fail
    And the error message should contain:
      """
      *** Duplicate rows
      """

  Scenario: Row-order mismatch shows position diagnostics and order blocks
    Given the expected table is:
      | 1  |
      | 2  |
      | 3  |
    And the actual table is:
      | 2  |
      | 1  |
      | 3  |
    And row order is respected
    When I compare the tables
    Then the comparison should fail
    And the error message should contain:
      """
      *** Row order mismatch
      """
    And the error message should contain:
      """
      should be at position
      """
    And the error message should contain:
      """
      Expected order
      """
    And the error message should contain:
      """
      Actual order
      """

  Scenario: Header mismatch uses expected and given header labels
    Given the expected table is:
      | Label one | id1 |
      | Label two | id2 |
    And the actual table is:
      | Label one | id1 |
      | Label two | id2 |
    And the expected header is:
      | label | id |
    When I compare the tables
    Then the comparison should fail
    And the error message should contain:
      """
      --- Expected header
      """
    And the error message should contain:
      """
      +++ Given header
      """

  Scenario: Custom labels are reflected in integration error output
    Given the expected table is:
      | id |
      | 1  |
      | 2  |
    And the actual table is:
      | id |
      | 1  |
      | 2  |
      | 2  |
    And row order is ignored
    And the expected header is:
      | id |
    And the "duplicate rows" label is "Duplicate characters"
    When I compare the tables
    Then the comparison should fail
    And the error message should contain:
      """
      *** Duplicate characters
      """

  Scenario: Comprehensive kitchen sink with all difference types and custom labels
    Given the expected table is:
      | id | name   |
      | 1  | one    |
      | 2  | two    |
      | 3  | three  |
      | 4  | four   |
      | 5  | five   |
      | 6  | six    |
      | 7  | seven  |
      | 8  | eight  |
      | 9  | nine   |
      | 10 | ten    |
    And the actual table is:
      | id | name     |
      | 1  | one      |
      | 2  | two      |
      | 2  | two      |
      | 3  | three    |
      | 4  | four     |
      | 6  | six      |
      | 7  | seven    |
      | 8  | changed  |
      | 9  | nine     |
      | 10 | ten      |
      | 13 | thirteen |
    And row order is respected
    And the "missing rows" label is "Missing items"
    And the "unexpected rows" label is "Unexpected items"
    And the "duplicate rows" label is "Duplicate items"
    When I compare the tables
    Then the comparison should fail
    And the error message should contain the full output:
      """
      --- Missing items
      | 5 | five  |
      | 8 | eight |
      +++ Unexpected items
      | 8  | changed  |
      | 13 | thirteen |
      *** Duplicate items
      | 2 | two | (appears 2 times, expected 1)
      Expected order
      | id | name  |
      | 1  | one   |
      | 2  | two   |
      | 3  | three |
      | 4  | four  |
      | 5  | five  |
      | 6  | six   |
      | 7  | seven |
      | 8  | eight |
      | 9  | nine  |
      | 10 | ten   |
      Actual order
      | id | name     |
      | 1  | one      |
      | 2  | two      |
      | 2  | two      |
      | 3  | three    |
      | 4  | four     |
      | 6  | six      |
      | 7  | seven    |
      | 8  | changed  |
      | 9  | nine     |
      | 10 | ten      |
      | 13 | thirteen |
      """
