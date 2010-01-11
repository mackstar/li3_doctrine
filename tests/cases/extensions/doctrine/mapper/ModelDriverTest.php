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
use \li3_doctrine\tests\mocks\data\model\MockDoctrineNoSchemaPost;

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

		$connection = Connections::get('doctrineTest');

		$this->post = new MockDoctrinePost();
		$this->noSchemaPost = new MockDoctrineNoSchemaPost();
		$this->datasource = $this->post->connection();
	}

	public function tearDown() {
		unset($this->post);
		unset($this->noSchemaPost);
	}

	public function testMetadata() {
		$schema = array_keys($this->post->schema());
		$meta = $this->datasource->getEntityManager()->getClassMetadata(get_class($this->post));
		$this->assertTrue(!empty($meta));
		$properties = $meta->getReflectionProperties();
		$this->assertTrue(!empty($properties));
		$result = array_keys($properties);
		$this->assertEqual($result, $schema);
	}

	public function testMetadataRelations() {
		$meta = $this->datasource->getEntityManager()->getClassMetadata(get_class($this->post));
		$associations = $meta->getAssociations();
		$this->assertTrue(!empty($associations));
		$this->assertEqual(array_keys($associations), array('id'));
		$this->assertTrue($associations['id']->isOneToMany());
		$this->assertFalse($associations['id']->hasCascades());
		$this->assertEqual($associations['id']->getSourceEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrinePost');
		$this->assertEqual($associations['id']->getSourceFieldName(), 'id');
		$this->assertEqual($associations['id']->getTargetEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrineComment');
		$this->assertEqual($associations['id']->getMappedByFieldName(), 'post_id');
	}

	public function testMetadataNoSchema() {
		$connection = Connections::get('doctrineTest', array('config'=>true));
		$this->skipIf(strpos($connection['driver'], 'sqlite') !== false, 'Non SQLite driver needed for this test');

		$schema = array_keys($this->post->schema());
		$meta = $this->datasource->getEntityManager()->getClassMetadata(get_class($this->noSchemaPost));
		$this->assertTrue(!empty($meta));
		$properties = $meta->getReflectionProperties();
		$this->assertTrue(!empty($properties));
		$result = array_keys($properties);
		$this->assertEqual($result, $schema);
	}

	public function testEntities() {
		$entities = $this->datasource->entities();
		$this->assertTrue(!empty($entities));
		$this->assertTrue(in_array($this->post->meta('source'), $entities));
	}
}

?>
