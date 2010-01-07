<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\doctrine\mapper;

use \li3_doctrine\extensions\doctrine\mapper\reflection\SchemaReflection;
use \lithium\data\Connections;
use \Doctrine\ORM\Mapping\ClassMetadataInfo;
use \Doctrine\ORM\Mapping\Driver\Driver;

/**
 *
 */
class ModelDriver implements Driver {
	public function loadMetadataForClass($className, ClassMetadataInfo $metadata) {
		if (!($metadata->reflClass instanceof SchemaReflection)) {
			$metadata->reflClass = new SchemaReflection($metadata->getClassName());
		}

		$metadata->primaryTable['name'] = $className::meta('source');
		$key = $className::meta('key');

		$schema = (array) $className::schema();
		$metadata->reflClass->setSchema($schema);

		foreach ($schema as $field => $column) {
			$primary = $field == $key;
			$mapping = array(
				'id' => $primary,
				'fieldName' => $field
			);
			$metadata->mapField($mapping + (array) $column);
		}
	}

	public function getAllClassNames() {
		return array();
	}

	public function isTransient($class) {
		return true;
	}

	public function preload() {
		$tables = array();
		return $tables;
	}
}

?>
