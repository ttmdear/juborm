<?php
namespace Juborm\Assistant;

use Juborm\ORM as ORM;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base Command class for Dbcompar.
 */
class Command extends ConsoleCommand
{
    public static function service($name)
    {
        return ORM::service($name);
    }

    protected function configure()
    {
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_OPTIONAL,
            'The path to the configuration file.',
            './juborm.xml'
        );

        $this->addOption(
            'env',
            'e',
            InputOption::VALUE_OPTIONAL,
            'Environment for which the script is run.',
            'development'
        );

        $this->addOption(
            'model',
            'm',
            InputOption::VALUE_REQUIRED,
            'Model for which the script is run.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $input->getOption('config');
        $env = $input->getOption('env');

        ORM::service('config')->load(realpath($config), $env);
    }
}
