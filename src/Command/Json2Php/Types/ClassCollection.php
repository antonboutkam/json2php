<?php

namespace Hurah\Generators\Command\Json2Php\Types;

use Hurah\Types\Type\AbstractCollectionDataType;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class ClassCollection extends AbstractCollectionDataType
{

    public function getOrCreate(PhpNamespace $oNamespace, string $sClassName)
    {
        foreach($this as $oClassType)
        {
            if($oClassType->getName() == $sClassName)
            {
                return $oClassType;
            }
        }
        $oClassNamespace = new PhpNamespace($oNamespace->getName());
        $oClassType = $oClassNamespace->addClass($sClassName);
        $this->add($oClassType);

        return $oClassType;
    }
    public function add(ClassType $oClass)
    {
        $this->array[] = $oClass;
    }

    public function current(): ClassType
    {
        return $this->array[$this->position];
    }
}