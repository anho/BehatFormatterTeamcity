<?php

namespace Behat\TeamCityFormatter;

use Behat\Testwork\Output\Printer\OutputPrinter;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleOutput
 * 
 * This class is responsible for displaying the formatted text on the console. 
 *
 * @package Behat\TeamCityFormatter
 *
 * @author Geza Buza <bghome@gmail.com>
 */
class ConsoleOutput implements OutputPrinter
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $styles;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Sets output path.
     *
     * @param string $path
     */
    public function setOutputPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns output path.
     *
     * @return null|string
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function getOutputPath()
    {
        return $this->path;
    }

    /**
     * Sets output styles.
     *
     * @param array $styles
     */
    public function setOutputStyles(array $styles)
    {
        $this->styles = $styles;
    }

    /**
     * Returns output styles.
     *
     * @return array
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function getOutputStyles()
    {
        return $this->styles;
    }

    /**
     * Forces output to be decorated.
     *
     * @param Boolean $decorated
     */
    public function setOutputDecorated($decorated)
    {
        $this->output->setDecorated($decorated);
    }

    /**
     * Returns output decoration status.
     *
     * @return null|Boolean
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function isOutputDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * Sets output verbosity level.
     *
     * @param integer $level
     */
    public function setOutputVerbosity($level)
    {
        $this->output->setVerbosity($level);
    }

    /**
     * Returns output verbosity level.
     *
     * @return integer
     *
     * @deprecated since 3.1, to be removed in 4.0
     */
    public function getOutputVerbosity()
    {
        return $this->output->getVerbosity();
    }

    /**
     * Writes message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function write($messages)
    {
        $this->output->write($messages);
    }

    /**
     * Writes newlined message(s) to output stream.
     *
     * @param string|array $messages message or array of messages
     */
    public function writeln($messages = '')
    {
        $this->output->writeln($messages);
    }

    /**
     * Clear output stream, so on next write formatter will need to init (create) it again.
     */
    public function flush()
    {
    }

}