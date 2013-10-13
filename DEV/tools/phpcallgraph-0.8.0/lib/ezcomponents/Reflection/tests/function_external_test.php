<?php
/**
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 * @filesource
 * @package Reflection
 * @subpackage Tests
 */

class ezcReflectionFunctionExternalTest extends ezcReflectionFunctionTest
{
    public function setUp() {
        $this->php_fctM1 = new ReflectionFunction( 'm1' );
        $this->php_fctM2 = new ReflectionFunction( 'm2' );
        $this->php_fctM3 = new ReflectionFunction( 'm3' );
        $this->php_fct_method_exists = new ReflectionFunction( 'method_exists' );
        $this->fctM1 = new ezcReflectionFunction( $this->php_fctM1 );
        $this->fctM2 = new ezcReflectionFunction( $this->php_fctM2 );
        $this->fctM3 = new ezcReflectionFunction( $this->php_fctM3 );
        $this->fct_method_exists = new ezcReflectionFunction( $this->php_fct_method_exists );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcReflectionFunctionExternalTest" );
    }
}
?>
