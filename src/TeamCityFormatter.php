<?php

namespace Behat\TeamCityFormatter;


use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\BeforeFeatureTested;
use Behat\Behat\EventDispatcher\Event\BeforeOutlineTested;
use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\EventDispatcher\Event\AfterSuiteTested;
use Behat\Testwork\EventDispatcher\Event\BeforeSuiteTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Output\Printer\OutputPrinter;
use Behat\Testwork\Tester\Result\ExceptionResult;
use Behat\Testwork\Tester\Result\TestResult;

class TeamCityFormatter implements Formatter
{
    private static $REPLACEMENTS = array(
        "|"  => "||",
        "'"  => "|'",
        "\n" => "|n",
        "\r" => "|r",
        "["  => "|[",
        "]"  => "|]",
    );

    /**
     * @var OutputPrinter
     */
    protected $printer;

    /** @var CallResult|null */
    private $failedStep;

    /**
     * TeamCityFormatter constructor.
     * 
     * @param OutputPrinter $printer
     */
    public function __construct(OutputPrinter $printer)
    {
        $this->printer = $printer;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            SuiteTested::BEFORE    => 'onBeforeSuiteTested',
            SuiteTested::AFTER     => 'onAfterSuiteTested',
            FeatureTested::BEFORE  => 'onBeforeFeatureTested',
            FeatureTested::AFTER   => 'onAfterFeatureTested',
            ScenarioTested::BEFORE => 'onBeforeScenarioTested',
            ScenarioTested::AFTER  => 'onAfterScenarioTested',
            OutlineTested::BEFORE  => 'onBeforeOutlineTested',
            OutlineTested::AFTER   => 'onAfterOutlineTested',
            StepTested::AFTER      => 'saveFailedStep',
        );
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return 'teamcity';
    }

    /**
     * Returns formatter description.
     *
     * @return string
     */
    public function getDescription()
    {
    }

    /**
     * Sets formatter parameter.
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParameter($name, $value)
    {
    }

    /**
     * Returns parameter name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
    }

    /**
     * Returns formatter output printer.
     *
     * @return OutputPrinter
     */
    public function getOutputPrinter()
    {
        return $this->printer;
    }

    public function saveFailedStep(AfterStepTested $event)
    {
        $result = $event->getTestResult();

        if (TestResult::FAILED === $result->getResultCode()) {
            $this->failedStep = $result;
        }
    }

    public function onBeforeSuiteTested(BeforeSuiteTested $event)
    {
        $this->writeServiceMessage('testSuiteStarted', array('name' => $event->getSuite()->getName()));
    }

    public function onAfterSuiteTested(AfterSuiteTested $event)
    {
        $this->writeServiceMessage('testSuiteFinished', array('name' => $event->getSuite()->getName()));
    }

    /**
     * @param BeforeFeatureTested $event
     */
    public function onBeforeFeatureTested(BeforeFeatureTested $event)
    {
        $this->writeServiceMessage('testSuiteStarted', array('name' => $event->getFeature()->getTitle()));
    }

    public function onAfterFeatureTested(AfterFeatureTested $event)
    {
        $this->writeServiceMessage('testSuiteFinished', array('name' => $event->getFeature()->getTitle()));
    }

    public function onBeforeScenarioTested(BeforeScenarioTested $event)
    {
        $this->beforeTest($event->getScenario());
    }

    public function onAfterScenarioTested(AfterScenarioTested $event)
    {
        $this->afterTest($event->getTestResult(), $event->getScenario());
    }

    public function onBeforeOutlineTested(BeforeOutlineTested $event)
    {
        $this->beforeTest($event->getOutline());
    }

    public function onAfterOutlineTested(AfterOutlineTested $event)
    {
        $this->afterTest($event->getTestResult(), $event->getOutline());
    }

    private function beforeTest(ScenarioLikeInterface $scenario)
    {
        $this->failedStep = null;
        $this->writeServiceMessage('testStarted', array('name' => $scenario->getTitle()));
    }

    private function afterTest(TestResult $result, ScenarioLikeInterface $scenario)
    {
        $params = array('name' => $scenario->getTitle());

        switch ($result->getResultCode()) {
            case TestResult::SKIPPED:
                #$this->writeServiceMessage('testIgnored', $params);
                #return;
                break;
            case TestResult::PASSED:
                break;
            case TestResult::FAILED:
                $failedParams = $params;

                if ($this->failedStep && $this->failedStep->hasException()) {
                    switch (true) {
                        case ($this->failedStep instanceof ExceptionResult && $this->failedStep->hasException()):
                            $exception = $this->failedStep->getException();
                            $failedParams['message'] = $exception->getMessage();

                            break;
                        default:
                            $failedParams['message'] = sprintf("Unknown error in ", get_class($this->failedStep));
                            break;
                    }

                    #$failedParams['details'] = $this->sanitizeExceptionStack($exception);
                }

                $this->writeServiceMessage('testFailed', $failedParams);

                break;
        }

        $this->writeServiceMessage('testFinished', $params);
    }

    private function writeServiceMessage($messageKey, array $params = array())
    {
        $message = '';
        $search  = array_keys(self::$REPLACEMENTS);
        $replace = array_values(self::$REPLACEMENTS);

        foreach ($params as $key => $value) {
            $value    = str_replace($search, $replace, $value);
            $message .= sprintf(" %s='%s'", $key, $value);
        }

        $message = sprintf("##teamcity[%s %s]", $messageKey, trim($message));

        $this->printer->writeln($message);
    }

    private function sanitizeExceptionStack(\Exception $exception)
    {
        $trace = $exception->getTraceAsString();
        $trace = str_replace(
            array_keys(self::$REPLACEMENTS),
            array_values(self::$REPLACEMENTS),
            $trace
        );

        return $trace;
    }
}
