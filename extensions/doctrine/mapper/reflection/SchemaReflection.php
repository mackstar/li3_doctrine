<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\doctrine\mapper\reflection;

class SchemaReflection extends \ReflectionClass {
	protected $_schema;

	public function getSchema() {
		return $this->_schema;
	}

	public function setSchema($schema) {
		$this->_schema = $schema;
	}

	public function getProperty($name) {
		$property = null;
		try {
			$property = parent::getProperty($name);
		} catch(\ReflectionException $e) {
			if (!array_key_exists($name, $this->_schema)) {
				throw $e;
			}
		}

		if (empty($property)) {
			$property = new SchemaReflectionProperty($this, $name);
		}

		return $property;
	}
}

?>
