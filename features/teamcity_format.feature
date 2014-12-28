Feature: Teamcity Formatter
  In order to gather information about tests
  As a teamcity server
  I need to have teamcity formatter

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
    <?php

      use Behat\Behat\Context\Context,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          private $value;

          /**
           * @Given I have entered :num
           */
          public function iHaveEntered($num) {
              $this->value = $num;
          }

          /**
           * @Then I must have :num
           */
          public function iMustHave($num) {
              PHPUnit_Framework_Assert::assertEquals($num, $this->value);
          }

          /**
           * @When I add :num
           */
          public function iAdd($num) {
              $this->value += $num;
          }

          /**
           * @When something not done yet
           */
          public function somethingNotDoneYet() {
              throw new PendingException();
          }
      }
    """
    And a file named "behat.yml" with:
    """
    default:
      extensions:
        Behat\TeamCityFormatter\TeamCityFormatterExtension: ~
    """

  Scenario: Failed test
    Given a file named "features/failed.feature" with:
    """
    Feature: Failed
      Scenario: Add
        Given I have entered 2
        When I add 2
        Then I must have 5
    """
    When I run "behat --no-colors -f teamcity"
    Then it should fail with:
    """
    ##teamcity[testSuiteStarted name='Failed']
    ##teamcity[testStarted name='Add']
    ##teamcity[testFailed name='Add' message='Failed asserting that 4 matches expected |'5|'.']
    ##teamcity[testFinished name='Add']
    ##teamcity[testSuiteFinished name='Failed']
    """

  Scenario: Simple feature
    Given a file named "features/simple.feature" with:
    """
    Feature: Simple
      Scenario: Add
        Given I have entered 2
        When I add 2
        Then I must have 4
    """
    When I run "behat --no-colors -f teamcity"
    Then it should pass with:
    """
    ##teamcity[testSuiteStarted name='Simple']
    ##teamcity[testStarted name='Add']
    ##teamcity[testFinished name='Add']
    ##teamcity[testSuiteFinished name='Simple']
    """

  Scenario: With background
    Given a file named "features/background.feature" with:
    """
    Feature: With background
      Background:
        Given I have entered 2

      Scenario: Add 2
        When I add 2
        Then I must have 4

      Scenario: Add 3
        When I add 3
        Then I must have 5
    """
    When I run "behat --no-colors -f teamcity"
    Then it should pass with:
    """
    ##teamcity[testSuiteStarted name='With background']
    ##teamcity[testStarted name='Add 2']
    ##teamcity[testFinished name='Add 2']
    ##teamcity[testStarted name='Add 3']
    ##teamcity[testFinished name='Add 3']
    ##teamcity[testSuiteFinished name='With background']
    """

  Scenario: With outline and examples
    Given a file named "features/outline-and-examples.feature" with:
    """
    Feature: With outline and examples

      Scenario Outline: Add
        Given I have entered 2
        When I add <num>
        Then I must have <total>

      Examples:
        | num | total |
        | 2   | 4     |
        | 3   | 5     |
    """
    When I run "behat --no-colors -f teamcity"
    Then it should pass with:
    """
    ##teamcity[testSuiteStarted name='With outline and examples']
    ##teamcity[testStarted name='Add']
    ##teamcity[testFinished name='Add']
    ##teamcity[testSuiteFinished name='With outline and examples']
    """

  Scenario: Complex
    Given a file named "features/complex.feature" with:
    """
    Feature: Complex
      Background:
        Given I have entered 2

      Scenario Outline: Add
        When I add <num>
        Then I must have <total>

      Examples:
        | num | total |
        | 2   | 4     |
        | 3   | 5     |

      Scenario Outline: Add more
        When I add <num1>
        When I add <num2>
        Then I must have <total>

      Examples:
        | num1 | num2 | total |
        | 1    | 1    | 4     |
        | 1    | 2    | 5     |

    """
    When I run "behat --no-colors -f teamcity"
    Then it should pass with:
    """
    ##teamcity[testSuiteStarted name='Complex']
    ##teamcity[testStarted name='Add']
    ##teamcity[testFinished name='Add']
    ##teamcity[testStarted name='Add more']
    ##teamcity[testFinished name='Add more']
    ##teamcity[testSuiteFinished name='Complex']
    """

  Scenario: Escape special characters
    Given a file named "features/escape.feature" with:
    """
    Feature: Escape '[]|
      Scenario: Escape chars '[]|
        Given I have entered 1
        When I add 1
        Then I must have 2
    """
    When I run "behat --no-colors -f teamcity"
    Then it should pass with:
    """
    ##teamcity[testSuiteStarted name='Escape |'|[|]||']
    ##teamcity[testStarted name='Escape chars |'|[|]||']
    ##teamcity[testFinished name='Escape chars |'|[|]||']
    ##teamcity[testSuiteFinished name='Escape |'|[|]||']
    """

  Scenario: Use a suite
    Given a file named "behat.yml" with:
    """
    default:
      extensions:
        Behat\TeamCityFormatter\TeamCityFormatterExtension: ~
      suites:
        my_suite:
          paths:
            - %paths.base%/features
    """
    And a file named "features/in-suite.feature" with:
    """
    Feature: Simple
      Scenario: Add
        Given I have entered 2
        When I add 2
        Then I must have 4

    """
    When I run "behat --no-colors -f teamcity -s my_suite"
    Then it should pass with:
    """
    ##teamcity[testSuiteStarted name='my_suite']
    ##teamcity[testSuiteStarted name='Simple']
    ##teamcity[testStarted name='Add']
    ##teamcity[testFinished name='Add']
    ##teamcity[testSuiteFinished name='Simple']
    ##teamcity[testSuiteFinished name='my_suite']
    """
