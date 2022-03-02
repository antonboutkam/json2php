<?php

namespace Hurah\Generators\Command\Collection\Generator;

use Hurah\Generators\Service\Service;
use Hurah\Types\Type\AbstractCollectionDataType;
use Hurah\Types\Type\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace as PhpGenerator;

class CollectionGenerator extends AbstractGenerator
{
    public function __construct(Service $oService)
    {
        $this->service = $oService;
    }

    public function generate(PhpGenerator $oGeneratorNamespace, PhpNamespace $oBaseNamespace):void
    {
        $oGeneratorNamespace->addUse(AbstractCollectionDataType::class);
        $sCollectionName = "{$oBaseNamespace->getShortName()}Collection";
        $oClass = $oGeneratorNamespace->addClass($sCollectionName);
        $oClass->setExtends(AbstractCollectionDataType::class);
        $this->addCurrentMethod($oClass, $oBaseNamespace);
        $this->addAddMethod($oClass, $oBaseNamespace);

        $this->saveToDisk($oGeneratorNamespace);
    }
    public function addAddMethod(ClassType $oClass, PhpNamespace $oBaseNamespace):void
    {
        $oCurrentMethod = $oClass->addMethod('add');
        $oCurrentMethod->setReturnType("void");
        $oItemParameter = $oCurrentMethod->addParameter('item');
        $oItemParameter->setType("{$oBaseNamespace}");
        $oCurrentMethod->setBody($this->getAddBody());
    }

    public function addCurrentMethod(ClassType $oClass, PhpNamespace $oBaseNamespace):void
    {
        $oCurrentMethod = $oClass->addMethod('current');
        $oCurrentMethod->setReturnType("{$oBaseNamespace}");
        $oCurrentMethod->setBody($this->getCurrentBody());

    }
    public function getAddBody():string
    {
        $aReturn = [
            '$this->array[] = $item;'
        ];
        return join(PHP_EOL, $aReturn);
    }
    public function getCurrentBody():string
    {
        $aReturn = [
            'return $this->array[$this->position];'
        ];
        return join(PHP_EOL, $aReturn);
    }

}