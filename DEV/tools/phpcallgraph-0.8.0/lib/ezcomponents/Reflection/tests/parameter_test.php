<?php
/**
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 * @filesource
 * @package Reflection
 * @subpackage Tests
 */

class ezcReflectionParameterTest extends ezcTestCase
{
    public function testGetType() {
        $func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();
        $type = $params[0]->getType();
        self::assertType('ezcReflectionType', $type);
        self::assertEquals('test', $params[0]->getName());
        self::assertEquals('string', $type->toString());

        $method = new ezcReflectionMethod('TestMethods', 'm3');
        $params = $method->getParameters();
        self::assertNull($params[0]->getType());
    }

    public function testGetClass() {
        $func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();

        $type = $params[1]->getClass();
        self::assertType('ezcReflectionType', $type);
        self::assertEquals('test2', $params[1]->getName());
        self::assertEquals('ezcReflectionApi', $type->toString());

        //@TODO: fix this error
        //fix or change documentation of handling of not existing classes
        //with the type system, at the moment, type with name with empty string is
        //returned, this is wrong and has to be fixed.
        $type = $params[2]->getClass();
        self::assertType('ezcReflectionType', $type);
        self::assertEquals('test3', $params[2]->getName());
        self::assertEquals('NonExistingType', $type->toString());

        $method = new ezcReflectionMethod('TestMethods', 'm3');
        $params = $method->getParameters();

        self::assertNull($params[0]->getClass());
    }

    public function testGetDeclaringFunction() {
        $func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();

		$decFunc = $params[0]->getDeclaringFunction();
		self::assertTrue($decFunc instanceof ezcReflectionFunction);
        self::assertEquals('m1', $decFunc->getName());
    }

    public function testGetDeclaringClass() {
        $method = new ezcReflectionMethod('TestMethods', 'm3');
        $params = $method->getParameters();

        $class = $params[0]->getDeclaringClass();
		self::assertTrue($class instanceof ezcReflectionClass);
        self::assertEquals('TestMethods', $class->getName());
    }

    public function testGetName() {
		$func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();
		self::assertEquals('test', $params[0]->getName());
	}

    public function testIsPassedByReference() {
		$func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();
		self::assertFalse($params[0]->isPassedByReference());
		self::assertTrue($params[2]->isPassedByReference());
	}

    public function testIsArray() {
		$func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();
		self::assertFalse($params[0]->isArray());
	}

    public function testAllowsNull() {
		$func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();
		self::assertTrue($params[0]->allowsNull());
	}

    public function testIsOptional() {
		$func = new ezcReflectionFunction('mmm');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertTrue($param->isOptional());

		$func = new ezcReflectionFunction('m1');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertFalse($param->isOptional());
	}

	public function testIsDefaultValueAvailable() {
		$func = new ezcReflectionFunction('mmm');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertTrue($param->isDefaultValueAvailable());

		$func = new ezcReflectionFunction('m1');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertFalse($param->isDefaultValueAvailable());
	}

	/**
	* @expectedException ReflectionException
	*/
	public function testGetDefaultValue() {
		$func = new ezcReflectionFunction('mmm');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertEquals('foo', $param->getDefaultValue());

		$func = new ezcReflectionFunction('m1');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertEquals(null, $param->getDefaultValue()); //should throw exception
	}

	public function testGetPosition() {
		$func = new ezcReflectionFunction('mmm');
		$param = $func->getParameters();
		$param = $param[0];
		self::assertEquals(0, $param->getPosition());

		$func = new ezcReflectionFunction('m1');
		$param = $func->getParameters();
		$param = $param[1];
		self::assertEquals(1, $param->getPosition());
	}

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcReflectionParameterTest" );
    }
}
?>
