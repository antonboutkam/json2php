<?php

namespace Hurah\Generators\Command;

use Hurah\Generators\Command\Collection\Main;
use Hurah\Generators\Service\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectionCommand extends Command
{
    public function configure()
    {
        $this->setName('make:collection');
        $this->setDescription("Generate collection + value object set");
        $this->setHelp("run generate <namespace> <file> and the script will do the rest");
        $this->addArgument('fqn', InputArgument::REQUIRED, 'Fully qualified class name of the item class. eg: \\Hurah\\Bladieda\\MyClass');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sFqn = $input->getArgument('fqn');

        $oService = new Service($input, $output);

        $oMain = new Main($oService);
        $oService->getOutput()->writeln("Input <info>$sFqn</info>");
        $oMain->create($sFqn);
        return Command::SUCCESS;
    }


}