<?php

namespace Hurah\Generators\Util;

use DateTime;
use Hurah\Generators\Service\Service;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Type\Path;

class CodeWriter
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function write(Path $oFileName, string $sGeneratedCode): void
    {
        $this->service->getOutput()->writeln("File name: <info>{$oFileName}</info>");

        $sCode = $this->makeCodeString($sGeneratedCode);
        $oFileName->dirname()->makeDir();
        if ($oFileName->exists())
        {
            $this->service->getOutput()->writeln("File exists <error>{$oFileName}</error>");
            exit();
        }
        else
        {
            $this->service->getOutput()->writeln("Create: <info>{$oFileName}</info>");
        }
        $oFileName->write($sCode);
    }

    /**
     * @param string $sGeneratedCode
     *
     * @return string
     */
    private function makeCodeString(string $sGeneratedCode): string
    {
        $oDateTime = new DateTime();
        $sDateTime = $oDateTime->format('Y-m-d H:i:s');
        $aCode = [
            '<?php',
            "/*",
            "Generated: {$sDateTime} ",
        ];
        foreach ($_SERVER['argv'] as $item => $value)
        {
            $aCode[] = "\$argv[$item] = $value";
        }
        $aCode[] = "*/";
        $aCode[] = $sGeneratedCode;
        return join(PHP_EOL, $aCode);
    }
}
