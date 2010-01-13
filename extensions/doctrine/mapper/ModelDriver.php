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
use \lithium\util\Inflector;
use \Doctrine\ORM\Mapping\ClassMetadataInfo;
use \Doctrine\ORM\Mapping\Driver\Driver;

/**
 *
 */
class ModelDriver implements Driver {
	protected static $_bindingMapping = array(
		'belongsTo' => 'mapManyToOne',
		'hasMany' => 'mapOneToMany',
		'hasOne' => 'mapOneToOne'
	);

	public function loadMetadataForClass($className, ClassMetadataInfo $metadata) {
		if (!($metadata->reflClass instanceof SchemaReflection)) {
			$metadata->reflClass = new SchemaReflection($metadata->getClassName());
		}

		$metadata->primaryTable['name'] = $className::meta('source');
		$primaryKey = $className::meta('key');

		$bindings = $this->_bindings($className);
		$relations = array();
		if (!empty($bindings)) {
			foreach($bindings as $type => $set) {
				foreach($set as $key => $relation) {
					$fieldName = $relation['class']::meta('name');
					$fieldName = strtolower($fieldName[0]).substr($fieldName, 1);
					$mapping = array(
						'fieldName' => $fieldName,
						'sourceEntity' => $className,
						'targetEntity' => $relation['class'],
						'mappedBy' => null,
						'cascade' => !empty($relation['dependent']) ? array('remove') : array()
					);

					switch($type) {
						case 'hasOne':
						case 'hasMany':
							$mapping['mappedBy'] = $relation['key'];
						break;
						case 'belongsTo':
							$mapping['mappedBy'] = $relation['class']::meta('key');
						break;
					}

					$relations[$type][$key] = $mapping;
				}
			}
		}

		$schema = (array) $className::schema();

		$metadata->reflClass->setRelations($relations);
		$metadata->reflClass->setSchema($schema);

		foreach ($schema as $field => $column) {
			$mapping = array(
				'id' => $field == $primaryKey,
				'fieldName' => $field
			);
			$metadata->mapField($mapping + (array) $column);
		}

		foreach($relations as $type => $set) {
			foreach($set as $key => $mapping) {
				$metadata->{self::$_bindingMapping[$type]}($mapping);
			}
		}
	}

	public function isTransient($class) {
		return true;
	}

	public function getAllClassNames() {
		$classes = array();
		return $classes;
	}

	public function preload() {
		$tables = array();
		return $tables;
	}

	protected function _bindings($className) {
		$ns = function($class) use ($className) {
			static $namespace;
			$namespace = $namespace ?: preg_replace('/\w+$/', '', $className);
			return "{$namespace}{$class}";
		};

		$modelName = $className::meta('name');
		$bindings = array();
		foreach(self::$_bindingMapping as $binding => $method) {
			$relations = $className::relations($binding);
			if (empty($relations)) {
				$bindings[$binding] = array();
				continue;
			}

			foreach($relations as $key => $value) {
				$defaults = array(
					'class' => null,
					'key' => null,
					'conditions' => null,
					'fields' => true
				);

				if ($binding != 'belongsTo') {
					$defaults['dependent'] = false;
				}

				if ($binding == 'hasMany') {
					$defaults = array_merge($defaults, array(
						'order' => null,
						'limit' => null,
						'exclusive' => null,
						'finder' => null,
						'counter' => null
					));
				}

				$relation = array();
				if (is_array($value)) {
					$relation = $value;
				}

				$relation = array_merge($defaults, $relation);

				if (!is_string($key) && is_string($value)) {
					$relation['class'] = $value;
				} elseif (is_string($key)) {
					$relation['class'] = $key;
				}

				if (empty($relation['key'])) {
					switch($binding) {
						case 'belongsTo':
							$relation['key'] = Inflector::underscore($relation['class']) . '_id';
						break;
						case 'hasOne':
						case 'hasMany':
							$relation['key'] = Inflector::underscore($modelName) . '_id';
						break;
					}
				}

				if (strpos($relation['class'], '\\') === false) {
					$relation['class'] = $ns($relation['class']);
				}

				if (!is_string($key)) {
					$key = $relation['class']::meta('name');
				}

				$bindings[$binding][$key] = $relation;
			}
		}
		return $bindings;
	}
}

?>
