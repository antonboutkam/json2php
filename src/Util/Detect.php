<?php

namespace Hurah\Generators\Util;

class Detect
{

    public static function type($mValue):string
    {
        if(is_float($mValue))
        {
            return 'double';
        }
        elseif(is_int($mValue))
        {
            return 'int';
        }
        elseif(is_bool($mValue))
        {
            return 'bool';
        }
        elseif(is_numeric($mValue))
        {
            return 'float';
        }
        elseif(is_string($mValue))
        {
            return 'string';
        }
        return 'void';
    }
}