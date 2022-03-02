<?php

namespace Hurah\Generators\Command\Json2Php\Types;

use Hurah\Types\Exception\NullPointerException;
use Hurah\Types\Type\AbstractCollectionDataType;
use Nette\PhpGenerator\PhpNamespace as PhpNamespaceType;

class NamespaceCollection extends AbstractCollectionDataType
{
    public function make($sNamespaceName)
    {
        $oNamespaceType = new PhpNamespaceType($sNamespaceName);
        $this->add($oNamespaceType);
        return $oNamespaceType;
    }
    public function getNamespace(string $sNamespaceName):PhpNamespaceType
    {
        foreach($this as $oNamespace)
        {
            if($oNamespace->getName() === $sNamespaceName)
            {
                return $oNamespace;
            }
        }
        throw new NullPointerException("Cannot find namespace $sNamespaceName");
    }

    public function hasNamespace(string $sNamespaceName): bool
    {
        foreach($this as $oNamespace)
        {
            if($oNamespace->getName() === $sNamespaceName)
            {
                return true;
            }
        }
        return false;
    }
    public function add(PhpNamespaceType $oNamespace)
    {
        $this->array[] = $oNamespace;
    }

    public function current(): PhpNamespaceType
    {
        return $this->array[$this->position];
    }
}