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
			Connections::add('doctrineTest', 'Doctrine', array(
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
		$pattern = '/^SELECT\s+(.+)?\s+FROM/i';
		$this->assertPattern($pattern, $result);
		if (preg_match($pattern, $result, $matches)) {
			$fields = explode(',', preg_replace('/\s+/', '', $matches[1]));
			$schemaFields = array();
			foreach(array_keys(MockDoctrinePost::schema()) as $field) {
				$schemaFields[] = MockDoctrinePost::meta('name').'.'.$field;
			}
			sort($schemaFields);
			sort($fields);
			$this->assertEqual($fields, $schemaFields);
		}

		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'fields' => 'id'
		));
		$this->doctrine->read($query, array('model'=>$query->model()));
		$result = $this->doctrine->doctrineQuery->getDql();
		$this->assertPattern('/^SELECT\sMockDoctrinePost\.id\s+FROM/i', $result);
	}

	public function testCreate() {
	}

	public function testRead() {
		$query = new Query(array(
			'model' =>  'li3_doctrine\tests\mocks\data\model\MockDoctrinePost',
			'conditions' => array('MockDoctrinePost.id' => 1)
		));
		$result = $this->doctrine->read($query, array('model'=>$query->model()));
		$expected = array(
			'id' => 1,
			'title' => 'First post',
			'body' => 'This is the body for the first post',
			'created' => new \DateTime('2010-01-02 17:06:04'),
			'modified' => new \DateTime('2010-01-02 17:06:04')
		);
		$this->assertTrue(!empty($result));
		$this->assertEqual(count($result), 1);
		$this->assertEqual($this->_toArray($result[0]), $expected);
	}

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

	protected function _toArray($model) {
		$schema = $model->schema();
		$row = array();
		foreach(array_keys($schema) as $field) {
			$row[$field] = $model->$field;
		}
		return $row;
	}
}

?>
