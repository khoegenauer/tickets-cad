<?php
/**
 * @licence New BSD like
 * @donotdocument
 * @testclass
 * @ignore
 */
class SomeClass extends BaseClass implements IInterface {
    /**
     * @var int[]
     */
    private $fields;

    public function __construct() {
        // echo "New SomeClass instance created.\n";
    }

    public function helloWorld()
    {
        return true;
    }
}
?>
