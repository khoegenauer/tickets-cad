<?php
/**
 * File containing the ezcReflectionFunction class.
 *
 * @package Reflection
 * @version //autogen//
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Extends the ReflectionFunction class using PHPDoc comments to provide
 * type information
 *
 * @package Reflection
 * @version //autogen//
 * @author Stefan Marr <mail@stefan-marr.de>
 * @author Falko Menge <mail@falko-menge.de>
 */
class ezcReflectionFunction extends ReflectionFunction
{
    /**
     * @var ezcReflectionDocParser Parser for source code annotations
     */
    protected $docParser;

    /**
     * @var string|ReflectionFunction
     *     ReflectionFunction object or function name used to initialize this
     *     object
     */
    protected $reflectionSource;

    /**
     * Constructs a new ezcReflectionFunction object
     *
     * Throws an Exception in case the given function does not exist
     * @param string|ReflectionFunction $function
     *        Name or ReflectionFunction object of the function to be reflected
     */
    public function __construct( $function ) {
        if ( !$function instanceof ReflectionFunction ) {
            parent::__construct( $function );
        }
        $this->reflectionSource = $function;

        $this->docParser = ezcReflectionApi::getDocParserInstance();
        $this->docParser->parse( $this->getDocComment() );
    }

    /**
     * Returns the parameters of the function as ezcReflectionParameter objects
     *
     * @return ezcReflectionParameter[] Function parameters
     */
    function getParameters() {
        $params = $this->docParser->getParamTags();
        $extParams = array();
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            $apiParams = $this->reflectionSource->getParameters();
        } else {
            $apiParams = parent::getParameters();
        }
        foreach ($apiParams as $param) {
            $found = false;
            foreach ($params as $tag) {
                if (
                    $tag instanceof ezcReflectionDocTagparam
                    and $tag->getParamName() == $param->getName()
                ) {
                    $extParams[] = new ezcReflectionParameter(
                        $tag->getType(),
                        $param
                    );
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $extParams[] = new ezcReflectionParameter(null, $param);
            }
        }
        return $extParams;
    }

    /**
     * Returns the type of the return value
     *
     * @return ezcReflectionType
     */
    function getReturnType() {
        $re = $this->docParser->getReturnTags();
        if (count($re) == 1 and isset($re[0]) and $re[0] instanceof ezcReflectionDocTagReturn) {
            return ezcReflectionApi::getTypeByName($re[0]->getType());
        }
        return null;
    }

    /**
     * Returns the description of the return value
     *
     * @return string
     */
    function getReturnDescription() {
        $re = $this->docParser->getReturnTags();
        if (count($re) == 1 and isset($re[0])) {
            return $re[0]->getDescription();
        }
        return '';
    }

    /**
     * Returns the short description from the function's documentation
     *
     * @return string Short description
     */
    public function getShortDescription() {
        return $this->docParser->getShortDescription();
    }

    /**
     * Returns the long description from the function's documentation
     *
     * @return string Long descrition
     */
    public function getLongDescription() {
        return $this->docParser->getLongDescription();
    }

    /**
     * Checks whether the function is annotated with the annotation $annotation
     *
     * @param string $annotation Name of the annotation
     * @return boolean True if the annotation exists for this function
     */
    public function isTagged($annotation) {
        return $this->docParser->isTagged($annotation);
    }

    /**
     * Returns an array of annotations (optinally only annotations of a given name)
     *
     * @param string $name Name of the annotations
     * @return ezcReflectionDocTag[] Annotations
     */
    public function getTags($name = '') {
        if ($name == '') {
            return $this->docParser->getTags();
        }
        else {
            return $this->docParser->getTagsByName($name);
        }
    }

    /**
     * Returns the source code of the function
     *
     * @return string Source code
     */
    public function getCode()
    {
        if ( $this->isInternal() ) {
            $code = '/* ' . $this->getName() . ' is an internal function.'
                  . ' Therefore the source code is not available. */';
        } else {
            $filename = $this->getFileName();

            $start = $this->getStartLine();
            $end = $this->getEndLine();

            $offset = $start - 1;
            $length = $end - $start + 1;
            
            $lines = array_slice( file( $filename ), $offset, $length );
            $code = implode( '', $lines );
        }
        return $code;
    }
    

    // the following methods do not contain additional features
    // they just call the parent method or the reflection source

