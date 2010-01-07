<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\cases\extensions\doctrine\mapper;

use \lithium\data\Connections;
use \li3_doctrine\tests\mocks\data\model\MockDoctrinePost;

/**
 *
 */
class ModelDriverTest extends \lithium\test\Unit {
	public function setUp() {
		if (!Connections::get('doctrineTest')) {
			Connections::add('doctrineTest', 'Doctrine', array(
				'driver' => 'pdo_sqlite',
				'path' => ':memory:'
			));
		}

		$this->post = new MockDoctrinePost();
	}

	public function testLoadMetadataForClass() {
		$meta = $this->post->connection()->getEntityManager()->getClassMetadata(get_class($this->post));
		$this->assertTrue(!empty($meta));
		$properties = $meta->getReflectionProperties();
		$this->assertTrue(!empty($properties));
		$result = array_keys($properties);
		$expected = array_keys($this->post->schema());
		$this->assertEqual($result, $expected);
	}
}

?>
