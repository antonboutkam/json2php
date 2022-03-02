<?php

namespace Hurah\Generators\Command\Collection\Generator;

use Hurah\Generators\Service\Service;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as PhpGenerator;

class ItemGenerator extends AbstractGenerator
{
    public function __construct(Service $oService)
    {
        $this->service = $oService;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generate(PhpGenerator $oGeneratorNamespace, PhpNamespace $oBaseNamespace): void
    {
        $oClass = $oGeneratorNamespace->addClass($oBaseNamespace->getShortName() . 'Item');
        $this->addConstructor($oClass);
        $this->addFromArray($oClass);
        $this->saveToDisk($oGeneratorNamespace);
    }

    private function addFromArray(ClassType $oClass): void
    {
        $oFromArray = $oClass->addMethod('fromArray');
        $oFromArray->setPublic();
        $oFromArray->setStatic();
        $oFromArray->setReturnType('self');
        $oFromArray->setBody(join(PHP_EOL, $this->fromArrayBody()));
    }

    private function addConstructor(ClassType $oClass): void
    {
        $oConstructor = $oClass->addMethod('__construct');
        $oConstructor->setPublic();
    }

    private function fromArrayBody(): array
    {
        return [
            '$oNew = new self();',
            'return $oNew;',
        ];

    }
}
