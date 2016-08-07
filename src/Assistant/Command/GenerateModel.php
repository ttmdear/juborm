<?php
namespace Juborm\Assistant\Command;

use Juborm\Assistant\Command;
use Juborm\Assistant\Operation\Model as ModelOperation;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModel extends Command
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('generateModel')
            ->setDescription('Generate model files for specific model.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $modelOperation = new ModelOperation();
        $modelOperation->setModel($input->getOption('model'));
        $modelOperation->generate();
    }
}
