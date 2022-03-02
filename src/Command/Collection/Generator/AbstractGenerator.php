<?php

namespace Hurah\Generators\Command\Collection\Generator;

use Hurah\Generators\Service\Service;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as PhpGenerator;

abstract class AbstractGenerator
{
    protected Service $service;

    abstract function generate(PhpGenerator $oGeneratorNamespace, PhpNamespace $oBaseNamespace): void;

    /**
     * @throws InvalidArgumentException
     */
    protected function saveToDisk(\Nette\PhpGenerator\PhpNamespace $oNamespace): void
    {
        foreach($oNamespace->getClasses() as $oClass)
        {
            $sFileName = "{$oClass->getName()}.php";
            $oDestination = $this->service->getCwd()->extend($sFileName);

            $this->service->getOutput()->writeln("File <info>{$sFileName}</info>");
            $this->service->getOutput()->writeln("Pathname <info>{$oDestination}</info>");
            $this->service->getWriter()->write($oDestination, $oNamespace);
        }

    }
}