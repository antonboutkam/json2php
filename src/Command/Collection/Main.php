<?php

namespace Hurah\Generators\Command\Collection;

use Hurah\Generators\Command\Collection\Generator\CollectionGenerator;
use Hurah\Generators\Command\Collection\Generator\ItemGenerator;
use Hurah\Generators\Service\Service;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\PhpNamespace;
use Nette\PhpGenerator\PhpNamespace as NettePhpGenerator;
use ReflectionException;

class Main
{
    private Service $service;

    public function __construct(Service $oService)
    {
        $this->service = $oService;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function create(string $sBaseNamespace)
    {
        $oBaseNamespace = new PhpNamespace($sBaseNamespace);

        $oGenerator = $this->createCollection($oBaseNamespace);

        $this->createItem($oBaseNamespace);


    }

    /**
     * @param PhpNamespace $oBaseNamespace
     *
     * @return NettePhpGenerator
     */
    private function createCollection(PhpNamespace $oBaseNamespace): void
    {
        $oGenerator = new NettePhpGenerator($oBaseNamespace);
        $this->service->getOutput()->writeln("Creating <info>collection</info> class");
        $oCollectionGenerator = new CollectionGenerator($this->service);
        $oCollectionGenerator->generate($oGenerator, $oBaseNamespace);
    }

    /**
     * @param PhpNamespace $oBaseNamespace
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function createItem(PhpNamespace $oBaseNamespace): void
    {
        $oGenerator = new NettePhpGenerator($oBaseNamespace);
        $this->service->getOutput()->writeln("Creating <info>item</info> class");
        $oItemGenerator = new ItemGenerator($this->service);
        $oItemGenerator->generate($oGenerator, $oBaseNamespace);
    }
}