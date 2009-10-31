<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\extensions\data\source;

use \Doctrine_Manager;
use \ReflectionProperty;

class Doctrine extends \lithium\data\Source {

	/**
	 * Holds a ReflectionProperty instance of the
	 * `Doctrine_Connection::$isConnected` property because its visibiliy is set
	 * to protected.
	 *
	 * @var object
	 */
	protected $_isConnectedProperty;

	public function __construct($config = array()) {
		$defaults = array(
			'connect' => null
		);
		parent::__construct((array)$config + $defaults);
	}

	protected function _init() {
		$manager = Doctrine_Manager::getInstance();
		$this->_connection = $manager->connection($this->_config['connect']);

		$adapter = get_class($this->_connection);
		$this->_isConnectedProperty = new ReflectionProperty($adapter, 'isConnected');
		$this->_isConnectedProperty->setAccessible(true);

		parent::_init();
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

	public function connect() {
		$this->_connection->connect();
	}

	public function disconnect() {

	}

	public function entities($class = null) {

	}

	public function describe($entity, $meta = array()) {

	}

	public function create($record, $options) {

	}

	public function read($query, $options) {

	}

	public function update($query, $options) {

	}

	public function delete($query, $options) {

	}

	protected function _isConnected() {
		return $this->_isConnectedProperty->getValue($this->_connection);
	}

}

?>