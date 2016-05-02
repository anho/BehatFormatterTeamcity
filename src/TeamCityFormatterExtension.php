<?php

namespace Behat\TeamCityFormatter;

use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TeamCityFormatterExtension implements Extension
{

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * @inheritdoc
     */
    public function getConfigKey()
    {
        return 'teamcity';
    }

    /**
     * @inheritdoc
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        #$extensionManager->getExtension('formatters');
    }

    /**
     * @inheritdoc
     */
    public function configure(ArrayNodeDefinition $builder)
    {
    }

    /**
     * @inheritdoc
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $outputDefinition = new Reference('cli.output');
        $outputPrinterDefinition = new Definition('Behat\\TeamCityFormatter\\ConsoleOutput', array($outputDefinition));

        $definition = new Definition("Behat\\TeamCityFormatter\\TeamCityFormatter", array($outputPrinterDefinition));
        $definition->addTag(OutputExtension::FORMATTER_TAG, array('priority' => 90));

        $container->setDefinition(OutputExtension::FORMATTER_TAG . '.teamcity', $definition);
    }
}