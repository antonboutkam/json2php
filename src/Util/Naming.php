<?php

namespace Hurah\Generators\Util;

class Naming
{
    public static function className(string $sSomeInput, string $sSuffix = null):string
    {
        $sAlphaNumericInput = preg_replace('/[^\da-z]/i', ' ', $sSomeInput);
        $sCamelCasedAlphaNumericInput = ucwords($sAlphaNumericInput);
        $sResult = preg_replace('/[\s]+/', '', $sCamelCasedAlphaNumericInput);

        if($sSuffix)
        {
            $sResult = $sResult . $sSuffix;
        }
        return $sResult;
    }
    public static function propertyName(string $sSomeInput, string $sPrefix = null):string
    {
        return self::methodName($sSomeInput, $sPrefix);
    }
    public static function methodName(string $sSomeInput, string $sPrefix = null):string
    {
        $sAsClassName = self::className($sSomeInput);

        if($sPrefix)
        {
            return $sPrefix . $sAsClassName;
        }
        return lcfirst($sAsClassName);
    }
}