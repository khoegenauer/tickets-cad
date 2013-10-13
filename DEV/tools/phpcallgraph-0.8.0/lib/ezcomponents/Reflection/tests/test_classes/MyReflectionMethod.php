<?php
class MyReflectionMethod extends ReflectionMethod {
	public function change() {
		return true;
	}

	public function getDeclaringClass() {
		return new MyReflectionClass(parent::getDeclaringClass()->getName());
	}

    public function getParameters() {
    	$params = parent::getParameters();

    	$result = array();
    	foreach ($params as $param) {
    		$result = new MyReflectionProperty($this->getName(), $param->getName());
    	}
    	return $result;
    }
}
?>