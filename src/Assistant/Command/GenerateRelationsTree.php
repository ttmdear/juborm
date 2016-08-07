<?php
namespace Juborm\Assistant\Command;

use Juborm\Assistant\Command;
use Juborm\Assistant\Operation\RelationsTree;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRelationsTree extends Command
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('generateRelationsTree')
            ->setDescription('Generate relations files for specific model.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $modelOperation = new RelationsTree();
        $modelOperation->setModel($input->getOption('model'));
        $modelOperation->generate();
    }
}
