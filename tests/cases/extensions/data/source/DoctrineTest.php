<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD, Inc. (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_doctrine\tests\cases\extensions\data\source;

use \lithium\data\Connections;

/**
 * Doctrine data source tests
 */
class DoctrineTest extends \lithium\test\Unit {

	/**
	 * Class dependencies
	 *
	 * @var array
	 */
	protected $_classes = array(
		'source' => '\li3_doctrine\extensions\data\source\Doctrine'
	);

	/**
	 * Bootstrap Doctrine
	 */
	public function setUp() {
		$plugin = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
		$bootstrap = $plugin . '/config/bootstrap.php';
		if (is_readable($bootstrap)) {
			include $bootstrap;
		}
	}

	/**
	 * Tests connections through `\lithium\data\Connections`
	 */
	public function testConnection() {
		Connections::add('li3_doctrine_test', 'Doctrine', array(
			'driver' => 'pdo_sqlite',
			'path' => ':memory:'
		));

		$source = Connections::get('li3_doctrine_test');
		$result = $source instanceof $this->_classes['source'];
		$this->assertTrue($result);

		$result = $source->isConnected();
		$this->assertTrue($result);
	}

}

?>