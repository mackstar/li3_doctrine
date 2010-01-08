<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\data\source;

use \li3_doctrine\extensions\doctrine\mapper\ModelDriver;
use \Doctrine\Common\EventManager;
use \Doctrine\Common\Cache\ArrayCache;
use \Doctrine\ORM\Configuration;
use \Doctrine\ORM\EntityManager;
use \Doctrine\ORM\Query;

/**
 *
 */
class Doctrine extends \lithium\data\Source {
	/**
	 * Entity manager.
	 */
	protected $_em;

	/**
	 * Schema manager
	 */
	protected $_sm;

	/**
	 *
	 */
	public function __construct($config = array()) {
		if (isset($config['configuration'])) {
			$configuration = $config['configuration'];
			unset($config['configuration']);
		} else {
			$configuration = new Configuration();
		}

		$configuration->setProxyDir(LITHIUM_APP_PATH . '/models');
		$configuration->setProxyNamespace('\app\models');
		$configuration->setAutoGenerateProxyClasses(true);

		if (isset($config['eventManager'])) {
			$eventManager = $config['eventManager'];
			unset($config['eventManager']);
		} else {
			$eventManager = new EventManager();
		}

		$configuration->setMetadataCacheImpl(new ArrayCache());
		$configuration->setMetadataDriverImpl(new ModelDriver());

		$this->_em = EntityManager::create($config, $configuration, $eventManager);
		$this->_sm = $this->_em->getConnection()->getSchemaManager();
		parent::__construct($config);
	}

	/**
	 * Connects to the database using the options provided to the class constructor.
	 *
	 * @return boolean True if the database could be connected, else false.
	 */
	public function connect() {
		if (!$this->isConnected()) {
			return $this->getEntityManager()->getConnection()->connect();
		}
		return false;
	}


	/**
	 * Disconnects the adapter from the database.
	 *
	 * @return boolean True on success, else false.
	 */
	public function disconnect() {
		if ($this->isConnected()) {
			$this->getEntityManager()->getConnection()->close();
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function isConnected($options = array()) {
		$defaults = array('autoConnect' => false);
		$options += $defaults;
		$connected = $this->getEntityManager()->getConnection()->isConnected();

		if (!$connected && $options['autoConnect']) {
			$this->connect();
			return $this->getEntityManager()->getConnection()->isConnected();
		}
		return $connected;
	}

	/**
	 *
	 */
	public function getEntityManager() {
		return $this->_em;
	}

	/**
	 * Returns the list of tables in the currently-connected database.
	 *
	 * @param string $model The fully-name-spaced class name of the model object making the request.
	 * @return array Returns an array of objects to which models can connect.
	 * @filter This method can be filtered.
	 */
	public function entities($class = null) {
		$tables = $this->_sm->listTables();
		return $tables;
	}

	/**
	 *
	 */
	public function result($type, $resource, $context) {
	}

	/**
	 *
	 */
	public function describe($entity, $meta = array()) {
		$schema = array();
		$columns = $this->_sm->listTableColumns($entity);
		foreach($columns as $field => $column) {
			$column['type'] = strtolower((string) $column['type']);
			$schema[$field] = $column;
		}

		return $schema;
	}

	/**
	 *
	 */
	public function create($query, $options) {
	}

	/**
	 *
	 * @return RecordSet
	 */
	public function read($query, $options) {
		var_dump($query);
	}

	/**
	 *
	 */
	public function update($query, $options) {
	}

	/**
	 *
	 */
	public function delete($query, $options) {
	}

	/**
	 *
	 */
	public function conditions($conditions, $query) {
		return $conditions ?: array();
	}

	/**
	 *
	 */
	public function fields($fields, $query) {
		return $fields = $fields ?: array();
	}

	/**
	 *
	 */
	public function order($order, $query) {
		return $order ?: array();
	}

	/**
	 *
	 */
	public function limit($limit, $query) {
		return $limit ?: array();
	}

	/**
	 *
	 */
	public function name($name) {
		return $name;
	}

	/**
	 *
	 */
	public function columns($query, $resource = null, $context = null) {
	}
}

?>
