<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\cases\extensions\data\source;

use \li3_doctrine\tests\mocks\data\model\MockDoctrinePost;

use \lithium\data\Connections;
use \lithium\data\model\Query;

class TestDoctrine extends \li3_doctrine\extensions\data\source\Doctrine {
	public function parseConditions() {
		$query = call_user_func_array(array($this, '_parseConditions'), func_get_args());
		if (!empty($query)) {
			$query = $this->getEntityManager()->createQueryBuilder()->add('where', $query)->getDql();
			$query = trim(preg_replace('/^SELECT\s+WHERE\s+/i', '', $query));
		}
		return $query;
	}
}

/**
 * Doctrine data source tests.
 */
class DoctrineTest extends \lithium\test\Unit {
	public function setUp() {
		if (!Connections::get('doctrineTest')) {
			Connections::add('doctrineTest', array(
				'type' => 'Doctrine',
				'driver' => 'pdo_sqlite',
				'path' => ':memory:'
			));
		}

		$this->doctrine = new TestDoctrine(Connections::get('doctrineTest', array('config'=>true)));
	}

	public function tearDown() {
		$this->doctrine->disconnect();
		unset($this->doctrine);
	}

	public function testParseConditions() {
		$alias = MockDoctrinePost::meta('name');

		$result = $this->doctrine->parseConditions(array(
			'id' => 1
		), compact('alias'));
		$this->assertPattern('/^MockDoctrinePost\.id\s*=\s*1$/i', $result);

		$result = $this->doctrine->parseConditions(array(
			'id' => 1,
			'title' => 'lithium'
		), compact('alias'));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')'
		)), $result);

		$result = $this->doctrine->parseConditions(array(
			'id' => 1,
			'or' => array(
				'title' => 'lithium',
				'body' => 'li3'
			)
		), compact('alias'));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(\s*',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')',
			'\s+OR\s+',
			'(MockDoctrinePost.body\s*=\s*\'li3\')',
			'\s*)'
		)), $result);

		$result = $this->doctrine->parseConditions(array(
			'id' => 1,
			'or' => array(
				'title' => 'lithium',
				array('title' => 'li3')
			)
		), compact('alias'));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(\s*',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')',
			'\s+OR\s+',
			'(MockDoctrinePost.title\s*=\s*\'li3\')',
			'\s*)'
		)), $result);

		$result = $this->doctrine->parseConditions(array(
			'id' => array(1, 2)
		), compact('alias'));
		$this->assertPattern('/^MockDoctrinePost\.id\s+IN\s*\(\s*1\s*,\s*2\s*\)$/i', $result);
	}

	public function testQuery() {
		$this->doctrine->applyFilter('read', function($self, $params, $chain) {
			$self->doctrineQuery = $chain->next($self, $params, $chain);
		});

		$this->expectException();
		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost'
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$result = $this->doctrine->doctrineQuery->getDql();
		$pattern = '/^SELECT\s+(.+?)\s+FROM\b/i';
		$this->assertPattern($pattern, $result);
		if (preg_match($pattern, $result, $matches)) {
			$result = explode(',', preg_replace('/\s+/', '', $matches[1]));
			$expected = array(MockDoctrinePost::meta('name'));
			$this->assertEqual($expected, $result);
		}

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'fields' => 'id'
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$result = $this->doctrine->doctrineQuery->getDql();
		$this->assertPattern('/^SELECT\s+MockDoctrinePost\.id\s+FROM\b/i', $result);

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'fields' => array('id', 'title')
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$result = $this->doctrine->doctrineQuery->getDql();
		$pattern = '/^SELECT\s+(.+?)\s+FROM\b/i';
		$this->assertPattern($pattern, $result);
		if (preg_match($pattern, $result, $matches)) {
			$result = explode(',', preg_replace('/\s+/', '', $matches[1]));
			$expected = array(
				'MockDoctrinePost.id',
				'MockDoctrinePost.title'
			);
			sort($result);
			sort($expected);
			$this->assertEqual($expected, $result);
		}

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'limit' => 5
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$this->assertEqual(5, $this->doctrine->doctrineQuery->getMaxResults());

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'page' => 2,
			'limit' => 5
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$this->assertEqual(5, $this->doctrine->doctrineQuery->getFirstResult());
		$this->assertEqual(5, $this->doctrine->doctrineQuery->getMaxResults());

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'order' => 'id DESC',
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$result = $this->doctrine->doctrineQuery->getDql();
		$this->assertPattern('/\bORDER\s+BY\s+MockDoctrinePost\.id\s+DESC\b/i', $result);

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'order' => array('id DESC', 'title' => 'asc'),
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$result = $this->doctrine->doctrineQuery->getDql();
		$this->assertPattern('/\bORDER\s+BY\s+MockDoctrinePost\.id\s+DESC\b/i', $result);
		$pattern = '/\bORDER\s+BY\s+(.+)$/i';
		$this->assertPattern($pattern, $result);
		if (preg_match($pattern, $result, $matches)) {
			$result = explode(',', preg_replace('/,\s+/', ',', $matches[1]));
			$expected = array(
				'MockDoctrinePost.id DESC',
				'MockDoctrinePost.title ASC'
			);
			sort($result);
			sort($expected);
			$this->assertEqual($expected, $result);
		}
	}

	public function testCreate() {
	}

	/*
	public function testRead() {
		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'conditions' => array('MockDoctrinePost.id' => 1),
			'fields' => array_keys(MockDoctrinePost::schema())
		));
		$result = $this->doctrine->read($query, array('model'=>$query->model()));
		$this->assertTrue($result instanceof \Doctrine\ORM\Internal\Hydration\IterableResult);
		$row = $result->next();
		$this->assertTrue(is_array($row));
		$this->assertTrue(!empty($row));
		$result = $row[0];
		$expected = array(
			'id' => 1,
			//'mockDoctrineAuthor' => 1,
			'title' => 'First post',
			'body' => 'This is the body for the first post',
			'created' => new \DateTime('2010-01-02 17:06:04'),
			'modified' => new \DateTime('2010-01-02 17:06:04')
		);
		$this->assertTrue(!empty($result));
		$this->assertEqual(1, count($result));
		$this->assertEqual($expected, $this->_toArray($result[0], array_keys($expected)));
	}
	*/

	public function testUpdate() {
	}

	public function testDelete() {
	}

	protected function _buildSqlRegex($sql) {
		$replacements = array(
			'(' => '\(',
			')' => '\)',
			'.' => '\.'
		);
		if (is_array($sql)) {
			$sql = implode($sql);
		}

		$sql = strtr($sql, $replacements);
		return '/^' . $sql . '$/i';
	}

	protected function _toArray($model, $columns = array()) {
		$columns = !empty($columns) ? $columns : array_keys($model->schema());
		$row = array();
		foreach($columns as $field) {
			$row[$field] = $model->$field;
		}
		return $row;
	}
}

?>
