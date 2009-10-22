<?php

namespace li3_doctrine\extensions\data\source;

class Doctrine extends \lithium\data\Source {
	
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

		if (!$this->_isConnected && $options['autoConnect']) {
			$this->connect();
		}
		return $this->_isConnected;
	}
	
	public function connect() {
		
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
	
}

?>