<?php

namespace Hurah\Generators\Util;

class Collection
{

    public static function isAssoc(array $array):bool
    {
        if(array_keys($array) !== range(0, count($array) - 1))
        {
            return true;
        }
        return false;
    }
}