<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\cases\extensions\data\source;

use \lithium\data\Connections;
use \li3_doctrine\tests\mocks\data\model\MockDoctrinePost;

/**
 * Doctrine data source tests.
 */
class DoctrineTest extends \lithium\test\Unit {
	public function setUp() {
		if (!Connections::get('doctrineTest')) {
			Connections::add('doctrineTest', 'Doctrine', array(
				'driver' => 'pdo_sqlite',
				'path' => ':memory:'
			));
		}

		$this->post = new MockDoctrinePost();
	}

	public function testCreate() {
		$post = $this->post->find('first', array(
			'conditions' => array('Post.id' => 1)
		));
	}

	public function testRead() {

	}

	public function testUpdate() {
	}

	public function testDelete() {
	}
}

?>
