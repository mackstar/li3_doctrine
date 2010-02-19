<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\cases\extensions\doctrine\mapper;

use \lithium\analysis\Logger;
use \lithium\data\Connections;
use \li3_doctrine\tests\mocks\analysis\MockLoggerAdapter;
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
				'path' => ':memory:',
				'useModelDriver' => true
			));
		}

		Logger::config(array('default' => array('adapter' => new MockLoggerAdapter())));

		$connection = Connections::get('doctrineTest');

		$this->post = new MockDoctrinePost();
		$this->noSchemaPost = new MockDoctrineNoSchemaPost();
		$this->datasource = $this->post->connection();
	}

	public function tearDown() {
		unset($this->post);
		unset($this->noSchemaPost);
		Logger::reset();
	}

	public function testMetadata() {
		$schema = array_diff(array_keys($this->post->schema()), array('author_id'));
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
		$this->assertFalse($associations['mockDoctrineAuthor']->isOwningSide());
		$this->assertFalse($associations['mockDoctrineAuthor']->hasCascades());
		$this->assertEqual('li3_doctrine\tests\mocks\data\model\MockDoctrinePost', $associations['mockDoctrineAuthor']->getSourceEntityName());
		$this->assertEqual('li3_doctrine\tests\mocks\data\model\MockDoctrineAuthor', $associations['mockDoctrineAuthor']->getTargetEntityName());

		$this->assertTrue($associations['mockDoctrineComment']->isOneToMany());
		$this->assertFalse($associations['mockDoctrineComment']->hasCascades());
		$this->assertEqual('li3_doctrine\tests\mocks\data\model\MockDoctrinePost', $associations['mockDoctrineComment']->getSourceEntityName());
		$this->assertEqual('li3_doctrine\tests\mocks\data\model\MockDoctrineComment', $associations['mockDoctrineComment']->getTargetEntityName());
		$this->assertEqual('mockDoctrinePost', $associations['mockDoctrineComment']->getMappedByFieldName());

		$this->assertTrue($associations['mockDoctrineExcerpt']->isOneToOne());
		$this->assertTrue($associations['mockDoctrineExcerpt']->isOwningSide());
		$this->assertTrue($associations['mockDoctrineExcerpt']->hasCascades());
		$this->assertEqual('li3_doctrine\tests\mocks\data\model\MockDoctrinePost', $associations['mockDoctrineExcerpt']->getSourceEntityName());
		$this->assertEqual('li3_doctrine\tests\mocks\data\model\MockDoctrineExcerpt', $associations['mockDoctrineExcerpt']->getTargetEntityName());
		$this->assertEqual('mockDoctrinePost', $associations['mockDoctrineExcerpt']->getMappedByFieldName());

		/*
		$this->datasource->applyFilter('read', function($self, $params, $chain) {
			$doctrineQuery = $chain->next($self, $params, $chain);
			$model = $params['options']['model']::meta('name');
			$doctrineQuery->innerJoin("{$model}.mockDoctrineAuthor", "a", \Doctrine\ORM\Query\Expr\Join::ON, "a.id = {$model}.mockDoctrineAuthor.id");
			return $doctrineQuery;
		});
		*/

		/*
		$post = MockDoctrinePost::find('first', array('fields' => array('id', 'title'), 'conditions' => array('id' => 1)));
		var_dump(\lithium\util\Set::extract(MockLoggerAdapter::$lines, '/message'));
		var_dump($post);
		*/

		/*
		$result = MockDoctrinePost::find('all', array('conditions' => array('id' => 1)));
		var_dump(\lithium\util\Set::extract(MockLoggerAdapter::$lines, '/message'));
		$post = $result->next();
		var_dump($post);
		*/

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
