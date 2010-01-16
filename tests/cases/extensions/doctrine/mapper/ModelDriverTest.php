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
		$expected = array_merge($schema, array('mockDoctrineAuthor', 'mockDoctrineComment', 'mockDoctrineExcerpt'));
		sort($result);
		sort($expected);
		$this->assertEqual($expected, $result);
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
		$this->assertEqual($schema, $result);
	}


	public function testMetadataRelations() {
		$meta = $this->datasource->getEntityManager()->getClassMetadata(get_class($this->post));
		$associations = $meta->getAssociations();
		$result = array_keys($associations);
		$expected = array('mockDoctrineAuthor', 'mockDoctrineComment', 'mockDoctrineExcerpt');
		sort($result);
		sort($expected);
		$this->assertTrue(!empty($associations));
		$this->assertEqual($expected, $result);

		$this->assertTrue($associations['mockDoctrineAuthor']->isOneToOne());
		//$this->assertFalse($associations['mockDoctrineAuthor']->isOwningSide());
		$this->assertFalse($associations['mockDoctrineAuthor']->hasCascades());
		$this->assertEqual($associations['mockDoctrineAuthor']->getSourceEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrinePost');
		$this->assertEqual($associations['mockDoctrineAuthor']->getTargetEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrineAuthor');

		$this->assertTrue($associations['mockDoctrineComment']->isOneToMany());
		$this->assertFalse($associations['mockDoctrineComment']->hasCascades());
		$this->assertEqual($associations['mockDoctrineComment']->getSourceEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrinePost');
		$this->assertEqual($associations['mockDoctrineComment']->getTargetEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrineComment');
		$this->assertEqual($associations['mockDoctrineComment']->getMappedByFieldName(), 'post_id');

		$this->assertTrue($associations['mockDoctrineExcerpt']->isOneToOne());
		$this->assertTrue($associations['mockDoctrineExcerpt']->isOwningSide());
		$this->assertTrue($associations['mockDoctrineExcerpt']->hasCascades());
		$this->assertEqual($associations['mockDoctrineExcerpt']->getSourceEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrinePost');
		$this->assertEqual($associations['mockDoctrineExcerpt']->getTargetEntityName(), 'li3_doctrine\tests\mocks\data\model\MockDoctrineExcerpt');
		$this->assertEqual($associations['mockDoctrineExcerpt']->getMappedByFieldName(), 'post_id');

		$post = MockDoctrinePost::find('first', array('conditions' => array('id' => 1)));
		//var_dump($post);
		//var_dump($post->author_id);
		//var_dump($post->mockDoctrineAuthor);
		//var_dump($post->mockDoctrineExcerpt);
		//var_dump($post->mockDoctrineComment);
	}

	public function testEntities() {
		$entities = $this->datasource->entities();
		$this->assertTrue(!empty($entities));
		$this->assertTrue(in_array($this->post->meta('source'), $entities));
	}
}

?>
