<?php
/**
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version //autogen//
 * @filesource
 * @package Reflection
 * @subpackage Tests
 */

class ezcReflectionFunctionTest extends ezcTestCase
{
	/**
     * @var ReflectionFunction
     */
    protected $php_fctM1;
    protected $php_fctM2;
    protected $php_fctM3;
    protected $php_fct_method_exists;

	/**
     * @var ezcReflectionFunction
     */
    protected $fctM1;
    protected $fctM2;
    protected $fctM3;
    protected $fct_method_exists;

    public function setUp() {
        $this->php_fctM1 = new ReflectionFunction('m1');
        $this->php_fctM2 = new ReflectionFunction('m2');
        $this->php_fctM3 = new ReflectionFunction('m3');
        $this->php_fct_method_exists = new ReflectionFunction('method_exists');
        $this->fctM1 = new ezcReflectionFunction('m1');
        $this->fctM2 = new ezcReflectionFunction('m2');
        $this->fctM3 = new ezcReflectionFunction('m3');
        $this->fct_method_exists = new ezcReflectionFunction('method_exists');
    }

    public function tearDown() {
        unset($this->php_fctM1);
        unset($this->php_fctM2);
        unset($this->php_fctM3);
        unset($this->fctM1);
        unset($this->fctM2);
        unset($this->fctM3);
    }

    public function testGetTags() {
        $func = $this->fctM1;
        $tags = $func->getTags();

        $expectedTags = array('webmethod', 'author', 'param', 'param', 'param', 'return');
        ReflectionTestHelper::expectedTags($expectedTags, $tags, $this);


        $func = $this->fctM2;
        $tags = $func->getTags();
        $expectedTags = array('param', 'author');
        ReflectionTestHelper::expectedTags($expectedTags, $tags, $this);
    }

    public function testIsTagged() {
        $func = $this->fctM1;
        self::assertFalse($func->isTagged('licence'));
        self::assertTrue($func->isTagged('webmethod'));
    }

    public function testGetLongDescription() {
        $func = $this->fctM1;
        $desc = $func->getLongDescription();

        $expected = '';
        self::assertEquals($expected, $desc);

        $func = $this->fctM2;
        $desc = $func->getLongDescription();

        $expected = '';
        self::assertEquals($expected, $desc);

        $func = $this->fctM3;
        $desc = $func->getLongDescription();

        $expected = '';
        self::assertEquals($expected, $desc);

        $func = new ezcReflectionFunction('m4');
        $desc = $func->getLongDescription();

        $expected =  "This function is used to set up the DOM-Tree and to make the important\n".
                     "nodes accessible by assigning global variables to them. Furthermore,\n".
                     "depending on the used \"USE\", diferent namespaces are added to the\n".
                     "definition element.\n".
                     "Important: the nodes are not appended now, because the messages are not\n".
                     "created yet. That's why they are appended after the messages are created.";
        self::assertEquals($expected, $desc);
    }

    public function testGetShortDescription() {
        $func = $this->fctM1;
        $desc = $func->getShortDescription();
        $expected = 'To check whether a tag was used';
        self::assertEquals($expected, $desc);

        $func = $this->fctM2;
        $desc = $func->getShortDescription();
        $expected = 'weird coding standards should also be supported:';
        self::assertEquals($expected, $desc);

        $func = $this->fctM3;
        $desc = $func->getShortDescription();
        $expected = '';
        self::assertEquals($expected, $desc);

        $func = new ezcReflectionFunction('m4');
        $desc = $func->getShortDescription();
        $expected = 'Enter description here...';
        self::assertEquals($expected, $desc);
    }

    public function testGetReturnDescription() {
        $func = $this->fctM1;
        $desc = $func->getReturnDescription();
        self::assertEquals('Hello World', $desc);

        $func = new ezcReflectionFunction('m4');
        $desc = $func->getReturnDescription();
        self::assertEquals('', $desc);
    }

    public function testGetReturnType() {
        $func = new ezcReflectionFunction('m1');
        $type = $func->getReturnType();
        self::assertType('ezcReflectionType', $type);
        self::assertEquals('string', $type->toString());

        $func = new ezcReflectionFunction('m4');
        self::assertNull($func->getReturnType());
    }

    public function testGetParameters() {
        $func = new ezcReflectionFunction('m1');
        $params = $func->getParameters();

        $expected = array('test', 'test2', 'test3');
        ReflectionTestHelper::expectedParams($expected, $params, $this);

        $func = $this->fctM3;
        $params = $func->getParameters();
        self::assertTrue(count($params) == 0);
    }

    public function testGetCode() {
        self::assertEquals( "function m1(\$test, \$test2, &\$test3) {\n    return 'Hello World';\n}\n", $this->fctM1->getCode() );
        self::assertEquals( "function m2() {\n\n}\n", $this->fctM2->getCode() );
        self::assertEquals( "function m3() {\n    static \$staticVar;\n}\n", $this->fctM3->getCode() );
        self::assertEquals( "/* method_exists is an internal function. Therefore the source code is not available. */", $this->fct_method_exists->getCode() );
        $tokens[] = token_get_all("<?php\n" . $this->fctM1->getCode());
        $tokens[] = token_get_all("<?php\n" . $this->fctM2->getCode());
        $tokens[] = token_get_all("<?php\n" . $this->fctM3->getCode());
        $tokens[] = token_get_all("<?php\n" . $this->fct_method_exists->getCode());
        //var_export($tokens);
    }


