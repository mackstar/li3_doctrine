<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of Rad, Inc. (http://union-of-rad.org)
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
	public static $_classes = array(
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
			'driver' => 'pdo_mysql'
		));

		$source = Connections::get('li3_doctrine_test');
		$this->assertTrue($source instanceof static::$_classes['source']);

		$this->assertTrue($source->isConnected());
	}

}

?>