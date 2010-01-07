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
	protected $_meta = array(
		'source' => 'posts',
		'key' => 'id',
		'connection' => 'doctrineTest'
	);

	protected $_schema = array(
		'id' => array('type' => 'integer', 'unsigned' => true, 'notnull' => true),
		'title' => array('type' => 'string', 'notnull' => true),
		'body' => array('type' => 'string', 'notnull' => true),
		'created' => array('type' => 'datetime'),
		'updated' => array('type' => 'datetime')
	);

	public $id;
	public $title;
	public $body;
	public $created;
	public $updated;

	public function connection() {
		return $this->_connection();
	}
}

?>
