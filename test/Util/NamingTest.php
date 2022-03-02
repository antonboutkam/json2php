<?php

use Hurah\Generators\Util\Naming;

class NamingTest extends \PHPUnit\Framework\TestCase
{

    public function testClassName()
    {
        $result = Naming::className('input.json');
        $this->assertEquals('InputJson', $result, $result);

        $result = Naming::className('CLoud9!@Eleven');
        $this->assertEquals('CLoud9Eleven', $result, $result);
    }


}