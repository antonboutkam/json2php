<?php

namespace Hurah\Generators\Service;

use Hurah\Generators\Util\CodeWriter;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Path;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Service
{
    private OutputInterface $output;
    private InputInterface $input;
    private CodeWriter $writer;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->writer = new CodeWriter($this);
    }
    /**
     * @throws InvalidArgumentException
     */
    public function getWriter():CodeWriter
    {
        return $this->writer;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCwd():Path
    {
        return Path::make(getcwd());
    }

    public function getOutput():OutputInterface
    {
        return $this->output;
    }

    public function getInput():InputInterface
    {
        return $this->input;
    }
}