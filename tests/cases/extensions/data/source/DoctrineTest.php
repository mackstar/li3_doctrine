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

		$result = $this->_extractConditions($this->post->connection()->parseConditions(array(
			'id' => 1
		), compact('alias')));
		$this->assertPattern('/^MockDoctrinePost\.id\s*=\s*1$/i', $result);

		$result = $this->_extractConditions($this->post->connection()->parseConditions(array(
			'id' => 1,
			'title' => 'lithium'
		), compact('alias')));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')'
		)), $result);

		$result = $this->_extractConditions($this->post->connection()->parseConditions(array(
			'id' => 1,
			'or' => array(
				'title' => 'lithium',
				'body' => 'li3'
			)
		), compact('alias')));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(\s*',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')',
			'\s+OR\s+',
			'(MockDoctrinePost.body\s*=\s*\'li3\')',
			'\s*)'
		)), $result);

		$result = $this->_extractConditions($this->post->connection()->parseConditions(array(
			'id' => 1,
			'or' => array(
				'title' => 'lithium',
				array('title' => 'li3')
			)
		), compact('alias')));
		$this->assertPattern($this->_buildSqlRegex(array(
			'(MockDoctrinePost.id\s*=\s*1)',
			'\s+AND\s+',
			'(\s*',
			'(MockDoctrinePost.title\s*=\s*\'lithium\')',
			'\s+OR\s+',
			'(MockDoctrinePost.title\s*=\s*\'li3\')',
			'\s*)'
		)), $result);

		$result = $this->_extractConditions($this->post->connection()->parseConditions(array(
			'id' => array(1, 2)
		), compact('alias')));
		$this->assertPattern('/^MockDoctrinePost\.id\s+IN\s*\(\s*1\s*,\s*2\s*\)$/i', $result);
	}

	public function testCreate() {
	}

	public function _testRead() {
		$post = $this->post->find('first', array(
			'conditions' => array('MockDoctrinePost.id' => 1)
		));
	}

	public function testUpdate() {
	}

	public function testDelete() {
	}

	protected function _extractConditions($query) {
		if (!empty($query)) {
			$query = $this->post->connection()->getEntityManager()->createQueryBuilder()->add('where', $query)->getDql();
			$query = trim(preg_replace('/^SELECT\s+WHERE\s+/i', '', $query));
		}
		return $query;
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
}

?>
