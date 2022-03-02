<?php

namespace Hurah\Generators\Command;

use Hurah\Generators\Command\Json2Php\Generator\Main;
use Hurah\Generators\Service\Service;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Json2PhpCommand extends Command
{
    public function configure()
    {
        $this->setName('make:json2php');
        $this->setDescription("Generate biolerplate classes from input json, classes will be generated on the spot (cwd)");
        $this->setHelp("run generate <namespace> <file> and the script will do the rest");
        $this->addArgument('namespace', InputArgument::REQUIRED, 'Name of the file that contains the json');
        $this->addArgument('filename', InputArgument::REQUIRED, 'Name of the file that contains the json');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sFileName = $input->getArgument('filename');
        $sNamespace = $input->getArgument('namespace');

        $oInputFile = Path::make($sFileName);
        $oNamespace = new PhpNamespace($sNamespace);

        $oService = new Service($input, $output);

        $oMain = new Main($oNamespace, $oInputFile, $oService);
        $oMain->generate();
        return Command::SUCCESS;
    }


}