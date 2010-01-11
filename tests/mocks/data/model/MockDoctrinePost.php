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
class MockDoctrinePost extends \lithium\data\Model {
	public $hasMany = array(
		'MockDoctrineComment' => array('key' => 'post_id')
	);

	protected $_meta = array(
		'source' => 'posts',
		'key' => 'id',
		'connection' => 'doctrineTest'
	);

	protected $_schema = array(
		'id' => array('type' => 'integer', 'unsigned' => true, 'notnull' => true),
		'title' => array('type' => 'string', 'notnull' => true),
		'body' => array('type' => 'text', 'notnull' => true),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime')
	);

	public function connection() {
		return $this->_connection();
	}
}

?>
