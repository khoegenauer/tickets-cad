<?php
/**
 * File containing the ezcReflectionAbstractType class.
 *
 * @package Reflection
 * @version //autogen//
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Abstract class provides default implementation for types.
 * Methods do return null or false values as default.
 *
 * @package Reflection
 * @version //autogen//
 * @author Stefan Marr <mail@stefan-marr.de>
 */
abstract class ezcReflectionAbstractType implements ezcReflectionType
{
    /**
     * Returns type of array items or null
     *
     * @return ezcReflectionType
     */
    public function getArrayType()
    {
        return null;
    }

    /**
     * Returns key type of map items or null
     *
     * @return ezcReflectionType
     */
    public function getMapIndexType()
    {
        return null;
    }

    /**
     * Returns value type of map items or null
     *
     * @return ezcReflectionType
     */
    public function getMapValueType()
    {
        return null;
    }

    /**
     * @return boolean
     */
    public function isArray() {
        return false;
    }

    /**
     * @return boolean
     */
    public function isClass() {
        return false;
    }

    /**
     * @return boolean
     */
    public function isPrimitive() {
        return false;
    }

    /**
     * @return boolean
     */
    public function isMap() {
        return false;
    }
}

?>