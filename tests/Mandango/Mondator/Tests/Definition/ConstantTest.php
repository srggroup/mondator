<?php

namespace Mandango\Mondator\Tests\Definition;

use Mandango\Mondator\Definition\Constant;

class ConstantTest extends \PHPUnit_Framework_TestCase
{
    const IRRELEVANT_NAME = 'X';
    const IRRELEVANT_VALUE = 'X';

    public function testConstructor()
    {
        $constant = new Constant(self::IRRELEVANT_NAME, self::IRRELEVANT_VALUE);

        $this->assertEquals($constant->getName(), self::IRRELEVANT_NAME);
        $this->assertEquals($constant->getValue(), self::IRRELEVANT_VALUE);
    }
}