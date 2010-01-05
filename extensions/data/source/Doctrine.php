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
use \Doctrine\ORM\Configuration;
use \Doctrine\ORM\EntityManager;

/**
 *
 */
class Doctrine extends \lithium\data\Source {

	/**
	 * Entity manager.
	 */
	protected $_em;

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

		$this->_em = EntityManager::create($config, $configuration, $eventManager);
		$schemaManager = $this->_em->getConnection()->getSchemaManager();
		$configuration->setMetadataDriverImpl(new ModelDriver());

		parent::__construct($config);
	}

	/**
	 *
	 */
	public function configureClass($class) {
		return array('classes' => array(
			'query' => '\li3_doctrine\extensions\data\model\Query'
		));
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
	 * Returns the list of tables in the currently-connected database.
	 *
	 * @param string $model The fully-name-spaced class name of the model object making the request.
	 * @return array Returns an array of objects to which models can connect.
	 * @filter This method can be filtered.
	 */
	public function entities($class = null) {
	}

	/**
	 *
	 */
	public function describe($entity, $meta = array()) {
	}

	/**
	 *
	 */
	public function create($query, $options) {
	}

	/**
	 *
	 */
	public function read($query, $options) {
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
	public function getEntityManager() {
		return $this->_em;
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
}

?>