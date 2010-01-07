<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\doctrine\mapper\reflection;

class SchemaReflectionProperty extends \ReflectionProperty {
	protected $_name;
	public function __construct($class, $name){
		try {
			parent::__construct($class, $name);
		} catch(\ReflectionException $e) {
			$this->_name = $name;
		}
	}

	public function getName() {
		if (isset($this->_name)) {
			return $this->_name;
		}
		return parent::getName();
	}

	public function getAccessible() {
		if (isset($this->_accessible)) {
			return $this->_accessible;
		}
		return parent::getAccessible();
	}

	public function setAccessible($accessible) {
		if (isset($this->_name)) {
			$this->_accessible = $accessible;
		} else {
			parent::setAccessible($accessible);
		}
	}
}

?>
