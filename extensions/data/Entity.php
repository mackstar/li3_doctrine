<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\data;

/**
 * The li3_doctrine base model for Doctrine 2 entities.
 *
 * The base model inherits from ActiveEntity to enable your models with more
 * traditional ActiveRecord behavior.
 */
abstract class Entity extends \DoctrineExtensions\ActiveEntity {

	/**
	 * Single-object instances for accessing certain instance properties and
	 * methods within static context.
	 *
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * Class dependencies.
	 *
	 * @var array
	 */
	protected $_classes = array(
		'connections' => '\lithium\data\Connections'
	);

	/**
	 * Meta data.
	 *
	 * @var array
	 */
	protected $_meta = array(
		'connection' => 'default'
	);

	/**
	 * Initialize model - attaches connection source to model instance.
	 */
	public static function __init() {
		if (get_called_class() === __CLASS__) {
			return;
		}

		$self = static::_instance();
		$connections = $self->_classes['connections'];
		$connection = $connections::get($self->_meta['connection']);

		static::setEntityManager($connection->entityManager());
	}

	/**
	 * Get instance of model for use in static context.
	 *
	 * @return object Model instance for use in static context
	 */
	protected static function &_instance() {
		$class = get_called_class();

		if (!isset(static::$_instances[$class])) {
			static::$_instances[$class] = new $class();
		}
		return static::$_instances[$class];
	}

}

?>