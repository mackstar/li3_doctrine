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
	protected $_connection = 'doctrineTest';
	public function setUp() {
		if (!Connections::get($this->_connection)) {
			Connections::add($this->_connection, 'Doctrine', array(
				'driver' => 'pdo_sqlite',
				'path' => ':memory:'
			));
		}

		$this->post = new MockDoctrinePost();
	}

	public function testParseConditions() {
		$alias = $this->post->meta('name');
		$doctrine = new TestDoctrine(Connections::get($this->_connection, array('config'=>true)));

		$result = $doctrine->parseConditions(array(
			'id' => 1
		), compact('alias'));
		$this->assertPattern('/^MockDoctrinePost\.id\s*=\s*1$/i', $result);

		$result = $doctrine->parseConditions(array(
			'id' => 1,
			'title' => 'lithium'
		), compact('alias'));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')'
		)), $result);

		$result = $doctrine->parseConditions(array(
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

		$result = $doctrine->parseConditions(array(
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

		$result = $doctrine->parseConditions(array(
			'id' => array(1, 2)
		), compact('alias'));
		$this->assertPattern('/^MockDoctrinePost\.id\s+IN\s*\(\s*1\s*,\s*2\s*\)$/i', $result);
	}

	public function testCreate() {
	}

	public function testRead() {
		$result = $this->post->find('first', array(
			'conditions' => array('MockDoctrinePost.id' => 1)
		));
		$expected = array(
			'id' => 1,
			'title' => 'First post',
			'body' => 'This is the body for the first post',
			'created' => new \DateTime('2010-01-02 17:06:04'),
			'modified' => new \DateTime('2010-01-02 17:06:04')
		);
		$this->assertEqual($this->_toArray($result), $expected);
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
		$schema = $this->post->schema();
		$row = array();
		foreach(array_keys($schema) as $field) {
			$row[$field] = $model->$field;
		}
		return $row;
	}

}

?>
