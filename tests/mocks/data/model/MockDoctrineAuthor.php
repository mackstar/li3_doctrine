<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\mocks\data\model;

/**
 *
 */
class MockDoctrineAuthor extends \lithium\data\Model {
	public $hasMany = array(
		'MockDoctrinePost' => array('key' => 'author_id')
	);
	protected $_meta = array(
		'source' => 'authors',
		'key' => 'id',
		'connection' => 'doctrineTest',
		'name' => null,
		'title' => null,
		'class' => null,
		'initialized' => false
	);

	protected $_schema = array(
		'id' => array('type' => 'integer', 'unsigned' => true, 'notnull' => true),
		'name' => array('type' => 'string', 'notnull' => true),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime')
	);

	public function connection() {
		return $this->_connection();
	}
}

?>
