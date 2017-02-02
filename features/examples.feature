Feature: Behat Table Comparison Examples
  In order to demonstrate use of the library
  As a library developer
  I want to include working examples

  Scenario: Compare lists (one dimensional arrays)
    Given I am "J. R. R. Tolkien"
    Then I should include the following "books" in "The Lord of the Rings series"
      | The Fellowship of the Ring |
      | The Two Towers             |
      | The Return of the King     |

  Scenario: Compare full tables (two dimensional arrays)
    Given I am "J. R. R. Tolkien"
    And I am writing "The Fellowship of the Ring"
    Then I should include the following "characters" in "the Company of the Ring"
      | name                        | race   |
      | Frodo Baggins               | Hobbit |
      | Samwise "Sam" Gamgee        | Hobbit |
      | Gandalf the Grey            | Wizard |
      | Legolas                     | Elf    |
      | Gimli                       | Dwarf  |
      | Aragorn (Strider)           | Man    |
      | Boromir                     | Man    |
      | Meriadoc "Merry" Brandybuck | Hobbit |
      | Peregrin "Pippin" Took      | Hobbit |
