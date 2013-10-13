<?php
/**
 * File containing the ezcReflectionDocTagFactory class.
 *
 * @package Reflection
 * @version //autogen//
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Creates a ezcReflectionDocTag object be the given doctag
 *
 * @package Reflection
 * @version //autogen//
 * @author Stefan Marr <mail@stefan-marr.de>
 */
class ezcReflectionDocTagFactory
{

	/**
	 * Don't allow objects, it is just a static factory
	 */
    // @codeCoverageIgnoreStart
    private function __construct() {}
    // @codeCoverageIgnoreEnd

    /**
     * @param string $type
     * @param string[] $line array of words
     * @return ezcReflectionDocTag
     */
    static public function createTag($type, $line) {
        $tagClassName = 'ezcReflectionDocTag' . ucfirst($type);
        $tag = null;
        if (!empty($type) and class_exists($tagClassName)) {
            $tag = new $tagClassName($line);
        }
        else {
            $tag = new ezcReflectionDocTag($line);
        }
        return $tag;
    }
}
?>
