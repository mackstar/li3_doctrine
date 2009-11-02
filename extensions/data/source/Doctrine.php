<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\data\source;

use \Doctrine\ORM\EntityManager;
use \Doctrine\ORM\Configuration;

/**
 * This source introduces support for the Doctrine ORM into Lithium.
 */
class Doctrine extends \lithium\data\Source {

	/**
	 * `Doctrine\ORM\EntityManager` instance tied to this connection.
	 *
	 * @var object
	 */
	protected $_entityManager;

	/**
	 * Doctrine connection options.
	 *
	 * @var array
	 */
	protected $_doctrineOptions = array('config' => null);

	/**
	 * Initialize Doctrine source.
	 *
	 * @param array $config
	 */
	public function __construct($config = array()) {
		$this->_doctrineOptions += $config;
		$config = array();
		parent::__construct($config);
	}

	/**
	 * Perform additional initialization.
	 */
	protected function _init() {
		$bootstrap = $this->_doctrineOptions['config'] ?: function($config) {
			$config->setProxyDir(LITHIUM_APP_PATH . '/extensions/proxies');
			$config->setProxyNamespace('\app\extensions\proxies');
			return $config;
		};

		$config = new Configuration();
		$config = $bootstrap($config);
		unset($this->_doctrineOptions['config']);

		$this->_entityManager = EntityManager::create($this->_doctrineOptions, $config);

		parent::_init();
	}

	/**
	 * Get `Doctrine\ORM\EntityManager` instance for this connection.
	 *
	 * @return object
	 */
	public function entityManager() {
		return $this->_entityManager;
	}

	/**
	 * Determine Doctrine connection state.
	 *
	 * @return boolean
	 */
	protected function _isConnected() {
		$connection = $this->_entityManager->getConnection();
		return $connection->isConnected();
	}

	/**
	 * Checks the connection status of this database. If the `'autoConnect'` option is set to true
	 * and the database connection is not currently active, an attempt will be made to connect
	 * to the database before returning the result of the connection status.
	 *
	 * @param array $options The options available for this method:
	 *              - 'autoConnect': If true, and the database connection is not currently active,
	 *                calls `connect()` on this object. Defaults to `false`.
	 * @return boolean Returns the current value of `$_isConnected`, indicating whether or not
	 *         the database connection is currently active.  This value may not always be accurate,
	 *         as the database session could have timed out or the database may have gone offline
	 *         during the course of the request.
	 */
	public function isConnected($options = array()) {
		$defaults = array('autoConnect' => false);
		$options += $defaults;

		if (!$this->_isConnected() && $options['autoConnect']) {
			$this->connect();
		}
		return $this->_isConnected();
	}

	/**
	 * Instruct EntityManager's Connection to establish connection.
	 */
	public function connect() {
		$connection = $this->_entityManager->getConnection();
		return $connection->connect();
	}

	/**
	 * Close connection.
	 *
	 * @return boolean False if not connected, true if disconnected.
	 */
	public function disconnect() {
		$connection = $this->_entityManager->getConnection();
		if (!$this->_isConnected()) {
			return false;
		}
		$connection->close();
		return true;
	}

	/**
	 * Not implemented
	 */
	public function entities($class = null) {}

	/**
	 * Not implemented
	 */
	public function describe($entity, $meta = array()) {}

	/**
	 * Not implemented
	 */
	public function create($record, $options) {}

	/**
	 * Not implemented
	 */
	public function read($query, $options) {}

	/**
	 * Not implemented
	 */
	public function update($query, $options) {}

	/**
	 * Not implemented
	 */
	public function delete($query, $options) {}

}

?>