    // the following methods do not contain additional features
    // they just call the parent method or the reflection source

    public function testToString() {
        self::assertEquals(  $this->php_fctM1->__toString(), $this->fctM1->__toString() );
        self::assertEquals( "{$this->php_fctM2}", "{$this->fctM2}" );
        self::assertEquals( (string) $this->php_fctM3, (string) $this->fctM3);
    }

    public function testGetName() {
    	self::assertEquals('m1', $this->fctM1->getName());
    	self::assertEquals('m2', $this->fctM2->getName());
    	self::assertEquals('m3', $this->fctM3->getName());
    }

    public function testIsInternal() {
        self::assertFalse($this->fctM1->isInternal());
        self::assertEquals(
            $this->php_fct_method_exists->isInternal(),
            $this->fct_method_exists->isInternal()
        );
    }

    public function testIsDisabled() {
    	self::assertFalse($this->fctM1->isDisabled());
    }

    public function testIsUserDefined() {
    	self::assertTrue($this->fctM1->isUserDefined());
    }

    public function testGetFileName() {
    	self::assertEquals('functions.php', basename($this->fctM1->getFileName()));
    }

    public function testGetStartLine() {
    	self::assertEquals(12, $this->fctM1->getStartLine());
    }

    public function testGetEndLine() {
    	self::assertEquals(14, $this->fctM1->getEndLine());
    }

    public function testGetDocComment() {
    	self::assertEquals("/**
 * @param void \$DocuFlaw
 * @author flaw joe
weird coding standards should also be supported: */", $this->fctM2->getDocComment());
    }

    public function testGetStaticVariables() {
    	$vars = $this->fctM3->getStaticVariables();
    	self::assertEquals(1, count($vars));
    	self::assertTrue(array_key_exists('staticVar', $vars));
    }

    public function testInvoke() {
        self::assertEquals(
            $this->php_fctM1->invoke(
                'test',
                'ezcReflectionApi',
                new ReflectionClass( 'ReflectionClass' )
            ),
            $this->fctM1->invoke(
                'test',
                'ezcReflectionApi',
                new ReflectionClass( 'ReflectionClass' )
            )
        );
        self::assertEquals(
            $this->php_fct_method_exists->invoke( 'ReflectionClass', 'hasMethod' ),
            $this->fct_method_exists->invoke( 'ReflectionClass', 'hasMethod' )
        );
    }

    public function testInvokeArgs() {
        self::assertEquals(
            $this->php_fctM1->invokeArgs(
                array(
                    'test',
                    'ezcReflectionApi',
                    new ReflectionClass( 'ReflectionClass' )
                )
            ),
            $this->fctM1->invokeArgs(
                array(
                    'test',
                    'ezcReflectionApi',
                    new ReflectionClass( 'ReflectionClass' )
                )
            )
        );
        self::assertEquals(
            $this->php_fct_method_exists->invokeArgs( array( 'ReflectionClass', 'hasMethod' ) ),
            $this->fct_method_exists->invokeArgs( array( 'ReflectionClass', 'hasMethod' ) )
        );
    }

    public function testReturnsReference() {
    	self::assertFalse($this->fctM3->returnsReference());
    }

    public function testGetNumberOfParameters() {
    	self::assertEquals(3, $this->fctM1->getNumberOfParameters());
    	$func = new ReflectionFunction('mmm');
    	self::assertEquals(1, $func->getNumberOfParameters());
    }
    public function testGetNumberOfRequiredParameters() {
    	self::assertEquals(3, $this->fctM1->getNumberOfRequiredParameters());
    	$func = new ReflectionFunction('mmm');
    	self::assertEquals(0, $func->getNumberOfRequiredParameters());
    }

    public function testGetExtension() {
        self::assertEquals( $this->php_fctM1->getExtension(), $this->fctM1->getExtension() );
        self::assertEquals( $this->php_fctM2->getExtension(), $this->fctM2->getExtension() );
        self::assertEquals( $this->php_fctM3->getExtension(), $this->fctM3->getExtension() );
        self::assertEquals( (string) $this->php_fct_method_exists->getExtension(), (string) $this->fct_method_exists->getExtension() );
    }

    public function testGetExtensionName() {
        self::assertEquals(  $this->php_fctM1->getExtensionName(), $this->fctM1->getExtensionName() );
        self::assertEquals(  $this->php_fctM2->getExtensionName(), $this->fctM2->getExtensionName() );
        self::assertEquals(  $this->php_fctM3->getExtensionName(), $this->fctM3->getExtensionName() );
        self::assertEquals(  $this->php_fct_method_exists->getExtensionName(), $this->fct_method_exists->getExtensionName() );
    }

    public function testExport() {
        self::assertEquals( ReflectionFunction::export( 'm1', true ), ezcReflectionFunction::export( 'm1', true ) );
        self::assertEquals( ReflectionFunction::export( 'm2', true ), ezcReflectionFunction::export( 'm2', true ) );
        self::assertEquals( ReflectionFunction::export( 'm3', true ), ezcReflectionFunction::export( 'm3', true ) );
    }

    public static function suite()
    {
         return new PHPUnit_Framework_TestSuite( "ezcReflectionFunctionTest" );
    }
}
?>
