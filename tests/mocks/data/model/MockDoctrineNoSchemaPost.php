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
class MockDoctrineNoSchemaPost extends \lithium\data\Model {
	protected $_meta = array(
		'source' => 'posts',
		'key' => 'id',
		'connection' => 'doctrineTest'
	);
	public function connection() {
		return $this->_connection();
	}
}

?>