    /**
     * Returns a string representation
     *
     * @return string String representation
     */
    public function __toString() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->__toString();
        } else {
            return parent::__toString();
        }
    }

    /**
     * Returns this function's name
     *
     * @return string This function's name
     */
    public function getName() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getName();
        } else {
            return parent::getName();
        }
    }

    /**
     * Returns whether this is an internal function
     *
     * @return boolean True if this is an internal function
     */
    public function isInternal() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->isInternal();
        } else {
            return parent::isInternal();
        }
    }

    /**
     * Returns whether this is a user-defined function
     *
     * @return boolean True if this is a user-defined function
     */
    public function isUserDefined() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->isUserDefined();
        } else {
            return parent::isUserDefined();
        }
    }

    /**
     * Returns whether this function has been disabled or not
     *
     * @return boolean True if this function has been disabled
     */
    public function isDisabled() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->isDisabled();
        } else {
            return parent::isDisabled();
        }
    }

    /**
     * Returns the filename of the file this function was declared in
     *
     * @return string Filename of the file this function was declared in
     */
    public function getFileName() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getFileName();
        } else {
            return parent::getFileName();
        }
    }

    /**
     * Returns the line this function's declaration starts at
     *
     * @return integer Line this function's declaration starts at
     */
    public function getStartLine() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getStartLine();
        } else {
            return parent::getStartLine();
        }
    }

    /**
     * Returns the line this function's declaration ends at
     *
     * @return integer Line this function's declaration ends at
     */
    public function getEndLine() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getEndLine();
        } else {
            return parent::getEndLine();
        }
    }

    /**
     * Returns the doc comment for this function
     *
     * @return string Doc comment for this function
     */
    public function getDocComment() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getDocComment();
        } else {
            return parent::getDocComment();
        }
    }

    /**
     * Returns an associative array containing this function's static variables
     * and their values
     *
     * @return array<sting,mixed> This function's static variables
     */
    public function getStaticVariables() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getStaticVariables();
        } else {
            return parent::getStaticVariables();
        }
    }

    /**
     * Invokes the function
     *
     * @param mixed $argument,...  Arguments
     * @return mixed               Return value of the function invocation
     */
    public function invoke( $argument ) {
        $arguments = func_get_args();
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            // doesn't work: return call_user_func_array( array( $this->reflectionSource, 'invoke' ), $arguments );
            // but hopefully the methods invoke and invokeArgs of
            // the external ReflectionFunction implementation are semantically the same
            return $this->reflectionSource->invokeArgs( $arguments );
        } else {
            // doesn't work: return call_user_func_array( array( parent, 'invoke' ), $arguments );
            // but hopefully the methods invoke and invokeArgs of
            // PHP's ReflectionFunction are semantically the same
            return parent::invokeArgs( $arguments );
        }
    }

    /**
     * Invokes the function and allows to pass its arguments as an array
     *
     * @param array<integer,mixed> $arguments
     *     Arguments
     * @return mixed
     *     Return value of the function invocation
     */
    public function invokeArgs( Array $arguments ) {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->invokeArgs( $arguments );
        } else {
            return parent::invokeArgs( $arguments );
        }
    }

    /**
     * Returns whether this function returns a reference
     *
     * @return boolean True if this function returns a reference
     */
    public function returnsReference() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->returnsReference();
        } else {
            return parent::returnsReference();
        }
    }

    /**
     * Returns the number of parameters
     *
     * @return integer The number of parameters
     */
    public function getNumberOfParameters() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getNumberOfParameters();
        } else {
            return parent::getNumberOfParameters();
        }
    }

    /**
     * Returns the number of required parameters
     *
     * @return integer The number of required parameters
     */
    public function getNumberOfRequiredParameters() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getNumberOfRequiredParameters();
        } else {
            return parent::getNumberOfRequiredParameters();
        }
    }

    /**
     * Returns NULL or the extension the function belongs to
     *
     * @return ezcReflectionExtension Extension the function belongs to
     */
    public function getExtension() {
        if ( $this->getExtensionName() === false ) {
            return null;
        } else {
            if ( $this->reflectionSource instanceof ReflectionFunction ) {
                return new ezcReflectionExtension(
                    $this->reflectionSource->getExtension()
                );
            } else {
                // using the name, since otherwhise the object would be treated like an
                // external reflection implementation and that would decrease performance
                return new ezcReflectionExtension( parent::getExtensionName() );
            }
        }
    }

    /**
     * Returns false or the name of the extension the function belongs to
     *
     * @return string|boolean False or the name of the extension
     */
    public function getExtensionName() {
        if ( $this->reflectionSource instanceof ReflectionFunction ) {
            return $this->reflectionSource->getExtensionName();
        } else {
            return parent::getExtensionName();
        }
    }

    /**
     * Exports a reflection function object.
     *
     * Returns the output if TRUE is specified for $return, printing it otherwise.
     * This is purely a wrapper method which calls the corresponding method of
     * the parent class (ReflectionFunction::export()).
     * @param string $function Name of the function
     * @param boolean $return
     *        Wether to return (TRUE) or print (FALSE) the output
     * @return mixed
     */
    public static function export($function, $return = false) {
        return parent::export($function, $return);
    }
}
?>
