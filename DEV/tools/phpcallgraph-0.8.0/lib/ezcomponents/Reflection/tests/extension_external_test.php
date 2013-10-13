<?php
/**
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 * @filesource
 * @package Reflection
 * @subpackage Tests
 */

class ezcReflectionExtensionExternalTest extends ezcReflectionExtensionTest
{
    public function setUp() {
        $this->phpExtRef = new ReflectionExtension('Reflection');
        $this->phpExtSpl = new ReflectionExtension('Spl');
        $this->extRef = new ezcReflectionExtension($this->phpExtRef);
        $this->extSpl = new ezcReflectionExtension($this->phpExtSpl);
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcReflectionExtensionExternalTest" );
    }
}
?>
